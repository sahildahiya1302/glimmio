import { pool } from '../config/database.js';

export async function createQuiz(course_id, payload) {
  const { title, passing_score = 60, attempts_allowed = 3 } = payload;
  const [res] = await pool.execute(
    `INSERT INTO lms_quizzes (course_id, title, passing_score, attempts_allowed)
     VALUES (?, ?, ?, ?)`,
    [course_id, title, passing_score, attempts_allowed]
  );
  const [rows] = await pool.execute(`SELECT * FROM lms_quizzes WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function addQuestion(quiz_id, payload) {
  const { text, type = 'single', points = 1, position = 1, options = [] } = payload;
  const [res] = await pool.execute(
    `INSERT INTO lms_questions (quiz_id, text, type, points, position) VALUES (?, ?, ?, ?, ?)`,
    [quiz_id, text, type, points, position]
  );
  const qid = res.insertId;

  for (const opt of options) {
    await pool.execute(
      `INSERT INTO lms_options (question_id, text, is_correct) VALUES (?, ?, ?)`,
      [qid, opt.text, opt.is_correct ? 1 : 0]
    );
  }

  const [rows] = await pool.execute(`SELECT * FROM lms_questions WHERE id=?`, [qid]);
  return rows[0];
}

export async function getQuiz(quiz_id) {
  const [quizRows] = await pool.execute(`SELECT * FROM lms_quizzes WHERE id=?`, [quiz_id]);
  const quiz = quizRows[0];
  if (!quiz) return null;

  const [qRows] = await pool.execute(`SELECT * FROM lms_questions WHERE quiz_id=? ORDER BY position, id`, [quiz_id]);
  const qids = qRows.map(q => q.id);
  let oRows = [];
  if (qids.length) {
    const [optRows] = await pool.execute(
      `SELECT * FROM lms_options WHERE question_id IN (${qids.map(_=>'?').join(',')})`,
      qids
    );
    oRows = optRows;
  }
  const merged = qRows.map(q => ({ ...q, options: oRows.filter(o => o.question_id === q.id) }));
  return { ...quiz, questions: merged };
}

export async function startAttempt(quiz_id, user_id) {
  // Check attempts limit
  const [[limitRow]] = await pool.execute(`SELECT attempts_allowed FROM lms_quizzes WHERE id=?`, [quiz_id]);
  if (!limitRow) throw new Error('QUIZ_NOT_FOUND');
  const [[countRow]] = await pool.execute(
    `SELECT COUNT(*) AS c FROM lms_attempts WHERE quiz_id=? AND user_id=? AND status IN ('submitted','passed','failed')`,
    [quiz_id, user_id]
  );
  if (Number(countRow.c) >= Number(limitRow.attempts_allowed)) throw new Error('ATTEMPTS_EXCEEDED');

  const [res] = await pool.execute(
    `INSERT INTO lms_attempts (quiz_id, user_id, status) VALUES (?, ?, 'in_progress')`,
    [quiz_id, user_id]
  );
  const [rows] = await pool.execute(`SELECT * FROM lms_attempts WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function submitAttempt(attempt_id, answers) {
  // Fetch attempt & quiz
  const [[att]] = await pool.execute(`SELECT * FROM lms_attempts WHERE id=?`, [attempt_id]);
  if (!att) throw new Error('ATTEMPT_NOT_FOUND');
  if (att.status !== 'in_progress') throw new Error('ALREADY_SUBMITTED');

  const quiz = await getQuiz(att.quiz_id);
  if (!quiz) throw new Error('QUIZ_NOT_FOUND');

  // Index correct options
  const correctMap = new Map(); // qid -> Set(correct option ids)
  const pointsMap = new Map();  // qid -> points
  for (const q of quiz.questions) {
    correctMap.set(q.id, new Set(q.options.filter(o => o.is_correct).map(o => o.id)));
    pointsMap.set(q.id, Number(q.points || 1));
  }

  // Grade answers
  let earned = 0;
  let total = quiz.questions.reduce((s, q) => s + Number(q.points || 1), 0);

  for (const ans of answers) {
    const qid = Number(ans.question_id);
    const selected = new Set((ans.selected_option_ids || []).map(Number));
    const correct = correctMap.get(qid) || new Set();

    // correct if sets equal
    const isCorrect = selected.size === correct.size && [...selected].every(x => correct.has(x));
    const pts = isCorrect ? Number(pointsMap.get(qid) || 1) : 0;
    earned += pts;

    await pool.execute(
      `INSERT INTO lms_attempt_answers (attempt_id, question_id, selected_option_ids, is_correct, earned_points)
       VALUES (?, ?, ?, ?, ?)`,
      [attempt_id, qid, [...selected].join(','), isCorrect ? 1 : 0, pts]
    );
  }

  const percent = total > 0 ? Math.round((earned / total) * 10000) / 100 : 0;
  const passed = percent >= Number(quiz.passing_score);

  await pool.execute(
    `UPDATE lms_attempts SET status=?, submitted_at=NOW(), score=? WHERE id=?`,
    [passed ? 'passed' : 'failed', percent, attempt_id]
  );

  return { score: percent, status: passed ? 'passed' : 'failed' };
}
