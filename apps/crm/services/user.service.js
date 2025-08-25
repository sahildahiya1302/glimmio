import { pool } from '../config/database.js';

export async function getProfile(id) {
  const [rows] = await pool.execute(
    `SELECT id, username, email, role, first_name, last_name, department
     FROM users WHERE id=?`,
    [id]
  );
  return rows[0];
}
