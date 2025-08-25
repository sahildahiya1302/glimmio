import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { pool } from '../config/database.js';

import { createShiftSchema, updateShiftSchema, createPolicySchema, updatePolicySchema, assignShiftSchema, clockSchema } from '../validators/attendance.validators.js';
import * as Shifts from '../services/attendance.shifts.service.js';
import * as Policies from '../services/attendance.policies.service.js';
import * as Assign from '../services/attendance.assignments.service.js';
import { clockIn, clockOut, getTimesheet, getDailySummary } from '../services/attendance.core.service.js';

const router = express.Router();

/* ----- Clock ----- */
router.post('/clock-in', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = clockSchema.validate(req.body || {});
    if (error) return res.status(422).json({ message: error.message });
    const result = await clockIn({ req, user_id: req.user.id, ...value });
    res.json(result);
  } catch (e) { next(e); }
});

router.post('/clock-out', requireAuth, async (req, res, next) => {
  try {
    const result = await clockOut({ req, user_id: req.user.id });
    res.json(result);
  } catch (e) { next(e); }
});

/* ----- Shifts CRUD ----- */
router.get('/shifts', requireAuth, requireRole('admin','manager'), async (_req, res, next) => {
  try { res.json(await Shifts.listShifts()); } catch (e) { next(e); }
});
router.get('/shifts/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const row = await Shifts.getShift(req.params.id);
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});
router.post('/shifts', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createShiftSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Shifts.createShift(value));
  } catch (e) { next(e); }
});
router.put('/shifts/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = updateShiftSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Shifts.updateShift(req.params.id, value));
  } catch (e) { next(e); }
});
router.delete('/shifts/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Shifts.deleteShift(req.params.id)); } catch (e) { next(e); }
});

/* ----- Policies CRUD ----- */
router.get('/policies', requireAuth, requireRole('admin','manager'), async (_req, res, next) => {
  try { res.json(await Policies.listPolicies()); } catch (e) { next(e); }
});
router.get('/policies/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const row = await Policies.getPolicy(req.params.id);
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});
router.post('/policies', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createPolicySchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Policies.createPolicy(value));
  } catch (e) { next(e); }
});
router.put('/policies/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = updatePolicySchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Policies.updatePolicy(req.params.id, value));
  } catch (e) { next(e); }
});
router.delete('/policies/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Policies.deletePolicy(req.params.id)); } catch (e) { next(e); }
});

/* ----- Assignments ----- */
router.get('/assignments', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const data = await Assign.listAssignments(req.query.user_id ? Number(req.query.user_id) : undefined);
    res.json(data);
  } catch (e) { next(e); }
});
router.post('/assignments', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = assignShiftSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Assign.assignShift(value));
  } catch (e) { next(e); }
});

/* ----- Reports ----- */
router.get('/timesheet/:user_id', requireAuth, async (req, res, next) => {
  try {
    const { start, end } = req.query;
    if (!start || !end) return res.status(400).json({ message: 'start and end required (YYYY-MM-DD)' });
    res.json(await getTimesheet(Number(req.params.user_id), String(start), String(end)));
  } catch (e) { next(e); }
});

router.get('/summary/daily', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const dateStr = req.query.date || new Date().toISOString().slice(0,10);
    res.json(await getDailySummary(dateStr));
  } catch (e) { next(e); }
});

export default router;
