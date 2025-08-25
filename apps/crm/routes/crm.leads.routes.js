import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { leadSchema, leadConvertSchema } from '../validators/crm.validators.js';
import * as Leads from '../services/crm.leads.service.js';

const router = express.Router();

router.get('/', requireAuth, async (req, res, next) => {
  try {
    const data = await Leads.listLeads({
      q: req.query.q || undefined,
      status: req.query.status || undefined,
      owner_id: req.query.owner_id ? Number(req.query.owner_id) : undefined
    });
    res.json(data);
  } catch (e) { next(e); }
});

router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await Leads.getLead(Number(req.params.id));
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});

router.post('/', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = leadSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Leads.createLead(value));
  } catch (e) { next(e); }
});

router.put('/:id', requireAuth, async (req, res, next) => {
  try { res.json(await Leads.updateLead(Number(req.params.id), req.body)); } catch (e) { next(e); }
});

router.delete('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await Leads.deleteLead(Number(req.params.id))); } catch (e) { next(e); }
});

router.post('/:id/convert', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = leadConvertSchema.validate(req.body || {});
    if (error) return res.status(422).json({ message: error.message });
    const result = await Leads.convertLead(Number(req.params.id), value);
    res.json(result);
  } catch (e) {
    if (e.message === 'LEAD_NOT_FOUND') return res.status(404).json({ message: e.message });
    next(e);
  }
});

export default router;
