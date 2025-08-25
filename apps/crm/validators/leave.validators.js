import Joi from 'joi';

export const leaveTypeCreateSchema = Joi.object({
  name: Joi.string().max(120).required(),
  code: Joi.string().uppercase().alphanum().max(30).required(),
  unit: Joi.string().valid('day','hour').default('day'),
  default_annual_quota: Joi.number().min(0).max(9999).default(0),
  carry_forward_max: Joi.number().min(0).max(9999).default(0),
  negative_balance_allowed: Joi.number().valid(0,1).default(0),
  requires_document: Joi.number().valid(0,1).default(0),
  gender_restriction: Joi.string().valid('male','female','other').allow(null),
  min_notice_days: Joi.number().integer().min(0).max(365).default(0),
  max_consecutive_days: Joi.number().min(0).max(9999).allow(null),
  weekend_inclusive: Joi.number().valid(0,1).default(0),
  half_day_allowed: Joi.number().valid(0,1).default(1),
  is_active: Joi.number().valid(0,1).default(1)
});

export const leaveTypeUpdateSchema = leaveTypeCreateSchema.min(1);

export const entitlementUpsertSchema = Joi.object({
  user_id: Joi.number().integer().required(),
  type_id: Joi.number().integer().required(),
  year: Joi.number().integer().min(2000).max(2100).required(),
  quota: Joi.number().min(0).max(9999).required(),
  carried_forward: Joi.number().min(0).max(9999).default(0),
  notes: Joi.string().allow('', null)
});

export const holidayCreateSchema = Joi.object({
  name: Joi.string().max(150).required(),
  date: Joi.date().required(),
  region: Joi.string().max(80).allow('', null),
  is_optional: Joi.number().valid(0,1).default(0)
});

export const holidayUpdateSchema = holidayCreateSchema.min(1);

export const leaveRequestSchema = Joi.object({
  type_id: Joi.number().integer().required(),
  start_date: Joi.date().required(),
  end_date: Joi.date().required(),
  half_day: Joi.number().valid(0,1).default(0),
  reason: Joi.string().allow('', null),
  // if document uploaded via multipart, ignore doc_file here
});

export const approveSchema = Joi.object({
  action: Joi.string().valid('approve','reject').required(),
  comment: Joi.string().allow('', null)
});