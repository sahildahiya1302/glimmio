import { pool } from '../config/database.js';

export async function listDocuments(user_id) {
  const [rows] = await pool.execute(
    `SELECT id, doc_type, file_name, file_path, mime_type, size, uploaded_at
     FROM employee_documents WHERE user_id=? ORDER BY uploaded_at DESC`,
    [user_id]
  );
  return rows;
}

export async function addDocument({ user_id, doc_type, file_name, file_path, mime_type, size }) {
  const [res] = await pool.execute(
    `INSERT INTO employee_documents (user_id, doc_type, file_name, file_path, mime_type, size)
     VALUES (?, ?, ?, ?, ?, ?)`,
    [user_id, doc_type, file_name, file_path, mime_type, size]
  );
  const [rows] = await pool.execute(`SELECT * FROM employee_documents WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function deleteDocument(id) {
  const [rows] = await pool.execute(`SELECT * FROM employee_documents WHERE id=?`, [id]);
  await pool.execute(`DELETE FROM employee_documents WHERE id=?`, [id]);
  return rows[0] || null; // caller can remove the file from disk if exists
}
