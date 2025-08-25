import { pool } from '../config/database.js';

export async function getApprovedSumDays(user_id, type_id, year) {
  const [rows] = await pool.execute(
    `SELECT COALESCE(SUM(days),0) AS used
       FROM leave_requests
      WHERE user_id=? AND type_id=? AND status='approved'
        AND YEAR(start_date)=?`,
    [user_id, type_id, year]
  );
  return Number(rows[0]?.used || 0);
}

export async function computeBalance(user_id, type_id, year) {
  const [ent] = await pool.execute(
    `SELECT quota, carried_forward FROM leave_entitlements WHERE user_id=? AND type_id=? AND year=?`,
    [user_id, type_id, year]
  );
  const quota = Number(ent[0]?.quota || 0);
  const cf = Number(ent[0]?.carried_forward || 0);
  const used = await getApprovedSumDays(user_id, type_id, year);
  const balance = quota + cf - used;
  return { quota, carried_forward: cf, used, balance };
}

export async function writeCachedBalance(user_id, type_id, year) {
  const b = await computeBalance(user_id, type_id, year);
  await pool.execute(
    `INSERT INTO leave_balances (user_id, type_id, year, balance)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE balance=VALUES(balance)`,
    [user_id, type_id, year, b.balance]
  );
  return b;
}
