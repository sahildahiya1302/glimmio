import { pool } from '../config/database.js';

/* Basic list/get/crud */
export async function listLeads({ q, status, owner_id } = {}) {
  const where = [], vals = [];
  if (q) {
    where.push('(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR company LIKE ?)');
    vals.push(`%${q}%`,`%${q}%`,`%${q}%`,`%${q}%`,`%${q}%`);
  }
  if (status) { where.push('status=?'); vals.push(status); }
  if (owner_id) { where.push('owner_id=?'); vals.push(owner_id); }
  const [rows] = await pool.execute(
    `SELECT * FROM leads ${where.length ? 'WHERE ' + where.join(' AND ') : ''} ORDER BY updated_at DESC LIMIT 1000`,
    vals
  );
  return rows;
}

export async function getLead(id) {
  const [[row]] = await pool.execute(`SELECT * FROM leads WHERE id=?`, [id]);
  return row || null;
}

export async function createLead(payload) {
  const fields = ['first_name','last_name','email','phone','company','title','source','status','owner_id'];
  const vals = fields.map(f => payload[f] ?? null);
  const [res] = await pool.execute(
    `INSERT INTO leads (${fields.join(',')}) VALUES (${fields.map(_=>'?').join(',')})`,
    vals
  );
  return await getLead(res.insertId);
}

export async function updateLead(id, payload) {
  const fields = ['first_name','last_name','email','phone','company','title','source','status','owner_id'];
  const sets = [], vals = [];
  for (const f of fields) if (payload[f] !== undefined) { sets.push(`${f}=?`); vals.push(payload[f]); }
  if (!sets.length) return await getLead(id);
  vals.push(id);
  await pool.execute(`UPDATE leads SET ${sets.join(', ')} WHERE id=?`, vals);
  return await getLead(id);
}

export async function deleteLead(id) {
  await pool.execute(`DELETE FROM leads WHERE id=?`, [id]);
  return { ok: true };
}

/* Conversion */
export async function convertLead(id, opts = {}) {
  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();
    const [[lead]] = await conn.execute(`SELECT * FROM leads WHERE id=? FOR UPDATE`, [id]);
    if (!lead) throw new Error('LEAD_NOT_FOUND');
    if (lead.status === 'converted') {
      const res = await getLead(id);
      await conn.release();
      return { already: true, lead: res };
    }

    // Account: try to find by company name
    let account_id = null;
    if (lead.company) {
      const [[acc]] = await conn.execute(`SELECT id FROM accounts WHERE name=?`, [lead.company]);
      if (acc) account_id = acc.id;
      else {
        const [ins] = await conn.execute(
          `INSERT INTO accounts (name, owner_id) VALUES (?, ?)`,
          [lead.company, lead.owner_id || null]
        );
        account_id = ins.insertId;
      }
    }

    // Contact: try by email
    let contact_id = null;
    if (lead.email) {
      const [[ct]] = await conn.execute(`SELECT id FROM contacts WHERE email=?`, [lead.email]);
      if (ct) contact_id = ct.id;
    }
    if (!contact_id) {
      const [ins] = await conn.execute(
        `INSERT INTO contacts (account_id, first_name, last_name, email, phone, title, owner_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)`,
        [account_id || null, lead.first_name || null, lead.last_name || null,
         lead.email || null, lead.phone || null, lead.title || null, lead.owner_id || null]
      );
      contact_id = ins.insertId;
    }

    // Optional Deal
    let deal_id = null;
    if (opts.create_deal) {
      // Default pipeline/stage = first stage of "Default" pipeline
      let pipeline_id = opts.pipeline_id;
      let stage_id = opts.stage_id;

      if (!pipeline_id || !stage_id) {
        const [[pipe]] = await conn.execute(`SELECT id FROM pipelines WHERE name='Default' ORDER BY id LIMIT 1`);
        pipeline_id = pipeline_id || pipe?.id;
        const [[st]] = await conn.execute(`SELECT id FROM stages WHERE pipeline_id=? ORDER BY position LIMIT 1`, [pipeline_id]);
        stage_id = stage_id || st?.id;
      }

      const title = opts.title || (lead.company ? `${lead.company} - Opportunity` : `${lead.first_name || ''} ${lead.last_name || ''}`.trim()) || 'New Deal';
      const amount = Number(opts.amount || 0);
      const [ins] = await conn.execute(
        `INSERT INTO deals (title, account_id, contact_id, amount, currency, pipeline_id, stage_id, owner_id)
         VALUES (?, ?, ?, ?, 'INR', ?, ?, ?)`,
        [title, account_id || null, contact_id || null, amount, pipeline_id, stage_id, lead.owner_id || null]
      );
      deal_id = ins.insertId;
    }

    // Update lead as converted
    await conn.execute(
      `UPDATE leads SET status='converted', account_id=?, contact_id=?, deal_id=? WHERE id=?`,
      [account_id, contact_id, deal_id, id]
    );

    await conn.commit();
    const updated = await getLead(id);
    return { lead: updated, account_id, contact_id, deal_id };
  } catch (e) {
    await conn.rollback();
    throw e;
  } finally {
    conn.release();
  }
}
