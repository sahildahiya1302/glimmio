import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { dealSchema, moveDealSchema } from '../validators/crm.validators.js';
import * as Deals from '../services/crm.deals.service.js';

const router = express.Router();

router.get('/', requireAuth, async (req, res, next) => {
  try {
    const data = await Deals.listDeals({
      pipeline_id: req.query.pipeline_id ? Number(req.query.pipeline_id) : undefined,
      stage_id: req.query.stage_id ? Number(req.query.stage_id) : undefined,
      owner_id: req.query.owner_id ? Number(req.query.owner_id) : undefined,
      q: req.query.q || undefined
    });
    res.json(data);
  } catch (e) { next(e); }
});

router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await Deals.getDeal(Number(req.params.id));
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});

router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = dealSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Deals.createDeal(value));
  } catch (e) { next(e); }
});

router.put('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await Deals.updateDeal(Number(req.params.id), req.body)); } catch (e) { next(e); }
});

router.post('/:id/move', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = moveDealSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.json(await Deals.moveDeal(Number(req.params.id), value.stage_id));
  } catch (e) { next(e); }
});

router.post('/:id/status', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { status } = req.body;
    if (!['open','won','lost'].includes(status)) return res.status(422).json({ message: 'invalid status' });
    res.json(await Deals.setDealStatus(Number(req.params.id), status));
  } catch (e) { next(e); }
});

router.get('/kanban', requireAuth, async (req, res, next) => {
  try {
    const pipeline_id = Number(req.query.pipeline_id || 1);
    const data = await Deals.listKanban(pipeline_id);
    res.json(data);
  } catch (e) { next(e); }
});


export default router;
