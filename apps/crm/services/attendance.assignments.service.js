import { pool } from '../config/database.js';

export async function listAssignments(user_id) {
  if (user_id) {
    const [rows] = await pool.execute(
      `SELECT sa.*, s.name AS shift_name, p.name AS policy_name
       FROM shift_assignments sa
       JOIN shifts s ON s.id = sa.shift_id
       LEFT JOIN attendance_policies p ON p.id = sa.policy_id
       WHERE sa.user_id=?
       ORDER BY sa.effective_from DESC`,
      [user_id]
    );
    return rows;
  }
  const [rows] = await pool.execute(
    `SELECT sa.*, u.username, s.name AS shift_name, p.name AS policy_name
     FROM shift_assignments sa
     JOIN users u ON u.id = sa.user_id
     JOIN shifts s ON s.id = sa.shift_id
     LEFT JOIN attendance_policies p ON p.id = sa.policy_id
     ORDER BY sa.effective_from DESC
     LIMIT 1000`
  );
  return rows;
}

export async function assignShift({ user_id, shift_id, policy_id, effective_from, effective_to }) {
  const [res] = await pool.execute(
    `INSERT INTO shift_assignments (user_id, shift_id, policy_id, effective_from, effective_to)
     VALUES (?, ?, ?, ?, ?)`,
    [user_id, shift_id, policy_id ?? null, effective_from, effective_to ?? null]
  );
  const [rows] = await pool.execute(`SELECT * FROM shift_assignments WHERE id=?`, [res.insertId]);
  return rows[0];
}

export async function getEffectiveAssignment(user_id, date) {
  const [rows] = await pool.execute(
    `SELECT sa.*, s.start_time, s.end_time, s.grace_minutes, s.working_days,
            p.ip_allowlist, p.geo_lat, p.geo_lng, p.geo_radius_m, p.device_bind, p.require_photo
     FROM shift_assignments sa
     JOIN shifts s ON s.id = sa.shift_id
     LEFT JOIN attendance_policies p ON p.id = sa.policy_id
     WHERE sa.user_id=?
       AND sa.effective_from <= ?
       AND (sa.effective_to IS NULL OR sa.effective_to >= ?)
     ORDER BY sa.effective_from DESC
     LIMIT 1`,
    [user_id, date, date]
  );
  return rows[0] || null;
}
