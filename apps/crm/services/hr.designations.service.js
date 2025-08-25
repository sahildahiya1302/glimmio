import { pool } from '../config/database.js';

export async function listDesignations() {
  const [rows] = await pool.execute(`SELECT * FROM designations ORDER BY level, name`);
  return rows;
}

export async function createDesignation(payload) {
  const { name, level = 0 } = payload;
  const [res] = await pool.execute(
    `INSERT INTO designations (name, level) VALUES (?, ?)`,
    [name, level]
  );
  const [rows] = await pool.execute(`SELECT * FROM designations WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function updateDesignation(id, payload) {
  const sets = [];
  const values = [];
  if (payload.name !== undefined) { sets.push('name=?'); values.push(payload.name); }
  if (payload.level !== undefined) { sets.push('level=?'); values.push(payload.level); }
  if (!sets.length) return await getDesignation(id);
  values.push(id);
  await pool.execute(`UPDATE designations SET ${sets.join(', ')} WHERE id=?`, values);
  return await getDesignation(id);
}

export async function deleteDesignation(id) {
  await pool.execute(`DELETE FROM designations WHERE id=?`, [id]);
  return { ok: true };
}

export async function getDesignation(id) {
  const [rows] = await pool.execute(`SELECT * FROM designations WHERE id=?`, [id]);
  return rows[0];
}
