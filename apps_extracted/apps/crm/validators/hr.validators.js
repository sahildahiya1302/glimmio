import Joi from 'joi';

export const createDepartmentSchema = Joi.object({
  name: Joi.string().max(100).required(),
  code: Joi.string().max(20).optional().allow(null, ''),
  description: Joi.string().allow('', null)
});

export const createDesignationSchema = Joi.object({
  name: Joi.string().max(100).required(),
  level: Joi.number().integer().min(0).default(0)
});

export const createEmployeeSchema = Joi.object({
  username: Joi.string().alphanum().min(3).max(50).required(),
  email: Joi.string().email().required(),
  password: Joi.string().min(6).required(),
  role: Joi.string().valid('admin','manager','employee').default('employee'),
  first_name: Joi.string().allow('', null),
  last_name: Joi.string().allow('', null),
  department_id: Joi.number().integer().allow(null),
  designation_id: Joi.number().integer().allow(null),
  manager_id: Joi.number().integer().allow(null),
  doj: Joi.date().allow(null),
  employee_code: Joi.string().max(30).allow(null, '')
});

export const updateEmployeeSchema = Joi.object({
  email: Joi.string().email(),
  role: Joi.string().valid('admin','manager','employee'),
  first_name: Joi.string().allow('', null),
  last_name: Joi.string().allow('', null),
  department_id: Joi.number().integer().allow(null),
  designation_id: Joi.number().integer().allow(null),
  manager_id: Joi.number().integer().allow(null),
  doj: Joi.date().allow(null),
  is_active: Joi.number().valid(0,1),
  employee_code: Joi.string().max(30).allow(null, '')
}).min(1);

export const upsertProfileSchema = Joi.object({
  phone: Joi.string().allow('', null),
  alt_phone: Joi.string().allow('', null),
  dob: Joi.date().allow(null),
  gender: Joi.string().valid('male','female','other').allow(null),
  blood_group: Joi.string().max(5).allow(null, ''),
  address_line1: Joi.string().allow('', null),
  address_line2: Joi.string().allow('', null),
  city: Joi.string().allow('', null),
  state: Joi.string().allow('', null),
  postal_code: Joi.string().allow('', null),
  country: Joi.string().allow('', null),
  emergency_contact_name: Joi.string().allow('', null),
  emergency_contact_phone: Joi.string().allow('', null),
  PAN: Joi.string().allow('', null),
  AADHAAR: Joi.string().allow('', null),
  bank_name: Joi.string().allow('', null),
  bank_account: Joi.string().allow('', null),
  ifsc: Joi.string().allow('', null)
});
