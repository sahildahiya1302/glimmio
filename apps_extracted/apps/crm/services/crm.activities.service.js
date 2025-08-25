import { pool } from '../config/database.js';

export async function listActivities(filter = {}) {
  const where = [], vals = [];
  for (const key of ['deal_id','account_id','contact_id','owner_id']) {
    if (filter[key]) { where.push(`${key}=?`); vals.push(filter[key]); }
  }
  const [rows] = await pool.execute(
    `SELECT * FROM activities ${where.length ? 'WHERE ' + where.join(' AND ') : ''} ORDER BY updated_at DESC LIMIT 1000`,
    vals
  );
  return rows;
}

export async function getActivity(id) {
  const [[row]] = await pool.execute(`SELECT * FROM activities WHERE id=?`, [id]);
  return row || null;
}

export async function createActivity(payload) {
  const fields = ['type','subject','description','due_date','status','owner_id','account_id','contact_id','deal_id'];
  const vals = fields.map(f => payload[f] ?? null);
  const [res] = await pool.execute(
    `INSERT INTO activities (${fields.join(',')}) VALUES (${fields.map(_=>'?').join(',')})`,
    vals
  );
  return await getActivity(res.insertId);
}

export async function updateActivity(id, payload) {
  const fields = ['type','subject','description','due_date','status','owner_id','account_id','contact_id','deal_id'];
  const sets = [], vals = [];
  for (const f of fields) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) return await getActivity(id);
  vals.push(id);
  await pool.execute(`UPDATE activities SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getActivity(id);
}

export async function deleteActivity(id) {
  await pool.execute(`DELETE FROM activities WHERE id=?`, [id]);
  return { ok: true };
}
