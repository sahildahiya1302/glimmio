import { pool } from '../config/database.js';
import { nextNumber } from '../utils/sequences.js';

export async function getQuote(id) {
  const [[row]] = await pool.execute(`SELECT * FROM quotes WHERE id=?`, [id]);
  if (!row) return null;
  const [items] = await pool.execute(`SELECT * FROM quote_items WHERE quote_id=?`, [id]);
  return { ...row, items };
}

export async function createQuote({ account_id, contact_id, deal_id, currency, owner_id }) {
  const quote_no = await nextNumber('QUOTE', 'Q-');
  const [res] = await pool.execute(
    `INSERT INTO quotes (quote_no, account_id, contact_id, deal_id, owner_id, currency)
     VALUES (?, ?, ?, ?, ?, ?)`,
    [quote_no, account_id, contact_id || null, deal_id || null, owner_id || null, currency || 'INR']
  );
  return await getQuote(res.insertId);
}

export async function addQuoteItem(quote_id, { product_id, name, price, qty = 1, discount = 0, tax_rate = 0 }) {
  const amount = ((Number(price) * Number(qty)) - Number(discount)) * (1 + Number(tax_rate)/100);
  const [res] = await pool.execute(
    `INSERT INTO quote_items (quote_id, product_id, name, price, qty, discount, tax_rate, amount)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
    [quote_id, product_id || null, name, price, qty, discount, tax_rate, amount]
  );
  await recomputeQuote(quote_id);
  const [[row]] = await pool.execute(`SELECT * FROM quote_items WHERE id=?`, [res.insertId]);
  return row;
}

export async function recomputeQuote(quote_id) {
  const [[sum]] = await pool.execute(
    `SELECT
       SUM(price * qty - discount) AS subtotal,
       SUM((price * qty - discount) * (tax_rate/100)) AS tax_total,
       SUM(amount) AS total
     FROM quote_items WHERE quote_id=?`,
    [quote_id]
  );
  await pool.execute(
    `UPDATE quotes SET subtotal=?, tax_total=?, total=? WHERE id=?`,
    [sum.subtotal || 0, sum.tax_total || 0, sum.total || 0, quote_id]
  );
}

export async function setQuoteStatus(id, status) {
  await pool.execute(`UPDATE quotes SET status=? WHERE id=?`, [status, id]);
  return await getQuote(id);
}
