import { pool } from '../config/database.js';

export async function pipelineSummary(pipeline_id) {
  const [stages] = await pool.execute(
    `SELECT id, name, is_won, is_lost FROM stages WHERE pipeline_id=? ORDER BY position`, [pipeline_id]
  );
  const ids = stages.map(s => s.id);
  if (!ids.length) return [];

  const [agg] = await pool.execute(
    `SELECT stage_id, COUNT(*) AS cnt, SUM(amount) AS amt
       FROM deals
      WHERE pipeline_id=? GROUP BY stage_id`,
    [pipeline_id]
  );
  const map = new Map(agg.map(r => [r.stage_id, r]));

  return stages.map(s => ({
    stage_id: s.id,
    stage_name: s.name,
    is_won: !!s.is_won,
    is_lost: !!s.is_lost,
    count: Number(map.get(s.id)?.cnt || 0),
    amount: Number(map.get(s.id)?.amt || 0)
  }));
}

export async function winRate(days = 90) {
  const [[won]] = await pool.execute(
    `SELECT COUNT(*) AS c FROM deals WHERE status='won' AND updated_at >= DATE_SUB(NOW(), INTERVAL ? DAY)`, [days]
  );
  const [[lost]] = await pool.execute(
    `SELECT COUNT(*) AS c FROM deals WHERE status='lost' AND updated_at >= DATE_SUB(NOW(), INTERVAL ? DAY)`, [days]
  );
  const total = Number(won.c) + Number(lost.c);
  const rate = total ? Math.round((Number(won.c) / total) * 10000) / 100 : 0;
  return { days, won: Number(won.c), lost: Number(lost.c), win_rate_pct: rate };
}

export async function revenueForecast(months = 3) {
  const [rows] = await pool.execute(
    `SELECT DATE_FORMAT(close_date, '%Y-%m-01') AS month,
            SUM(CASE WHEN status='won' THEN amount ELSE 0 END)               AS won_amount,
            SUM(CASE WHEN status='open' THEN amount ELSE 0 END)              AS open_amount
       FROM deals
      WHERE close_date BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
                          AND DATE_ADD(LAST_DAY(DATE_ADD(CURDATE(), INTERVAL ? MONTH)), INTERVAL 0 DAY)
      GROUP BY month ORDER BY month`,
    [months - 1]
  );
  return rows.map(r => ({
    month: r.month,
    won_amount: Number(r.won_amount || 0),
    open_amount: Number(r.open_amount || 0)
  }));
}
