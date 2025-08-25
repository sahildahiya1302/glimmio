import { pool } from '../config/database.js';

export async function listPipelines() {
  const [pipes] = await pool.execute(`SELECT * FROM pipelines ORDER BY id`);
  const ids = pipes.map(p => p.id);
  let stages = [];
  if (ids.length) {
    const [st] = await pool.execute(
      `SELECT * FROM stages WHERE pipeline_id IN (${ids.map(_=>'?').join(',')}) ORDER BY position, id`,
      ids
    );
    stages = st;
  }
  return pipes.map(p => ({ ...p, stages: stages.filter(s => s.pipeline_id === p.id) }));
}
export async function createPipeline({ name }) {
  const [res] = await pool.execute(`INSERT INTO pipelines (name) VALUES (?)`, [name]);
  const [[row]] = await pool.execute(`SELECT * FROM pipelines WHERE id=?`, [res.insertId]);
  return row;
}
export async function addStage(pipeline_id, payload) {
  const { name, position = 1, is_won = 0, is_lost = 0 } = payload;
  const [res] = await pool.execute(
    `INSERT INTO stages (pipeline_id, name, position, is_won, is_lost) VALUES (?, ?, ?, ?, ?)`,
    [pipeline_id, name, position, is_won ? 1 : 0, is_lost ? 1 : 0]
  );
  const [[row]] = await pool.execute(`SELECT * FROM stages WHERE id=?`, [res.insertId]);
  return row;
}
export async function updateStage(id, payload) {
  const sets = [], vals = [];
  for (const f of ['name','position','is_won','is_lost']) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) {
    const [[row]] = await pool.execute(`SELECT * FROM stages WHERE id=?`, [id]);
    return row;
  }
  vals.push(id);
  await pool.execute(`UPDATE stages SET ${sets.join(', ')} WHERE id=?`, vals);
  const [[row]] = await pool.execute(`SELECT * FROM stages WHERE id=?`, [id]);
  return row;
}
export async function deleteStage(id) {
  await pool.execute(`DELETE FROM stages WHERE id=?`, [id]);
  return { ok: true };
}
