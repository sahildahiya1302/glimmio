import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { invoiceCreateSchema, invoiceItemSchema } from '../validators/crm.validators.js';
import * as Invoices from '../services/crm.invoices.service.js';

const router = express.Router();

router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await Invoices.getInvoice(Number(req.params.id));
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});

router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = invoiceCreateSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Invoices.createInvoice({ ...value, owner_id: req.user.id }));
  } catch (e) { next(e); }
});

router.post('/:id/items', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = invoiceItemSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Invoices.addInvoiceItem(Number(req.params.id), value));
  } catch (e) { next(e); }
});

router.post('/:id/status', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { status } = req.body;
    if (!['draft','sent','paid','overdue'].includes(status)) return res.status(422).json({ message: 'invalid status' });
    res.json(await Invoices.setInvoiceStatus(Number(req.params.id), status));
  } catch (e) { next(e); }
});

export default router;
