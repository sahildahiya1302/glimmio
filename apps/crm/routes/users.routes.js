import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { getProfile } from '../services/user.service.js';

const router = express.Router();

router.get('/me', requireAuth, async (req, res, next) => {
  try {
    const me = await getProfile(req.user.id);
    res.json(me);
  } catch (e) {
    next(e);
  }
});

router.get('/admin/summary', requireAuth, requireRole('admin'), async (_req, res) => {
  res.json({ users: 'TODO', departments: 'TODO' });
});

export default router;
