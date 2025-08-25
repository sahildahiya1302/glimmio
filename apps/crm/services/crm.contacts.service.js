import { pool } from '../config/database.js';

export async function listContacts({ q, account_id } = {}) {
  const where = [], vals = [];
  if (q) { where.push('(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)'); vals.push(`%${q}%`,`%${q}%`,`%${q}%`,`%${q}%`); }
  if (account_id) { where.push('account_id=?'); vals.push(account_id); }
  const [rows] = await pool.execute(
    `SELECT * FROM contacts ${where.length ? 'WHERE ' + where.join(' AND ') : ''} ORDER BY updated_at DESC LIMIT 1000`,
    vals
  );
  return rows;
}
export async function getContact(id) {
  const [[row]] = await pool.execute(`SELECT * FROM contacts WHERE id=?`, [id]);
  return row || null;
}
export async function createContact(payload) {
  const fields = ['account_id','first_name','last_name','email','phone','title','owner_id'];
  const vals = fields.map(f => payload[f] ?? null);
  const [res] = await pool.execute(
    `INSERT INTO contacts (${fields.join(',')}) VALUES (${fields.map(_=>'?').join(',')})`,
    vals
  );
  return await getContact(res.insertId);
}
export async function updateContact(id, payload) {
  const fields = ['account_id','first_name','last_name','email','phone','title','owner_id'];
  const sets = [], vals = [];
  for (const f of fields) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) return await getContact(id);
  vals.push(id);
  await pool.execute(`UPDATE contacts SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getContact(id);
}
export async function deleteContact(id) {
  await pool.execute(`DELETE FROM contacts WHERE id=?`, [id]);
  return { ok: true };
}
