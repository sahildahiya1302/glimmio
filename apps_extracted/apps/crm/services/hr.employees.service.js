import { pool } from '../config/database.js';
import { hashPassword } from '../utils/crypto.js';

export async function listEmployees({ q, dept, desig, active } = {}) {
  const where = [];
  const args = [];
  if (q) {
    where.push(`(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.employee_code LIKE ?)`);
    args.push(`%${q}%`,`%${q}%`,`%${q}%`,`%${q}%`,`%${q}%`);
  }
  if (dept) { where.push(`u.department_id=?`); args.push(dept); }
  if (desig) { where.push(`u.designation_id=?`); args.push(desig); }
  if (active !== undefined) { where.push(`u.is_active=?`); args.push(active); }

  const sql = `
    SELECT u.id, u.username, u.email, u.role, u.first_name, u.last_name,
           u.department_id, d.name AS department_name,
           u.designation_id, g.name AS designation_name,
           u.manager_id, u.employee_code, u.doj, u.is_active,
           CONCAT(m.first_name, ' ', m.last_name) AS manager_name
    FROM users u
    LEFT JOIN departments d ON d.id = u.department_id
    LEFT JOIN designations g ON g.id = u.designation_id
    LEFT JOIN users m ON m.id = u.manager_id
    ${where.length ? 'WHERE ' + where.join(' AND ') : ''}
    ORDER BY u.created_at DESC
    LIMIT 500
  `;
  const [rows] = await pool.execute(sql, args);
  return rows;
}

export async function getEmployee(id) {
  const [rows] = await pool.execute(
    `SELECT id, username, email, role, first_name, last_name, department_id, designation_id, manager_id,
            employee_code, doj, is_active
     FROM users WHERE id=?`, [id]
  );
  const user = rows[0];
  if (!user) return null;

  const [profileRows] = await pool.execute(
    `SELECT * FROM employee_profiles WHERE user_id=?`, [id]
  );
  user.profile = profileRows[0] || null;
  return user;
}

export async function createEmployee(payload) {
  const {
    username, email, password, role = 'employee',
    first_name, last_name,
    department_id, designation_id, manager_id, doj, employee_code
  } = payload;

  const hashed = await hashPassword(password);
  const [res] = await pool.execute(
    `INSERT INTO users (username, email, password, role, first_name, last_name, department_id, designation_id, manager_id, doj, employee_code)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [username, email, hashed, role, first_name || null, last_name || null,
      department_id || null, designation_id || null, manager_id || null, doj || null, employee_code || null]
  );
  const id = res.insertId;

  await pool.execute(`INSERT INTO employee_profiles (user_id) VALUES (?)`, [id]);
  return await getEmployee(id);
}

export async function updateEmployee(id, payload) {
  const fields = ['email','role','first_name','last_name','department_id','designation_id','manager_id','doj','is_active','employee_code'];
  const sets = [];
  const values = [];
  for (const f of fields) {
    if (payload[f] !== undefined) {
      sets.push(`${f}=?`);
      values.push(payload[f] ?? null);
    }
  }
  if (sets.length) {
    values.push(id);
    await pool.execute(`UPDATE users SET ${sets.join(', ')} WHERE id=?`, values);
  }
  return await getEmployee(id);
}

export async function upsertProfile(user_id, payload) {
  const [exists] = await pool.execute(`SELECT id FROM employee_profiles WHERE user_id=?`, [user_id]);
  const keys = [
    'phone','alt_phone','dob','gender','blood_group','address_line1','address_line2','city','state',
    'postal_code','country','emergency_contact_name','emergency_contact_phone','PAN','AADHAAR',
    'bank_name','bank_account','ifsc'
  ];
  const vals = keys.map(k => payload[k] ?? null);

  if (exists.length) {
    const sets = keys.map(k => `${k}=?`).join(', ');
    await pool.execute(`UPDATE employee_profiles SET ${sets} WHERE user_id=?`, [...vals, user_id]);
  } else {
    await pool.execute(
      `INSERT INTO employee_profiles ( ${keys.join(', ')} , user_id) VALUES (${keys.map(_=>'?').join(', ')}, ?)`,
      [...vals, user_id]
    );
  }
  return await getEmployee(user_id);
}

export async function softDeleteEmployee(id) {
  await pool.execute(`UPDATE users SET is_active=0 WHERE id=?`, [id]);
  return { ok: true };
}

export async function getOrgChart() {
  // returns array of {id, name, manager_id}
  const [rows] = await pool.execute(
    `SELECT id, CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,'')) AS name, manager_id
     FROM users WHERE is_active=1 ORDER BY manager_id`
  );
  return rows;
}

export async function exportEmployees() {
  const [rows] = await pool.execute(
    `SELECT u.id, u.username, u.email, u.role, u.first_name, u.last_name,
            u.employee_code, u.doj, u.is_active,
            d.name AS department, g.name AS designation
     FROM users u
     LEFT JOIN departments d ON d.id = u.department_id
     LEFT JOIN designations g ON g.id = u.designation_id
     ORDER BY u.id`
  );
  return rows;
}

export async function bulkImportEmployees(rows) {
  // rows: array of objects parsed from CSV columns
  // required: username, email, password
  const created = [];
  for (const r of rows) {
    if (!r.username || !r.email || !r.password) continue;
    const payload = {
      username: String(r.username).trim(),
      email: String(r.email).trim(),
      password: String(r.password),
      role: r.role || 'employee',
      first_name: r.first_name || null,
      last_name: r.last_name || null,
      employee_code: r.employee_code || null,
      doj: r.doj || null
    };

    if (r.department) {
      const [dep] = await pool.execute(`SELECT id FROM departments WHERE name=?`, [r.department]);
      payload.department_id = dep[0]?.id || null;
    }
    if (r.designation) {
      const [des] = await pool.execute(`SELECT id FROM designations WHERE name=?`, [r.designation]);
      payload.designation_id = des[0]?.id || null;
    }
    const emp = await createEmployee(payload);
    created.push(emp);
  }
  return created;
}
