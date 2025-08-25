import express from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';

import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';

import { lmsUploadPath, sanitizeFilename } from '../utils/storage.js';

import {
  createCourseSchema, updateCourseSchema,
  createModuleSchema, updateModuleSchema,
  createLessonSchema, updateLessonSchema,
  enrollSchema, quizCreateSchema, questionCreateSchema,
  attemptStartSchema, attemptSubmitSchema
} from '../validators/lms.validators.js';

import * as Courses from '../services/lms.courses.service.js';
import * as Enroll from '../services/lms.enroll.service.js';
import * as Certs from '../services/lms.certificates.service.js';
import * as Quiz from '../services/lms.quizzes.service.js';

const router = express.Router();
const upload = multer({ dest: lmsUploadPath('tmp') });

/* ===== Courses ===== */
router.get('/courses', requireAuth, async (_req, res, next) => {
  try { res.json(await Courses.listCourses({ publishedOnly: true })); } catch (e) { next(e); }
});

router.get('/courses/admin', requireAuth, requireRole('admin','manager'), async (_req, res, next) => {
  try { res.json(await Courses.listCourses({ publishedOnly: false })); } catch (e) { next(e); }
});

router.get('/courses/:id', requireAuth, async (req, res, next) => {
  try {
    const course = await Courses.getCourse(Number(req.params.id));
    if (!course) return res.status(404).json({ message: 'Not found' });
    const structure = await Courses.getCourseStructure(course.id);
    res.json({ ...course, modules: structure });
  } catch (e) { next(e); }
});

router.post('/courses', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createCourseSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Courses.createCourse(value, req.user.id));
  } catch (e) { next(e); }
});

router.put('/courses/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = updateCourseSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Courses.updateCourse(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.delete('/courses/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Courses.deleteCourse(Number(req.params.id))); } catch (e) { next(e); }
});

/* ===== Modules ===== */
router.post('/courses/:id/modules', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createModuleSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Courses.addModule(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.put('/modules/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = updateModuleSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Courses.updateModule(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.delete('/modules/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Courses.deleteModule(Number(req.params.id))); } catch (e) { next(e); }
});

/* ===== Lessons ===== */
router.post('/modules/:id/lessons', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createLessonSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Courses.addLesson(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.put('/lessons/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = updateLessonSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Courses.updateLesson(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.delete('/lessons/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Courses.deleteLesson(Number(req.params.id))); } catch (e) { next(e); }
});

/* Lesson file upload */
router.post('/lessons/:id/upload', requireAuth, requireRole('admin','manager'), upload.single('file'), async (req, res, next) => {
  try {
    if (!req.file) return res.status(400).json({ message: 'file required' });
    const safe = sanitizeFilename(req.file.originalname || req.file.filename);
    const destDir = lmsUploadPath('lessons', String(req.params.id));
    fs.mkdirSync(destDir, { recursive: true });
    const dest = path.join(destDir, safe);
    fs.renameSync(req.file.path, dest);
    const rel = dest.replace(process.cwd(), '');
    const updated = await Courses.updateLesson(Number(req.params.id), { file_path: rel });
    res.json(updated);
  } catch (e) { next(e); }
});

/* ===== Enrollments & Progress ===== */
router.post('/enroll', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = enrollSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Enroll.enroll(value.course_id, req.user.id));
  } catch (e) { next(e); }
});

router.get('/me/enrollments', requireAuth, async (req, res, next) => {
  try { res.json(await Enroll.listMyEnrollments(req.user.id)); } catch (e) { next(e); }
});

router.post('/lessons/:id/complete', requireAuth, async (req, res, next) => {
  try {
    const result = await Enroll.markLessonComplete({ user_id: req.user.id, lesson_id: Number(req.params.id) });
    res.json(result);
  } catch (e) { next(e); }
});

/* ===== Quizzes ===== */
router.post('/courses/:id/quizzes', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = quizCreateSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Quiz.createQuiz(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.post('/quizzes/:id/questions', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = questionCreateSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Quiz.addQuestion(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.get('/quizzes/:id', requireAuth, async (req, res, next) => {
  try {
    const quiz = await Quiz.getQuiz(Number(req.params.id));
    if (!quiz) return res.status(404).json({ message: 'Not found' });
    res.json(quiz);
  } catch (e) { next(e); }
});

router.post('/attempts/start', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = attemptStartSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Quiz.startAttempt(value.quiz_id, req.user.id));
  } catch (e) { next(e); }
});

router.post('/attempts/:id/submit', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = attemptSubmitSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    const result = await Quiz.submitAttempt(Number(req.params.id), value.answers);
    res.json(result);
  } catch (e) {
    if (e.message && ['ATTEMPT_NOT_FOUND','ALREADY_SUBMITTED','QUIZ_NOT_FOUND','ATTEMPTS_EXCEEDED'].includes(e.message)) {
      return res.status(400).json({ message: e.message });
    }
    next(e);
  }
});

/* ===== Certificates ===== */
router.get('/courses/:id/certificate', requireAuth, async (req, res, next) => {
  try {
    const existing = await Certs.getCertificate(Number(req.params.id), req.user.id);
    if (existing) return res.json(existing);

    const issued = await Certs.maybeIssueCertificate(Number(req.params.id), req.user.id);
    if (!issued) return res.status(400).json({ message: 'Not eligible' });
    res.json(issued);
  } catch (e) { next(e); }
});

export default router;
