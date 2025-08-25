import { pool } from '../config/database.js';

export async function listPolicies() {
  const [rows] = await pool.execute(`SELECT * FROM attendance_policies ORDER BY id DESC`);
  return rows;
}
export async function getPolicy(id) {
  const [rows] = await pool.execute(`SELECT * FROM attendance_policies WHERE id=?`, [id]);
  return rows[0];
}
export async function createPolicy(data) {
  const { name, ip_allowlist, geo_lat, geo_lng, geo_radius_m, device_bind, require_photo } = data;
  const [res] = await pool.execute(
    `INSERT INTO attendance_policies (name, ip_allowlist, geo_lat, geo_lng, geo_radius_m, device_bind, require_photo)
     VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [name, ip_allowlist || null, geo_lat ?? null, geo_lng ?? null, geo_radius_m ?? null, device_bind ? 1 : 0, require_photo ? 1 : 0]
  );
  return await getPolicy(res.insertId);
}
export async function updatePolicy(id, data) {
  const fields = ['name','ip_allowlist','geo_lat','geo_lng','geo_radius_m','device_bind','require_photo'];
  const sets = [], vals = [];
  for (const f of fields) {
    if (data[f] !== undefined) { sets.push(`${f}=?`); vals.push(data[f]); }
  }
  if (!sets.length) return await getPolicy(id);
  vals.push(id);
  await pool.execute(`UPDATE attendance_policies SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getPolicy(id);
}
export async function deletePolicy(id) {
  await pool.execute(`DELETE FROM attendance_policies WHERE id=?`, [id]);
  return { ok: true };
}
