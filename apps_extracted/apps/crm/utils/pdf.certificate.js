// utils/pdf.certificate.js
import fs from 'fs';
import path from 'path';
import PDFDocument from 'pdfkit';
import { lmsUploadPath, sanitizeFilename } from './storage.js';

export function ensureCertDir() {
  const dir = lmsUploadPath('certificates');
  fs.mkdirSync(dir, { recursive: true });
  return dir;
}

export function buildCertificatePDF({ courseTitle, userName, certCode, issuedAt, brand = {} }) {
  const dir = ensureCertDir();
  const fileName = sanitizeFilename(`${certCode}.pdf`);
  const outPath = path.join(dir, fileName);

  const doc = new PDFDocument({ size: 'A4', margins: { top: 50, left: 50, right: 50, bottom: 50 } });
  const stream = fs.createWriteStream(outPath);
  doc.pipe(stream);

  // Border
  doc.rect(20, 20, doc.page.width - 40, doc.page.height - 40).lineWidth(3).stroke();

  // Branding
  const brandName = brand.name || 'Glimmio';
  doc.font('Times-Bold').fontSize(28).text(brandName, { align: 'center' });
  doc.moveDown(0.5);
  doc.font('Times-Roman').fontSize(16).fillColor('#444')
     .text('Certificate of Completion', { align: 'center' });
  doc.moveDown(1.5);

  // Recipient
  doc.fillColor('#000').font('Times-Italic').fontSize(14)
     .text('This certifies that', { align: 'center' });
  doc.moveDown(0.3);
  doc.font('Times-Bold').fontSize(26).text(userName, { align: 'center' });
  doc.moveDown(0.6);
  doc.font('Times-Roman').fontSize(14)
     .text('has successfully completed the course', { align: 'center' });
  doc.moveDown(0.3);
  doc.font('Times-Bold').fontSize(20).text(`“${courseTitle}”`, { align: 'center' });

  // Details
  doc.moveDown(1.2);
  doc.font('Times-Roman').fontSize(12).fillColor('#333')
     .text(`Certificate Code: ${certCode}`, { align: 'center' });
  doc.text(`Issued: ${issuedAt}`, { align: 'center' });

  // Signature lines
  const y = doc.y + 50;
  const pageW = doc.page.width;
  const leftX = pageW * 0.15;
  const rightX = pageW * 0.55;

  doc.moveTo(leftX, y).lineTo(leftX + 200, y).stroke();
  doc.text('Training Lead', leftX, y + 5, { width: 200, align: 'center' });

  doc.moveTo(rightX, y).lineTo(rightX + 200, y).stroke();
  doc.text('HR / Admin', rightX, y + 5, { width: 200, align: 'center' });

  doc.end();

  return new Promise((resolve, reject) => {
    stream.on('finish', () => resolve(outPath));
    stream.on('error', reject);
  });
}
