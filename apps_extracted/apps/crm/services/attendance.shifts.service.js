import { pool } from '../config/database.js';

export async function listShifts() {
  const [rows] = await pool.execute(`SELECT * FROM shifts ORDER BY id DESC`);
  return rows;
}
export async function getShift(id) {
  const [rows] = await pool.execute(`SELECT * FROM shifts WHERE id=?`, [id]);
  return rows[0];
}
export async function createShift(data) {
  const { name, working_days, start_time, end_time, grace_minutes } = data;
  const [res] = await pool.execute(
    `INSERT INTO shifts (name, working_days, start_time, end_time, grace_minutes)
     VALUES (?, ?, ?, ?, ?)`,
    [name, working_days, start_time, end_time, grace_minutes]
  );
  return await getShift(res.insertId);
}
export async function updateShift(id, data) {
  const fields = ['name','working_days','start_time','end_time','grace_minutes'];
  const sets = [], vals = [];
  for (const f of fields) {
    if (data[f] !== undefined) { sets.push(`${f}=?`); vals.push(data[f]); }
  }
  if (!sets.length) return await getShift(id);
  vals.push(id);
  await pool.execute(`UPDATE shifts SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getShift(id);
}
export async function deleteShift(id) {
  await pool.execute(`DELETE FROM shifts WHERE id=?`, [id]);
  return { ok: true };
}
