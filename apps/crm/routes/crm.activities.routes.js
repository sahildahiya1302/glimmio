import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { activitySchema } from '../validators/crm.validators.js';
import * as Acts from '../services/crm.activities.service.js';

const router = express.Router();

router.get('/', requireAuth, async (req, res, next) => {
  try {
    const filter = {};
    ['deal_id','account_id','contact_id','owner_id'].forEach(k => {
      if (req.query[k]) filter[k] = Number(req.query[k]);
    });
    res.json(await Acts.listActivities(filter));
  } catch (e) { next(e); }
});

router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await Acts.getActivity(Number(req.params.id));
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});

router.post('/', requireAuth, async (req, res, next) => {
  try {
    const { error, value } = activitySchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Acts.createActivity(value));
  } catch (e) { next(e); }
});

router.put('/:id', requireAuth, async (req, res, next) => {
  try { res.json(await Acts.updateActivity(Number(req.params.id), req.body)); } catch (e) { next(e); }
});

router.delete('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await Acts.deleteActivity(Number(req.params.id))); } catch (e) { next(e); }
});

export default router;
