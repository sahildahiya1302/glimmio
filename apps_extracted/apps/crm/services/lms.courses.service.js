import { pool } from '../config/database.js';

export async function listCourses({ publishedOnly = true } = {}) {
  const [rows] = await pool.execute(
    `SELECT * FROM lms_courses ${publishedOnly ? 'WHERE is_published=1' : ''} ORDER BY id DESC`
  );
  return rows;
}
export async function getCourse(id) {
  const [rows] = await pool.execute(`SELECT * FROM lms_courses WHERE id=?`, [id]);
  return rows[0];
}
export async function createCourse(payload, user_id) {
  const {
    title, description, category, level, language, duration_minutes, is_published
  } = payload;
  const [res] = await pool.execute(
    `INSERT INTO lms_courses (title, description, category, level, language, duration_minutes, is_published, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
    [title, description || null, category || null, level || 'beginner', language || null,
     duration_minutes || 0, is_published ? 1 : 0, user_id || null]
  );
  return await getCourse(res.insertId);
}
export async function updateCourse(id, payload) {
  const fields = ['title','description','category','level','language','duration_minutes','is_published'];
  const sets = [], vals = [];
  for (const f of fields) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) return await getCourse(id);
  vals.push(id);
  await pool.execute(`UPDATE lms_courses SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getCourse(id);
}
export async function deleteCourse(id) {
  await pool.execute(`DELETE FROM lms_courses WHERE id=?`, [id]);
  return { ok: true };
}

/* Modules */
export async function addModule(course_id, payload) {
  const { title, position = 1 } = payload;
  const [res] = await pool.execute(
    `INSERT INTO lms_modules (course_id, title, position) VALUES (?, ?, ?)`,
    [course_id, title, position]
  );
  const [rows] = await pool.execute(`SELECT * FROM lms_modules WHERE id=?`, [res.insertId]);
  return rows[0];
}
export async function updateModule(id, payload) {
  const sets = [], vals = [];
  for (const f of ['title','position']) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) {
    const [r] = await pool.execute(`SELECT * FROM lms_modules WHERE id=?`, [id]);
    return r[0];
  }
  vals.push(id);
  await pool.execute(`UPDATE lms_modules SET ${sets.join(', ')} WHERE id=?`, vals);
  const [rows] = await pool.execute(`SELECT * FROM lms_modules WHERE id=?`, [id]);
  return rows[0];
}
export async function deleteModule(id) {
  await pool.execute(`DELETE FROM lms_modules WHERE id=?`, [id]);
  return { ok: true };
}

export async function getCourseStructure(course_id) {
  const [mods] = await pool.execute(`SELECT * FROM lms_modules WHERE course_id=? ORDER BY position, id`, [course_id]);
  const [less] = await pool.execute(
    `SELECT l.*, q.title AS quiz_title
       FROM lms_lessons l
       LEFT JOIN lms_quizzes q ON q.id=l.quiz_id
      WHERE l.module_id IN (${mods.length ? mods.map(m => '?').join(',') : 'NULL'})
      ORDER BY l.position, l.id`,
    mods.map(m => m.id)
  );
  const grouped = {};
  for (const m of mods) grouped[m.id] = { ...m, lessons: [] };
  for (const l of less) grouped[l.module_id]?.lessons.push(l);
  return mods.map(m => grouped[m.id]);
}

/* Lessons */
export async function addLesson(module_id, payload) {
  const { title, type, content, video_url, quiz_id, duration_minutes = 0, position = 1, is_preview = 0 } = payload;
  const [res] = await pool.execute(
    `INSERT INTO lms_lessons (module_id, title, type, content, video_url, quiz_id, duration_minutes, position, is_preview)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [module_id, title, type, content || null, video_url || null, quiz_id || null,
     duration_minutes, position, is_preview ? 1 : 0]
  );
  const [rows] = await pool.execute(`SELECT * FROM lms_lessons WHERE id=?`, [res.insertId]);
  return rows[0];
}
export async function updateLesson(id, payload) {
  const fields = ['title','type','content','video_url','file_path','quiz_id','duration_minutes','position','is_preview'];
  const sets = [], vals = [];
  for (const f of fields) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) {
    const [r] = await pool.execute(`SELECT * FROM lms_lessons WHERE id=?`, [id]);
    return r[0];
  }
  vals.push(id);
  await pool.execute(`UPDATE lms_lessons SET ${sets.join(', ')} WHERE id=?`, vals);
  const [rows] = await pool.execute(`SELECT * FROM lms_lessons WHERE id=?`, [id]);
  return rows[0];
}
export async function deleteLesson(id) {
  await pool.execute(`DELETE FROM lms_lessons WHERE id=?`, [id]);
  return { ok: true };
}
