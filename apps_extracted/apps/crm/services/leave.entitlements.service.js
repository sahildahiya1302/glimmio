import { pool } from '../config/database.js';

export async function upsertEntitlement({ user_id, type_id, year, quota, carried_forward, notes }) {
  await pool.execute(
    `INSERT INTO leave_entitlements (user_id, type_id, year, quota, carried_forward, notes)
     VALUES (?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE quota=VALUES(quota), carried_forward=VALUES(carried_forward), notes=VALUES(notes)`,
    [user_id, type_id, year, quota, carried_forward ?? 0, notes ?? null]
  );
  const [rows] = await pool.execute(
    `SELECT * FROM leave_entitlements WHERE user_id=? AND type_id=? AND year=?`,
    [user_id, type_id, year]
  );
  return rows[0];
}

export async function getEntitlement(user_id, type_id, year) {
  const [rows] = await pool.execute(
    `SELECT * FROM leave_entitlements WHERE user_id=? AND type_id=? AND year=?`,
    [user_id, type_id, year]
  );
  return rows[0] || null;
}
