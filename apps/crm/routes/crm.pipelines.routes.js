import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { pipelineSchema, stageSchema } from '../validators/crm.validators.js';
import * as Pipes from '../services/crm.pipelines.service.js';

const router = express.Router();

router.get('/', requireAuth, async (_req, res, next) => {
  try { res.json(await Pipes.listPipelines()); } catch (e) { next(e); }
});
router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = pipelineSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Pipes.createPipeline(value));
  } catch (e) { next(e); }
});
router.post('/:id/stages', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = stageSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Pipes.addStage(Number(req.params.id), value));
  } catch (e) { next(e); }
});
router.put('/stages/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await Pipes.updateStage(Number(req.params.id), req.body)); } catch (e) { next(e); }
});
router.delete('/stages/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Pipes.deleteStage(Number(req.params.id))); } catch (e) { next(e); }
});

export default router;
