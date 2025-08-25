import { pool } from '../config/database.js';

export async function searchAll(q, limit = 5) {
  const like = `%${q}%`;
  const [accounts] = await pool.execute(
    `SELECT id, name, domain, 'account' AS type FROM accounts
      WHERE name LIKE ? OR domain LIKE ? ORDER BY updated_at DESC LIMIT ?`, [like, like, limit]
  );
  const [contacts] = await pool.execute(
    `SELECT id, CONCAT_WS(' ',first_name,last_name) AS name, email, 'contact' AS type FROM contacts
      WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? ORDER BY updated_at DESC LIMIT ?`,
    [like, like, like, limit]
  );
  const [deals] = await pool.execute(
    `SELECT id, title AS name, amount, 'deal' AS type FROM deals
      WHERE title LIKE ? ORDER BY updated_at DESC LIMIT ?`, [like, limit]
  );
  const [leads] = await pool.execute(
    `SELECT id, CONCAT_WS(' ',first_name,last_name) AS name, email, 'lead' AS type FROM leads
      WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR company LIKE ? ORDER BY updated_at DESC LIMIT ?`,
    [like, like, like, like, limit]
  );
  return { accounts, contacts, deals, leads };
}
