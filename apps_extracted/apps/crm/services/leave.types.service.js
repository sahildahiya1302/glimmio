import { pool } from '../config/database.js';

export async function listTypes(activeOnly = false) {
  const [rows] = await pool.execute(
    `SELECT * FROM leave_types ${activeOnly ? 'WHERE is_active=1' : ''} ORDER BY name`
  );
  return rows;
}

export async function getType(id) {
  const [rows] = await pool.execute(`SELECT * FROM leave_types WHERE id=?`, [id]);
  return rows[0];
}

export async function createType(payload) {
  const fields = [
    'name','code','unit','default_annual_quota','carry_forward_max',
    'negative_balance_allowed','requires_document','gender_restriction',
    'min_notice_days','max_consecutive_days','weekend_inclusive','half_day_allowed','is_active'
  ];
  const vals = fields.map(f => payload[f] ?? null);
  const placeholders = fields.map(_ => '?').join(', ');
  const [res] = await pool.execute(
    `INSERT INTO leave_types (${fields.join(',')}) VALUES (${placeholders})`,
    vals
  );
  return await getType(res.insertId);
}

export async function updateType(id, payload) {
  const fields = [
    'name','code','unit','default_annual_quota','carry_forward_max',
    'negative_balance_allowed','requires_document','gender_restriction',
    'min_notice_days','max_consecutive_days','weekend_inclusive','half_day_allowed','is_active'
  ];
  const sets = [], vals = [];
  for (const f of fields) {
    if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  }
  if (!sets.length) return await getType(id);
  vals.push(id);
  await pool.execute(`UPDATE leave_types SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getType(id);
}

export async function deleteType(id) {
  await pool.execute(`DELETE FROM leave_types WHERE id=?`, [id]);
  return { ok: true };
}
