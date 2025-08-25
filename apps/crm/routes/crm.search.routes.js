import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { searchAll } from '../services/crm.search.service.js';

const router = express.Router();

router.get('/', requireAuth, async (req, res, next) => {
  try {
    const q = String(req.query.q || '').trim();
    if (!q) return res.status(400).json({ message: 'q required' });
    const limit = req.query.limit ? Number(req.query.limit) : 5;
    res.json(await searchAll(q, limit));
  } catch (e) { next(e); }
});

export default router;
