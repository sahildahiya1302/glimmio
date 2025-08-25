import fs from 'fs';
import path from 'path';
import PDFDocument from 'pdfkit';
import { crmUploadPath, sanitizeFilename } from './storage.js';

export function ensureInvoiceDir() {
  const dir = crmUploadPath('invoices');
  fs.mkdirSync(dir, { recursive: true });
  return dir;
}

export function buildInvoicePDF({ invoice, account, contact }) {
  const dir = ensureInvoiceDir();
  const fileName = sanitizeFilename(`${invoice.invoice_no}.pdf`);
  const outPath = path.join(dir, fileName);

  const doc = new PDFDocument({ size: 'A4', margin: 50 });
  const stream = fs.createWriteStream(outPath);
  doc.pipe(stream);

  // Header
  doc.fontSize(24).text('INVOICE', { align: 'right' });
  doc.moveDown(0.5);
  doc.fontSize(12)
    .text(`Invoice No: ${invoice.invoice_no}`, { align: 'right' })
    .text(`Date: ${new Date(invoice.created_at).toISOString().slice(0,10)}`, { align: 'right' })
    .text(`Due: ${invoice.due_date || '-'}`, { align: 'right' });

  // From / To
  doc.moveDown(1);
  doc.fontSize(14).text('From:', { underline: true });
  doc.fontSize(11).text('Glimmio').text('support@glimmio.local').moveDown(0.5);

  doc.fontSize(14).text('Bill To:', { underline: true });
  doc.fontSize(11)
    .text(account?.name || '-')
    .text(contact ? `${contact.first_name || ''} ${contact.last_name || ''}`.trim() : '')
    .text(contact?.email || '')
    .moveDown(1);

  // Items table
  const tableTop = doc.y + 10;
  const col = (i) => [50, 290, 360, 410, 460, 520][i];

  doc.fontSize(12).text('Item', col(0), tableTop)
    .text('Qty', col(1), tableTop)
    .text('Price', col(2), tableTop)
    .text('Disc', col(3), tableTop)
    .text('Tax%', col(4), tableTop)
    .text('Amount', col(5), tableTop);
  doc.moveTo(50, tableTop + 15).lineTo(560, tableTop + 15).stroke();

  let y = tableTop + 25;
  for (const it of invoice.items || []) {
    doc.fontSize(11)
      .text(it.name, col(0), y, { width: 230 })
      .text(String(it.qty), col(1), y)
      .text(Number(it.price).toFixed(2), col(2), y)
      .text(Number(it.discount).toFixed(2), col(3), y)
      .text(Number(it.tax_rate).toFixed(2), col(4), y)
      .text(Number(it.amount).toFixed(2), col(5), y);
    y += 18;
    if (y > 720) { doc.addPage(); y = 80; }
  }

  // Totals
  y += 10;
  doc.moveTo(360, y).lineTo(560, y).stroke();
  y += 8;
  const right = (label, val) => { doc.fontSize(12).text(label, 360, y).text(val, 500, y, { width: 60, align: 'right' }); y += 16; };
  right('Subtotal', Number(invoice.subtotal).toFixed(2));
  right('Tax', Number(invoice.tax_total).toFixed(2));
  right('Total', Number(invoice.total).toFixed(2));

  // Footer
  doc.moveDown(2);
  doc.fontSize(10).fillColor('#666').text('Thank you for your business!', 50, 760, { align: 'center' });
  doc.end();

  return new Promise((resolve, reject) => {
    stream.on('finish', () => resolve(outPath));
    stream.on('error', reject);
  });
}
