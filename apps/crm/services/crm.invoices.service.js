import { pool } from '../config/database.js';
import { nextNumber } from '../utils/sequences.js';
import { buildInvoicePDF } from '../utils/pdf.invoice.js';

export async function getInvoice(id) {
  const [[row]] = await pool.execute(`SELECT * FROM invoices WHERE id=?`, [id]);
  if (!row) return null;
  const [items] = await pool.execute(`SELECT * FROM invoice_items WHERE invoice_id=?`, [id]);
  return { ...row, items };
}

export async function createInvoice({ account_id, contact_id, quote_id, currency, owner_id, due_date }) {
  const invoice_no = await nextNumber('INV', 'INV-');
  const [res] = await pool.execute(
    `INSERT INTO invoices (invoice_no, account_id, contact_id, quote_id, owner_id, currency, due_date)
     VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [invoice_no, account_id, contact_id || null, quote_id || null, owner_id || null, currency || 'INR', due_date || null]
  );
  const id = res.insertId;

  if (quote_id) {
    const [items] = await pool.execute(`SELECT name, price, qty, discount, tax_rate
                                        FROM quote_items WHERE quote_id=?`, [quote_id]);
    for (const it of items) {
      await addInvoiceItem(id, { name: it.name, price: it.price, qty: it.qty, discount: it.discount, tax_rate: it.tax_rate });
    }
  }
  return await getInvoice(id);
}

export async function addInvoiceItem(invoice_id, { product_id, name, price, qty = 1, discount = 0, tax_rate = 0 }) {
  const amount = ((Number(price) * Number(qty)) - Number(discount)) * (1 + Number(tax_rate)/100);
  const [res] = await pool.execute(
    `INSERT INTO invoice_items (invoice_id, product_id, name, price, qty, discount, tax_rate, amount)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
    [invoice_id, product_id || null, name, price, qty, discount, tax_rate, amount]
  );
  await recomputeInvoice(invoice_id);
  const [[row]] = await pool.execute(`SELECT * FROM invoice_items WHERE id=?`, [res.insertId]);
  return row;
}

export async function recomputeInvoice(invoice_id) {
  const [[sum]] = await pool.execute(
    `SELECT
       SUM(price * qty - discount) AS subtotal,
       SUM((price * qty - discount) * (tax_rate/100)) AS tax_total,
       SUM(amount) AS total
     FROM invoice_items WHERE invoice_id=?`,
    [invoice_id]
  );
  await pool.execute(
    `UPDATE invoices SET subtotal=?, tax_total=?, total=? WHERE id=?`,
    [sum.subtotal || 0, sum.tax_total || 0, sum.total || 0, invoice_id]
  );
}

export async function setInvoiceStatus(id, status) {
  await pool.execute(`UPDATE invoices SET status=? WHERE id=?`, [status, id]);
  return await getInvoice(id);
}

/* --- NEW: PDF generation --- */
export async function generateInvoicePdf(id) {
  const invoice = await getInvoice(id);
  if (!invoice) return null;
  const [[account]] = await pool.execute(`SELECT * FROM accounts WHERE id=?`, [invoice.account_id]);
  const [[contact]] = await pool.execute(`SELECT * FROM contacts WHERE id=?`, [invoice.contact_id]);
  const pdfPath = await buildInvoicePDF({ invoice, account, contact });
  return pdfPath;
}
// Add near the bottom, before export default:
router.get('/:id/pdf', requireAuth, async (req, res, next) => {
  try {
    const { generateInvoicePdf } = await import('../services/crm.invoices.service.js');
    const p = await generateInvoicePdf(Number(req.params.id));
    if (!p) return res.status(404).json({ message: 'Not found' });
    res.download(p);
  } catch (e) { next(e); }
});
