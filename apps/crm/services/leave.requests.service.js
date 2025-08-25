import { pool } from '../config/database.js';
import { listHolidays } from './leave.holidays.service.js';
import { computeBalance } from './leave.balance.service.js';
import { computeDays } from '../utils/leave_days.js';

async function getType(type_id) {
  const [rows] = await pool.execute(`SELECT * FROM leave_types WHERE id=? AND is_active=1`, [type_id]);
  return rows[0] || null;
}

async function getUser(user_id) {
  const [rows] = await pool.execute(`SELECT id, role, manager_id, first_name, last_name, gender FROM users WHERE id=?`, [user_id]);
  return rows[0] || null;
}

async function getAdmins() {
  const [rows] = await pool.execute(`SELECT id FROM users WHERE role='admin' AND is_active=1 ORDER BY id ASC LIMIT 5`);
  return rows.map(r => r.id);
}

async function createApprovalChain(user, type) {
  const chain = [];
  if (user?.manager_id) chain.push(user.manager_id);
  const admins = await getAdmins();
  if (!admins.includes(user.manager_id)) chain.push(admins[0]); // ensure at least one admin
  return chain.filter(Boolean);
}

export async function listRequests({ user_id, status } = {}) {
  const where = [];
  const vals = [];
  if (user_id) { where.push('lr.user_id=?'); vals.push(user_id); }
  if (status) { where.push('lr.status=?'); vals.push(status); }
  const sql = `
    SELECT lr.*, lt.name AS type_name
    FROM leave_requests lr
    JOIN leave_types lt ON lt.id = lr.type_id
    ${where.length ? 'WHERE ' + where.join(' AND ') : ''}
    ORDER BY lr.created_at DESC
    LIMIT 1000
  `;
  const [rows] = await pool.execute(sql, vals);
  return rows;
}

export async function getRequest(id) {
  const [rows] = await pool.execute(
    `SELECT lr.*, lt.name AS type_name
       FROM leave_requests lr
       JOIN leave_types lt ON lt.id = lr.type_id
      WHERE lr.id=?`,
    [id]
  );
  const req = rows[0];
  if (!req) return null;

  const [steps] = await pool.execute(
    `SELECT la.*,
            CONCAT(u.first_name,' ',u.last_name) AS approver_name
       FROM leave_approvals la
       JOIN users u ON u.id = la.approver_id
      WHERE la.request_id=?
      ORDER BY la.step_no ASC`,
    [id]
  );

  req.approvals = steps;
  return req;
}

export async function applyRequest(user_id, payload) {
  const type = await getType(payload.type_id);
  if (!type) throw new Error('INVALID_TYPE');

  // Basic gender restriction
  const user = await getUser(user_id);
  if (type.gender_restriction && user?.gender && type.gender_restriction !== user.gender) {
    throw new Error('TYPE_NOT_ALLOWED_FOR_GENDER');
  }

  // Notice period
  const today = new Date().toISOString().slice(0,10);
  const noticeDays = Math.floor((new Date(payload.start_date) - new Date(today)) / (1000*60*60*24));
  if (noticeDays < Number(type.min_notice_days || 0)) throw new Error('NOTICE_TOO_SHORT');

  // Holidays list for range
  const holidays = await listHolidays({
    start: payload.start_date,
    end: payload.end_date
  });
  const holidaysSet = new Set(holidays.map(h => h.date.toISOString().slice(0,10)));

  // Compute effective days
  const days = computeDays({
    start_date: payload.start_date,
    end_date: payload.end_date,
    half_day: Number(payload.half_day || 0) === 1,
    weekend_inclusive: Number(type.weekend_inclusive || 0) === 1,
    holidaysSet
  });

  if (type.max_consecutive_days && days > Number(type.max_consecutive_days)) throw new Error('EXCEEDS_MAX_CONSECUTIVE');

  // Balance check
  const year = new Date(payload.start_date).getFullYear();
  const bal = await computeBalance(user_id, payload.type_id, year);
  if (!type.negative_balance_allowed && days > bal.balance) throw new Error('INSUFFICIENT_BALANCE');

  // Insert request
  const [res] = await pool.execute(
    `INSERT INTO leave_requests (user_id, type_id, start_date, end_date, half_day, days, reason, status, doc_file)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NULL)`,
    [user_id, payload.type_id, payload.start_date, payload.end_date,
     Number(payload.half_day || 0), days, payload.reason || null]
  );
  const request_id = res.insertId;

  // Build approval chain (manager -> admin)
  const chain = await createApprovalChain(user, type);
  let step = 1;
  for (const approver of chain) {
    await pool.execute(
      `INSERT INTO leave_approvals (request_id, step_no, approver_id, status) VALUES (?, ?, ?, 'pending')`,
      [request_id, step++, approver]
    );
  }

  const [rows] = await pool.execute(`SELECT * FROM leave_requests WHERE id=?`, [request_id]);
  return rows[0];
}

export async function attachDocument(request_id, relPath) {
  await pool.execute(`UPDATE leave_requests SET doc_file=? WHERE id=?`, [relPath, request_id]);
  const [rows] = await pool.execute(`SELECT * FROM leave_requests WHERE id=?`, [request_id]);
  return rows[0];
}

export async function actOnRequest({ request_id, approver_id, action, comment }) {
  // find current pending step for this approver
  const [pending] = await pool.execute(
    `SELECT * FROM leave_approvals
      WHERE request_id=? AND approver_id=? AND status='pending'
      ORDER BY step_no ASC LIMIT 1`,
    [request_id, approver_id]
  );
  if (!pending.length) throw new Error('NO_PENDING_STEP');

  const status = action === 'approve' ? 'approved' : 'rejected';
  await pool.execute(
    `UPDATE leave_approvals SET status=?, comment=?, acted_at=NOW() WHERE id=?`,
    [status, comment || null, pending[0].id]
  );

  if (status === 'rejected') {
    await pool.execute(`UPDATE leave_requests SET status='rejected' WHERE id=?`, [request_id]);
    return { final: true, status: 'rejected' };
  }

  // check if any pending steps remain
  const [remain] = await pool.execute(
    `SELECT COUNT(*) AS c FROM leave_approvals WHERE request_id=? AND status='pending'`,
    [request_id]
  );
  if (Number(remain[0].c) === 0) {
    await pool.execute(`UPDATE leave_requests SET status='approved' WHERE id=?`, [request_id]);
    return { final: true, status: 'approved' };
  }
  return { final: false, status: 'pending' };
}
