import { pool } from '../config/database.js';

export async function listHolidays({ start, end, region } = {}) {
  const where = [];
  const vals = [];
  if (start && end) { where.push('date BETWEEN ? AND ?'); vals.push(start, end); }
  if (region) { where.push('(region = ? OR region IS NULL)'); vals.push(region); }
  const sql = `SELECT * FROM holidays ${where.length ? 'WHERE ' + where.join(' AND ') : ''} ORDER BY date`;
  const [rows] = await pool.execute(sql, vals);
  return rows;
}

export async function createHoliday(payload) {
  const { name, date, region, is_optional } = payload;
  const [res] = await pool.execute(
    `INSERT INTO holidays (name, date, region, is_optional) VALUES (?, ?, ?, ?)`,
    [name, date, region || null, is_optional ? 1 : 0]
  );
  const [rows] = await pool.execute(`SELECT * FROM holidays WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function updateHoliday(id, payload) {
  const sets = [], vals = [];
  for (const f of ['name','date','region','is_optional']) {
    if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  }
  if (!sets.length) {
    const [rows] = await pool.execute(`SELECT * FROM holidays WHERE id=?`, [id]);
    return rows[0];
  }
  vals.push(id);
  await pool.execute(`UPDATE holidays SET ${sets.join(', ')} WHERE id=?`, vals);
  const [rows] = await pool.execute(`SELECT * FROM holidays WHERE id=?`, [id]);
  return rows[0];
}

export async function deleteHoliday(id) {
  await pool.execute(`DELETE FROM holidays WHERE id=?`, [id]);
  return { ok: true };
}
