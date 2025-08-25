import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { createDepartmentSchema } from '../validators/hr.validators.js';
import { listDepartments, createDepartment, updateDepartment, deleteDepartment, getDepartment } from '../services/hr.departments.service.js';

const router = express.Router();

router.get('/', requireAuth, async (_req, res, next) => {
  try { res.json(await listDepartments()); } catch (e) { next(e); }
});

router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await getDepartment(req.params.id);
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});

router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createDepartmentSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await createDepartment(value));
  } catch (e) { next(e); }
});

router.put('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await updateDepartment(req.params.id, req.body)); } catch (e) { next(e); }
});

router.delete('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await deleteDepartment(req.params.id)); } catch (e) { next(e); }
});

export default router;
