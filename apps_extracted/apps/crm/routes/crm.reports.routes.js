import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { pipelineSummary, winRate, revenueForecast } from '../services/crm.reports.service.js';

const router = express.Router();

router.get('/pipeline', requireAuth, async (req, res, next) => {
  try {
    const pid = Number(req.query.pipeline_id || 1);
    res.json(await pipelineSummary(pid));
  } catch (e) { next(e); }
});

router.get('/winrate', requireAuth, async (req, res, next) => {
  try {
    const days = req.query.days ? Number(req.query.days) : 90;
    res.json(await winRate(days));
  } catch (e) { next(e); }
});

router.get('/forecast', requireAuth, async (req, res, next) => {
  try {
    const months = req.query.months ? Number(req.query.months) : 3;
    res.json(await revenueForecast(months));
  } catch (e) { next(e); }
});

export default router;
