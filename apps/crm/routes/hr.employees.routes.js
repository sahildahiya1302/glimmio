import express from 'express';
import multer from 'multer';
import fs from 'fs';
import path from 'path';

import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { createEmployeeSchema, updateEmployeeSchema, upsertProfileSchema } from '../validators/hr.validators.js';
import { listEmployees, getEmployee, createEmployee, updateEmployee, upsertProfile, softDeleteEmployee, getOrgChart, exportEmployees, bulkImportEmployees } from '../services/hr.employees.service.js';
import { listDocuments, addDocument, deleteDocument } from '../services/hr.documents.service.js';
import { listAssets, assignAsset, updateAssetStatus } from '../services/hr.assets.service.js';

import { listDepartments } from '../services/hr.departments.service.js';
import { toCSV } from '../utils/csv.js';
import { hrUploadPath, sanitizeFilename, ensureUploadDirs } from '../utils/storage.js';

const router = express.Router();
const upload = multer({ dest: hrUploadPath('tmp') });

ensureUploadDirs();

/* Employees */
router.get('/', requireAuth, async (req, res, next) => {
  try {
    const { q, dept, desig, active } = req.query;
    const data = await listEmployees({
      q: q || undefined,
      dept: dept ? Number(dept) : undefined,
      desig: desig ? Number(desig) : undefined,
      active: active !== undefined ? Number(active) : undefined
    });
    res.json(data);
  } catch (e) { next(e); }
});

router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const emp = await getEmployee(req.params.id);
    if (!emp) return res.status(404).json({ message: 'Not found' });
    res.json(emp);
  } catch (e) { next(e); }
});

router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createEmployeeSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    const emp = await createEmployee(value);
    res.status(201).json(emp);
  } catch (e) { next(e); }
});

router.put('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = updateEmployeeSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    const emp = await updateEmployee(req.params.id, value);
    res.json(emp);
  } catch (e) { next(e); }
});

router.delete('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await softDeleteEmployee(req.params.id)); } catch (e) { next(e); }
});

/* Profiles */
router.put('/:id/profile', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = upsertProfileSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await upsertProfile(req.params.id, value));
  } catch (e) { next(e); }
});

/* Org chart */
router.get('/_internal/org-chart/tree', requireAuth, async (_req, res, next) => {
  try { res.json(await getOrgChart()); } catch (e) { next(e); }
});

/* Export CSV */
router.get('/export/csv', requireAuth, requireRole('admin','manager'), async (_req, res, next) => {
  try {
    const rows = await exportEmployees();
    const csv = toCSV(rows);
    res.setHeader('Content-Type', 'text/csv');
    res.setHeader('Content-Disposition', `attachment; filename="employees.csv"`);
    res.send(csv);
  } catch (e) { next(e); }
});

/* Import CSV (fields: username,email,password,role,first_name,last_name,employee_code,doj,department,designation) */
router.post('/import/csv', requireAuth, requireRole('admin','manager'), upload.single('file'), async (req, res, next) => {
  try {
    if (!req.file) return res.status(400).json({ message: 'file required' });
    const text = fs.readFileSync(req.file.path, 'utf-8');
    // naive CSV parse (expects header)
    const [headerLine, ...lines] = text.split(/\r?\n/).filter(Boolean);
    const headers = headerLine.split(',').map(h => h.trim());
    const rows = lines.map(l => {
      const cols = l.match(/("([^"]|"")*"|[^,]+)/g) || [];
      const norm = cols.map(c => c.replace(/^"(.*)"$/s, '$1').replace(/""/g,'"'));
      const obj = {};
      headers.forEach((h, i) => obj[h] = norm[i] ?? '');
      return obj;
    });
    const created = await bulkImportEmployees(rows);
    fs.unlinkSync(req.file.path);
    res.json({ ok: true, created: created.length });
  } catch (e) { next(e); }
});

/* Documents (upload/list/delete) */
router.get('/:id/documents', requireAuth, async (req, res, next) => {
  try { res.json(await listDocuments(req.params.id)); } catch (e) { next(e); }
});

router.post('/:id/documents', requireAuth, requireRole('admin','manager'), upload.single('file'), async (req, res, next) => {
  try {
    if (!req.file) return res.status(400).json({ message: 'file required' });
    const { doc_type = 'GENERAL' } = req.body;
    const safeName = sanitizeFilename(req.file.originalname || req.file.filename);
    const dest = hrUploadPath(String(req.params.id), safeName);
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.renameSync(req.file.path, dest);
    const doc = await addDocument({
      user_id: Number(req.params.id),
      doc_type,
      file_name: safeName,
      file_path: dest.replace(process.cwd(), ''), // relative-ish
      mime_type: req.file.mimetype,
      size: req.file.size
    });
    res.status(201).json(doc);
  } catch (e) { next(e); }
});

router.delete('/documents/:docId', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const doc = await deleteDocument(req.params.docId);
    if (doc?.file_path) {
      const abs = path.isAbsolute(doc.file_path) ? doc.file_path : path.join(process.cwd(), doc.file_path);
      if (fs.existsSync(abs)) fs.unlinkSync(abs);
    }
    res.json({ ok: true });
  } catch (e) { next(e); }
});

/* Assets */
router.get('/:id/assets', requireAuth, async (req, res, next) => {
  try { res.json(await listAssets(req.params.id)); } catch (e) { next(e); }
});

router.post('/:id/assets', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.status(201).json(await assignAsset(req.params.id, req.body)); } catch (e) { next(e); }
});

router.post('/assets/:assetId/status', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { status } = req.body;
    res.json(await updateAssetStatus(req.params.assetId, status));
  } catch (e) { next(e); }
});

export default router;
