import { pool } from '../config/database.js';
import { nanoid } from 'nanoid';
import { buildCertificatePDF } from '../utils/pdf.certificate.js';

export async function getCertificate(course_id, user_id) {
  const [rows] = await pool.execute(
    `SELECT * FROM lms_certificates WHERE course_id=? AND user_id=?`,
    [course_id, user_id]
  );
  return rows[0] || null;
}

export async function maybeIssueCertificate(course_id, user_id) {
  // only if enrollment is completed
  const [rows] = await pool.execute(
    `SELECT status FROM lms_enrollments WHERE course_id=? AND user_id=?`,
    [course_id, user_id]
  );
  if (!rows.length || rows[0].status !== 'completed') return null;

  const existing = await getCertificate(course_id, user_id);
  if (existing) return existing;

  const code = nanoid(12).toUpperCase();
  const [res] = await pool.execute(
    `INSERT INTO lms_certificates (course_id, user_id, cert_code) VALUES (?, ?, ?)`,
    [course_id, user_id, code]
  );
  const [created] = await pool.execute(`SELECT * FROM lms_certificates WHERE id=?`, [res.insertId]);
  return created[0];
}

export async function generateCertificatePdf(course_id, user_id) {
  // Ensure there is a certificate row (issue if eligible).
  let cert = await getCertificate(course_id, user_id);
  if (!cert) {
    cert = await maybeIssueCertificate(course_id, user_id);
    if (!cert) return null; // not eligible
  }

  // Fetch course + user names
  const [[course]] = await pool.execute(`SELECT title FROM lms_courses WHERE id=?`, [course_id]);
  const [[user]] = await pool.execute(
    `SELECT COALESCE(CONCAT(first_name,' ',last_name), username) AS name FROM users WHERE id=?`, [user_id]
  );
  const issuedAt = (cert.issued_at instanceof Date)
    ? cert.issued_at.toISOString().slice(0,10)
    : String(cert.issued_at).slice(0,10);

  const pdfPath = await buildCertificatePDF({
    courseTitle: course?.title || `Course #${course_id}`,
    userName: user?.name || `User #${user_id}`,
    certCode: cert.cert_code,
    issuedAt,
    brand: { name: 'Glimmio' }
  });

  return { path: pdfPath, cert };
}
