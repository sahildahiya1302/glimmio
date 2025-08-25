import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { createDesignationSchema } from '../validators/hr.validators.js';
import { listDesignations, createDesignation, updateDesignation, deleteDesignation, getDesignation } from '../services/hr.designations.service.js';

const router = express.Router();

router.get('/', requireAuth, async (_req, res, next) => {
  try { res.json(await listDesignations()); } catch (e) { next(e); }
});

router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await getDesignation(req.params.id);
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});

router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = createDesignationSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await createDesignation(value));
  } catch (e) { next(e); }
});

router.put('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await updateDesignation(req.params.id, req.body)); } catch (e) { next(e); }
});

router.delete('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await deleteDesignation(req.params.id)); } catch (e) { next(e); }
});

export default router;
