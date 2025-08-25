import { pool } from '../config/database.js';

export async function listDepartments() {
  const [rows] = await pool.execute(`SELECT * FROM departments ORDER BY name`);
  return rows;
}

export async function createDepartment(payload) {
  const { name, code, description } = payload;
  const [res] = await pool.execute(
    `INSERT INTO departments (name, code, description) VALUES (?, ?, ?)`,
    [name, code || null, description || null]
  );
  const [rows] = await pool.execute(`SELECT * FROM departments WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function updateDepartment(id, payload) {
  const fields = ['name','code','description'];
  const sets = [];
  const values = [];
  for (const f of fields) {
    if (payload[f] !== undefined) {
      sets.push(`${f}=?`);
      values.push(payload[f] || null);
    }
  }
  if (!sets.length) return await getDepartment(id);
  values.push(id);
  await pool.execute(`UPDATE departments SET ${sets.join(', ')} WHERE id=?`, values);
  return await getDepartment(id);
}

export async function deleteDepartment(id) {
  await pool.execute(`DELETE FROM departments WHERE id=?`, [id]);
  return { ok: true };
}

export async function getDepartment(id) {
  const [rows] = await pool.execute(`SELECT * FROM departments WHERE id=?`, [id]);
  return rows[0];
}
