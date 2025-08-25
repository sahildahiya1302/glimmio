// /services/hr.assets.service.js
import { pool } from '../config/database.js';

export async function listAssets(user_id) {
  const [rows] = await pool.execute(
    `SELECT * FROM employee_assets WHERE user_id=? ORDER BY assigned_at DESC`,
    [user_id]
  );
  return rows;
}

export async function assignAsset(user_id, payload) {
  const { item_name, serial_no, notes } = payload;
  const [res] = await pool.execute(
    `INSERT INTO employee_assets (user_id, item_name, serial_no, notes, status)
     VALUES (?, ?, ?, ?, 'assigned')`,
    [user_id, item_name, serial_no || null, notes || null]
  );
  const [rows] = await pool.execute(`SELECT * FROM employee_assets WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function updateAssetStatus(id, status) {
  const allowed = new Set(['assigned', 'returned', 'lost', 'damaged']);
  if (!allowed.has(status)) throw new Error('INVALID_STATUS');

  let sql = `UPDATE employee_assets SET status=?`;
  const args = [status];

  if (status === 'returned') {
    sql += `, returned_at = NOW()`;
  }

  sql += ` WHERE id=?`;
  args.push(id);

  await pool.execute(sql, args);

  const [rows] = await pool.execute(`SELECT * FROM employee_assets WHERE id=?`, [id]);
  return rows[0];
}
