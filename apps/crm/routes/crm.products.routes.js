import express from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/rbac.js';
import { productSchema } from '../validators/crm.validators.js';
import * as Products from '../services/crm.products.service.js';

const router = express.Router();

router.get('/', requireAuth, async (req, res, next) => {
  try {
    const active = req.query.active !== undefined ? Number(req.query.active) : undefined;
    res.json(await Products.listProducts({ q: req.query.q, active }));
  } catch (e) { next(e); }
});
router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const row = await Products.getProduct(Number(req.params.id));
    if (!row) return res.status(404).json({ message: 'Not found' });
    res.json(row);
  } catch (e) { next(e); }
});
router.post('/', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try {
    const { error, value } = productSchema.validate(req.body);
    if (error) return res.status(422).json({ message: error.message });
    res.status(201).json(await Products.createProduct(value));
  } catch (e) { next(e); }
});
router.put('/:id', requireAuth, requireRole('admin','manager'), async (req, res, next) => {
  try { res.json(await Products.updateProduct(Number(req.params.id), req.body)); } catch (e) { next(e); }
});
router.delete('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try { res.json(await Products.deleteProduct(Number(req.params.id))); } catch (e) { next(e); }
});

export default router;
