import { pool } from '../config/database.js';

export async function nextNumber(seqType, prefix = '') {
  const year = new Date().getFullYear();
  await pool.execute(
    `INSERT INTO doc_sequences (seq_type, seq_year, next_seq)
     VALUES (?, ?, 1)
     ON DUPLICATE KEY UPDATE next_seq = next_seq`,
    [seqType, year]
  );
  // increment and fetch atomically
  await pool.execute(
    `UPDATE doc_sequences SET next_seq = next_seq + 1 WHERE seq_type=? AND seq_year=?`,
    [seqType, year]
  );
  const [[row]] = await pool.execute(
    `SELECT next_seq FROM doc_sequences WHERE seq_type=? AND seq_year=?`,
    [seqType, year]
  );
  const seq = Number(row.next_seq) - 1;
  return `${prefix}${year}-${String(seq).padStart(5, '0')}`;
}
