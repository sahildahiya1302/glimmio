import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { contactSchema } from '../validators/crm.validators.js';
import * as Contacts from '../services/crm.contacts.service.js';

const router = express.Router();

router.get('/', requireAuth, async (req, res, next) => {
  try { res.json(await Contacts.listContacts({ q: req.query.q, account_id: req.query.account_id ? Number(req.query.account_id) : undefined })); } catch (e) { next(e); }
});
router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await Contacts.getContact(Number(req.params.id));
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});
router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = contactSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Contacts.createContact(value));
  } catch (e) { next(e); }
});
router.put('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await Contacts.updateContact(Number(req.params.id), req.body)); } catch (e) { next(e); }
});
router.delete('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Contacts.deleteContact(Number(req.params.id))); } catch (e) { next(e); }
});

export default router;
