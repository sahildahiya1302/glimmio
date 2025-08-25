import { pool } from '../config/database.js';
import { getEffectiveAssignment } from './attendance.assignments.service.js';
import { ipAllowed, getClientIp } from '../utils/ip.js';
import { distanceMeters } from '../utils/geo.js';

export async function clockIn({ req, user_id, lat, lng, device_id, source }) {
  const today = new Date();
  const yyyyMmDd = today.toISOString().slice(0,10);
  const ip = getClientIp(req);

  // Assignment & policy
  const assignment = await getEffectiveAssignment(user_id, yyyyMmDd);
  if (!assignment) {
    await addException(user_id, yyyyMmDd, 'no_assignment', 'No shift assigned');
    // still allow clock-in as a fallback
  }

  // Policy checks
  if (assignment?.ip_allowlist && !ipAllowed(ip, assignment.ip_allowlist)) {
    await addException(user_id, yyyyMmDd, 'ip_denied', `IP ${ip} not in allowlist`);
  }
  if (assignment?.geo_lat != null && assignment?.geo_lng != null && assignment?.geo_radius_m != null && lat != null && lng != null) {
    const dist = distanceMeters(Number(lat), Number(lng), Number(assignment.geo_lat), Number(assignment.geo_lng));
    if (dist > Number(assignment.geo_radius_m)) {
      await addException(user_id, yyyyMmDd, 'outside_geo', `Distance ${Math.round(dist)}m > ${assignment.geo_radius_m}m`);
    }
  }
  if (assignment?.device_bind && device_id) {
    // NOTE: For simplicity, we only record device_id now; binding registry can be added in Admin later
    // If mismatch logic is required, add a "registered_devices" table and compare.
  }

  // Late check (grace)
  if (assignment) {
    const nowMinutes = today.getHours() * 60 + today.getMinutes();
    const [sh, sm] = String(assignment.start_time).split(':').map(Number);
    const startMinutes = sh * 60 + sm + Number(assignment.grace_minutes || 0);
    if (nowMinutes > startMinutes) {
      await addException(user_id, yyyyMmDd, 'late', `Clock-in after grace (${assignment.grace_minutes}m)`);
    }
  }

  // insert attendance row for today (only if not already)
  const [existing] = await pool.execute(
    `SELECT id FROM attendance WHERE employee_id=? AND DATE(clock_in)=?`,
    [user_id, yyyyMmDd]
  );
  if (existing.length) {
    await addException(user_id, yyyyMmDd, 'duplicate_in', 'Clock-in already exists');
    return { ok: false, message: 'Already clocked in' };
  }

  const [res] = await pool.execute(
    `INSERT INTO attendance
       (employee_id, clock_in, shift_id, policy_id, ip_used, location_lat, location_lng, device_id, source)
     VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)`,
    [user_id, assignment?.shift_id || null, assignment?.policy_id || null, ip,
      lat ?? null, lng ?? null, device_id ?? null, source || 'web']
  );

  return { ok: true, id: res.insertId };
}

export async function clockOut({ req, user_id }) {
  const today = new Date();
  const yyyyMmDd = today.toISOString().slice(0,10);

  const [row] = await pool.execute(
    `SELECT id, clock_in FROM attendance
     WHERE employee_id=? AND DATE(clock_in)=? AND clock_out IS NULL
     ORDER BY clock_in DESC LIMIT 1`,
    [user_id, yyyyMmDd]
  );

  if (!row.length) {
    await addException(user_id, yyyyMmDd, 'duplicate_out', 'No open attendance row to clock-out');
    return { ok: false, message: 'Not clocked in / already clocked out' };
  }

  const id = row[0].id;
  await pool.execute(`UPDATE attendance SET clock_out = NOW() WHERE id=?`, [id]);

  // compute worked minutes
  await pool.execute(
    `UPDATE attendance
       SET worked_minutes = TIMESTAMPDIFF(MINUTE, clock_in, clock_out)
     WHERE id=?`,
    [id]
  );

  return { ok: true };
}

export async function addException(user_id, dateStr, type, details) {
  try {
    await pool.execute(
      `INSERT INTO attendance_exceptions (user_id, date, type, details) VALUES (?, ?, ?, ?)
       ON DUPLICATE KEY UPDATE details = VALUES(details)`,
      [user_id, dateStr, type, details || null]
    );
  } catch {
    // ignore duplicate races
  }
}

export async function getTimesheet(user_id, start_date, end_date) {
  const [rows] = await pool.execute(
    `SELECT a.*, s.name AS shift_name
     FROM attendance a
     LEFT JOIN shifts s ON s.id = a.shift_id
     WHERE a.employee_id=? AND DATE(a.clock_in) BETWEEN ? AND ?
     ORDER BY a.clock_in ASC`,
    [user_id, start_date, end_date]
  );
  return rows;
}

export async function getDailySummary(dateStr) {
  const [rows] = await pool.execute(
    `SELECT u.id AS user_id, u.username, u.first_name, u.last_name,
            MIN(a.clock_in) AS first_in, MAX(a.clock_out) AS last_out,
            SUM(a.worked_minutes) AS total_minutes
     FROM users u
     LEFT JOIN attendance a ON a.employee_id=u.id AND DATE(a.clock_in)=?
     WHERE u.is_active=1
     GROUP BY u.id
     ORDER BY u.id`,
    [dateStr]
  );
  return rows;
}
