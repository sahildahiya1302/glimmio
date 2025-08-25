import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { pool } from '../config/database.js';

const router = express.Router();

router.post('/', requireAuth, async (req, res, next) => {
  try {
    const { name, email, phone, source } = req.body;
    const [r] = await pool.execute(
      `INSERT INTO leads (name, email, phone, source, status) VALUES (?, ?, ?, ?, 'unassigned')`,
      [name, email, phone, source]
    );
    res.json({ ok: true, id: r.insertId });
  } catch (e) { next(e); }
});

export default router;
