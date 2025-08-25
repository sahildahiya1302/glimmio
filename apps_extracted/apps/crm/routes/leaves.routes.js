import express from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';

import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import {
  leaveTypeCreateSchema, leaveTypeUpdateSchema,
  entitlementUpsertSchema, holidayCreateSchema, holidayUpdateSchema,
  leaveRequestSchema, approveSchema
} from '../validators/leave.validators.js';

import * as Types from '../services/leave.types.service.js';
import * as Ent from '../services/leave.entitlements.service.js';
import * as Hol from '../services/leave.holidays.service.js';
import * as Bal from '../services/leave.balance.service.js';
import * as Req from '../services/leave.requests.service.js';

const router = express.Router();

/* Storage for leave docs */
const ROOT = process.cwd();
const DOC_DIR = path.join(ROOT, 'storage', 'uploads', 'leaves');
fs.mkdirSync(DOC_DIR, { recursive: true });
const upload = multer({ dest: path.join(DOC_DIR, 'tmp') });

/* ===== Leave Types ===== */
router.get('/types', requireAuth, requireRole('admin','manager'), async (_req, res, next) => {
  try { res.json(await Types.listTypes()); } catch (e) { next(e); }
});

router.get('/types/active', requireAuth, async (_req, res, next) => {
  try { res.json(await Types.listTypes(true)); } catch (e) { next(e); }
});

router.post('/types', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { error, value } = leaveTypeCreateSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Types.createType(value));
  } catch (e) { next(e); }
});

router.put('/types/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { error, value } = leaveTypeUpdateSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Types.updateType(req.params.id, value));
  } catch (e) { next(e); }
});

router.delete('/types/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Types.deleteType(req.params.id)); } catch (e) { next(e); }
});

/* ===== Holidays ===== */
router.get('/holidays', requireAuth, async (req, res, next) => {
  try {
    const { start, end, region } = req.query;
    res.json(await Hol.listHolidays({ start, end, region }));
  } catch (e) { next(e); }
});

router.post('/holidays', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = holidayCreateSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Hol.createHoliday(value));
  } catch (e) { next(e); }
});

router.put('/holidays/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = holidayUpdateSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Hol.updateHoliday(req.params.id, value));
  } catch (e) { next(e); }
});

router.delete('/holidays/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Hol.deleteHoliday(req.params.id)); } catch (e) { next(e); }
});

/* ===== Entitlements & Balances ===== */
router.post('/entitlements', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = entitlementUpsertSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Ent.upsertEntitlement(value));
  } catch (e) { next(e); }
});

router.get('/balance', requireAuth, async (req, res, next) => {
  try {
    const user_id = Number(req.query.user_id || req.user.id);
    const type_id = Number(req.query.type_id);
    const year = Number(req.query.year || new Date().getFullYear());
    if (!type_id) return res.status(400).json({ message: 'type_id required' });
    res.json(await Bal.computeBalance(user_id, type_id, year));
  } catch (e) { next(e); }
});

/* ===== Requests ===== */
router.get('/requests', requireAuth, async (req, res, next) => {
  try {
    const user_id = req.query.user_id ? Number(req.query.user_id) : undefined;
    const status = req.query.status || undefined;
    res.json(await Req.listRequests({ user_id, status }));
  } catch (e) { next(e); }
});

router.get('/requests/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await Req.getRequest(req.params.id);
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});

router.post('/requests', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = leaveRequestSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    const created = await Req.applyRequest(req.user.id, value);
    res.status(201).json(created);
  } catch (e) {
    if (e.message && [
      'INVALID_TYPE','NOTICE_TOO_SHORT','TYPE_NOT_ALLOWED_FOR_GENDER',
      'EXCEEDS_MAX_CONSECUTIVE','INSUFFICIENT_BALANCE'
    ].includes(e.message)) {
      return res.status(400).json({ message: e.message });
    }
    next(e);
  }
});

/* Attach a document to a request (optional) */
router.post('/requests/:id/document', requireAuth, upload.single('file'), async (req, res, next) => {
  try {
    if (!req.file) return res.status(400).json({ message: 'file required' });
    const safe = (req.file.originalname || req.file.filename).replace(/[^a-zA-Z0-9._-]/g, '_');
    const destDir = path.join(DOC_DIR, String(req.params.id));
    fs.mkdirSync(destDir, { recursive: true });
    const dest = path.join(destDir, safe);
    fs.renameSync(req.file.path, dest);
    const rel = dest.replace(ROOT, '');
    res.json(await Req.attachDocument(req.params.id, rel));
  } catch (e) { next(e); }
});

/* Approvals */
router.post('/requests/:id/act', requireAuth, requireRole('manager','admin'), async (req, res, next) => {
  try {
    const { error, value } = approveSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    const result = await Req.actOnRequest({
      request_id: Number(req.params.id),
      approver_id: req.user.id,
      action: value.action,
      comment: value.comment || null
    });
    res.json(result);
  } catch (e) {
    if (e.message === 'NO_PENDING_STEP') return res.status(400).json({ message: e.message });
    next(e);
  }
});

export default router;
