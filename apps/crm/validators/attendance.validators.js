import Joi from 'joi';

export const createShiftSchema = Joi.object({
  name: Joi.string().max(100).required(),
  working_days: Joi.string().pattern(/^[0-6](,[0-6])*$/).default('1,2,3,4,5'),
  start_time: Joi.string().pattern(/^\d{2}:\d{2}(:\d{2})?$/).required(),
  end_time: Joi.string().pattern(/^\d{2}:\d{2}(:\d{2})?$/).required(),
  grace_minutes: Joi.number().integer().min(0).max(180).default(10)
});

export const updateShiftSchema = createShiftSchema.min(1);

export const createPolicySchema = Joi.object({
  name: Joi.string().max(120).required(),
  ip_allowlist: Joi.string().allow('', null),
  geo_lat: Joi.number().allow(null),
  geo_lng: Joi.number().allow(null),
  geo_radius_m: Joi.number().integer().min(10).max(20000).allow(null),
  device_bind: Joi.number().valid(0,1).default(0),
  require_photo: Joi.number().valid(0,1).default(0)
});

export const updatePolicySchema = createPolicySchema.min(1);

export const assignShiftSchema = Joi.object({
  user_id: Joi.number().integer().required(),
  shift_id: Joi.number().integer().required(),
  policy_id: Joi.number().integer().allow(null),
  effective_from: Joi.date().required(),
  effective_to: Joi.date().allow(null)
});

export const clockSchema = Joi.object({
  lat: Joi.number().optional(),
  lng: Joi.number().optional(),
  device_id: Joi.string().max(120).allow('', null),
  source: Joi.string().max(40).default('web')
});
