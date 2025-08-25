import { pool } from '../config/database.js';

export async function listDeals({ pipeline_id, stage_id, owner_id, q } = {}) {
  const where = [], vals = [];
  if (pipeline_id) { where.push('d.pipeline_id=?'); vals.push(pipeline_id); }
  if (stage_id) { where.push('d.stage_id=?'); vals.push(stage_id); }
  if (owner_id) { where.push('d.owner_id=?'); vals.push(owner_id); }
  if (q) { where.push('(d.title LIKE ? OR a.name LIKE ? OR c.email LIKE ?)'); vals.push(`%${q}%`,`%${q}%`,`%${q}%`); }

  const sql = `
    SELECT d.*, a.name AS account_name, CONCAT(c.first_name,' ',c.last_name) AS contact_name,
           s.name AS stage_name, p.name AS pipeline_name
    FROM deals d
    JOIN pipelines p ON p.id = d.pipeline_id
    JOIN stages s ON s.id = d.stage_id
    LEFT JOIN accounts a ON a.id = d.account_id
    LEFT JOIN contacts c ON c.id = d.contact_id
    ${where.length ? 'WHERE ' + where.join(' AND ') : ''}
    ORDER BY d.updated_at DESC
    LIMIT 1000`;
  const [rows] = await pool.execute(sql, vals);
  return rows;
}

export async function getDeal(id) {
  const [rows] = await pool.execute(
    `SELECT d.*, a.name AS account_name, CONCAT(c.first_name,' ',c.last_name) AS contact_name,
            s.name AS stage_name, p.name AS pipeline_name
     FROM deals d
     JOIN pipelines p ON p.id = d.pipeline_id
     JOIN stages s ON s.id = d.stage_id
     LEFT JOIN accounts a ON a.id = d.account_id
     LEFT JOIN contacts c ON c.id = d.contact_id
     WHERE d.id=?`, [id]
  );
  return rows[0] || null;
}

export async function createDeal(payload) {
  const fields = ['title','account_id','contact_id','amount','currency','pipeline_id','stage_id','owner_id','close_date'];
  const vals = fields.map(f => payload[f] ?? null);
  const [res] = await pool.execute(
    `INSERT INTO deals (${fields.join(',')}) VALUES (${fields.map(_=>'?').join(',')})`,
    vals
  );
  return await getDeal(res.insertId);
}

export async function updateDeal(id, payload) {
  const fields = ['title','account_id','contact_id','amount','currency','pipeline_id','stage_id','owner_id','close_date','status'];
  const sets = [], vals = [];
  for (const f of fields) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) return await getDeal(id);
  vals.push(id);
  await pool.execute(`UPDATE deals SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getDeal(id);
}

export async function moveDeal(id, stage_id) {
  await pool.execute(`UPDATE deals SET stage_id=? WHERE id=?`, [stage_id, id]);
  return await getDeal(id);
}

export async function setDealStatus(id, status) {
  await pool.execute(`UPDATE deals SET status=? WHERE id=?`, [status, id]);
  return await getDeal(id);
}
export async function listKanban(pipeline_id) {
  // stages for the pipeline
  const [stages] = await pool.execute(`SELECT * FROM stages WHERE pipeline_id=? ORDER BY position, id`, [pipeline_id]);
  if (!stages.length) return [];
  const [deals] = await pool.execute(
    `SELECT d.*, a.name AS account_name
       FROM deals d
       LEFT JOIN accounts a ON a.id=d.account_id
      WHERE d.pipeline_id=?
      ORDER BY d.updated_at DESC`,
    [pipeline_id]
  );
  return stages.map(s => ({
    stage: s,
    deals: deals.filter(d => d.stage_id === s.id)
  }));
}

