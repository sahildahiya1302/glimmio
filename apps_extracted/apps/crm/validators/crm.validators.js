import Joi from 'joi';

export const accountSchema = Joi.object({
  name: Joi.string().max(200).required(),
  domain: Joi.string().allow('', null),
  phone: Joi.string().allow('', null),
  website: Joi.string().uri().allow('', null),
  address_line1: Joi.string().allow('', null),
  address_line2: Joi.string().allow('', null),
  city: Joi.string().allow('', null),
  state: Joi.string().allow('', null),
  postal_code: Joi.string().allow('', null),
  country: Joi.string().allow('', null),
  owner_id: Joi.number().integer().allow(null)
});

export const contactSchema = Joi.object({
  account_id: Joi.number().integer().allow(null),
  first_name: Joi.string().allow('', null),
  last_name: Joi.string().allow('', null),
  email: Joi.string().email().required(),
  phone: Joi.string().allow('', null),
  title: Joi.string().allow('', null),
  owner_id: Joi.number().integer().allow(null)
});

export const pipelineSchema = Joi.object({
  name: Joi.string().max(120).required()
});
export const stageSchema = Joi.object({
  name: Joi.string().max(120).required(),
  position: Joi.number().integer().min(1).default(1),
  is_won: Joi.number().valid(0,1).default(0),
  is_lost: Joi.number().valid(0,1).default(0)
});

export const dealSchema = Joi.object({
  title: Joi.string().max(200).required(),
  account_id: Joi.number().integer().allow(null),
  contact_id: Joi.number().integer().allow(null),
  amount: Joi.number().min(0).default(0),
  currency: Joi.string().max(10).default('INR'),
  pipeline_id: Joi.number().integer().required(),
  stage_id: Joi.number().integer().required(),
  owner_id: Joi.number().integer().allow(null),
  close_date: Joi.date().allow(null)
});

export const moveDealSchema = Joi.object({
  stage_id: Joi.number().integer().required()
});

export const activitySchema = Joi.object({
  type: Joi.string().valid('call','email','meeting','task').required(),
  subject: Joi.string().max(200).required(),
  description: Joi.string().allow('', null),
  due_date: Joi.date().allow(null),
  status: Joi.string().valid('open','done').default('open'),
  owner_id: Joi.number().integer().required(),
  account_id: Joi.number().integer().allow(null),
  contact_id: Joi.number().integer().allow(null),
  deal_id: Joi.number().integer().allow(null)
});

export const productSchema = Joi.object({
  sku: Joi.string().allow('', null),
  name: Joi.string().max(200).required(),
  description: Joi.string().allow('', null),
  price: Joi.number().min(0).default(0),
  currency: Joi.string().max(10).default('INR'),
  is_active: Joi.number().valid(0,1).default(1)
});

export const quoteCreateSchema = Joi.object({
  account_id: Joi.number().integer().required(),
  contact_id: Joi.number().integer().allow(null),
  deal_id: Joi.number().integer().allow(null),
  currency: Joi.string().max(10).default('INR')
});
export const quoteItemSchema = Joi.object({
  product_id: Joi.number().integer().allow(null),
  name: Joi.string().max(200).required(),
  price: Joi.number().min(0).required(),
  qty: Joi.number().min(0).default(1),
  discount: Joi.number().min(0).default(0),
  tax_rate: Joi.number().min(0).max(100).default(0)
});

export const invoiceCreateSchema = Joi.object({
  account_id: Joi.number().integer().required(),
  contact_id: Joi.number().integer().allow(null),
  quote_id: Joi.number().integer().allow(null),
  currency: Joi.string().max(10).default('INR'),
  due_date: Joi.date().allow(null)
});
export const invoiceItemSchema = quoteItemSchema;


// ...existing exports...

export const leadSchema = Joi.object({
  first_name: Joi.string().allow('', null),
  last_name: Joi.string().allow('', null),
  email: Joi.string().email().allow('', null),
  phone: Joi.string().allow('', null),
  company: Joi.string().allow('', null),
  title: Joi.string().allow('', null),
  source: Joi.string().allow('', null),
  status: Joi.string().valid('new','working','qualified','unqualified','converted').default('new'),
  owner_id: Joi.number().integer().allow(null)
});

export const leadConvertSchema = Joi.object({
  create_deal: Joi.boolean().default(true),
  pipeline_id: Joi.number().integer().allow(null),
  stage_id: Joi.number().integer().allow(null),
  amount: Joi.number().min(0).default(0),
  title: Joi.string().allow('', null)
});
