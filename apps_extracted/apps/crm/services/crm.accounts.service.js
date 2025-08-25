import { pool } from '../config/database.js';

export async function listAccounts({ q } = {}) {
  const where = [], vals = [];
  if (q) { where.push('(name LIKE ? OR domain LIKE ? OR phone LIKE ?)'); vals.push(`%${q}%`,`%${q}%`,`%${q}%`); }
  const [rows] = await pool.execute(
    `SELECT * FROM accounts ${where.length ? 'WHERE ' + where.join(' AND ') : ''} ORDER BY updated_at DESC LIMIT 1000`,
    vals
  );
  return rows;
}
export async function getAccount(id) {
  const [[row]] = await pool.execute(`SELECT * FROM accounts WHERE id=?`, [id]);
  return row || null;
}
export async function createAccount(payload) {
  const fields = ['name','domain','phone','website','address_line1','address_line2','city','state','postal_code','country','owner_id'];
  const vals = fields.map(f => payload[f] ?? null);
  const [res] = await pool.execute(
    `INSERT INTO accounts (${fields.join(',')}) VALUES (${fields.map(_=>'?').join(',')})`,
    vals
  );
  return await getAccount(res.insertId);
}
export async function updateAccount(id, payload) {
  const fields = ['name','domain','phone','website','address_line1','address_line2','city','state','postal_code','country','owner_id'];
  const sets = [], vals = [];
  for (const f of fields) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) return await getAccount(id);
  vals.push(id);
  await pool.execute(`UPDATE accounts SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getAccount(id);
}
export async function deleteAccount(id) {
  await pool.execute(`DELETE FROM accounts WHERE id=?`, [id]);
  return { ok: true };
}
