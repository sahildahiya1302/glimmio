import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { pool } from '../config/database.js';

const router = express.Router();

router.post('/', requireAuth, async (req, res, next) => {
  try {
    const { lead_id, title, description, priority, deadline } = req.body;
    const [r] = await pool.execute(
      `INSERT INTO tasks (lead_id, assigned_to, title, description, priority, deadline, status)
       VALUES (?, ?, ?, ?, ?, ?, 'pending')`,
      [lead_id || null, req.user.id, title, description, priority || 'medium', deadline || null]
    );
    res.json({ ok: true, id: r.insertId });
  } catch (e) { next(e); }
});

export default router;
