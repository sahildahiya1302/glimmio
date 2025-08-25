import path from 'path';
import fs from 'fs';

const ROOT = process.cwd();
const ensureDir = (p) => (fs.mkdirSync(p, { recursive: true }), p);

/* HR uploads */
const HR_ROOT = path.join(ROOT, 'storage', 'uploads', 'hr');
export const ensureUploadDirs = () => ensureDir(HR_ROOT);
export const hrUploadPath = (...p) => (ensureUploadDirs(), path.join(HR_ROOT, ...p));

/* LMS uploads */
const LMS_ROOT = path.join(ROOT, 'storage', 'uploads', 'lms');
export const ensureLmsDirs = () => ensureDir(LMS_ROOT);
export const lmsUploadPath = (...p) => (ensureLmsDirs(), path.join(LMS_ROOT, ...p));

/* CRM uploads */
const CRM_ROOT = path.join(ROOT, 'storage', 'uploads', 'crm');
export const ensureCrmDirs = () => ensureDir(CRM_ROOT);
export const crmUploadPath = (...p) => (ensureCrmDirs(), path.join(CRM_ROOT, ...p));

/* Shared */
export const sanitizeFilename = (name) => String(name || '').replace(/[^a-zA-Z0-9._-]/g, '_');
