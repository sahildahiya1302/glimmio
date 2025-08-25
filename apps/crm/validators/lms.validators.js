import Joi from 'joi';

export const createCourseSchema = Joi.object({
  title: Joi.string().max(200).required(),
  description: Joi.string().allow('', null),
  category: Joi.string().allow('', null),
  level: Joi.string().valid('beginner','intermediate','advanced').default('beginner'),
  language: Joi.string().allow('', null),
  duration_minutes: Joi.number().integer().min(0).default(0),
  is_published: Joi.number().valid(0,1).default(0)
});
export const updateCourseSchema = createCourseSchema.min(1);

export const createModuleSchema = Joi.object({
  title: Joi.string().max(200).required(),
  position: Joi.number().integer().min(1).default(1)
});
export const updateModuleSchema = createModuleSchema.min(1);

export const createLessonSchema = Joi.object({
  title: Joi.string().max(200).required(),
  type: Joi.string().valid('video','article','file','quiz').required(),
  content: Joi.string().allow('', null),
  video_url: Joi.string().uri().allow('', null),
  quiz_id: Joi.number().integer().allow(null),
  duration_minutes: Joi.number().integer().min(0).default(0),
  position: Joi.number().integer().min(1).default(1),
  is_preview: Joi.number().valid(0,1).default(0)
});
export const updateLessonSchema = createLessonSchema.min(1);

export const enrollSchema = Joi.object({
  course_id: Joi.number().integer().required()
});

export const quizCreateSchema = Joi.object({
  title: Joi.string().max(200).required(),
  passing_score: Joi.number().integer().min(0).max(100).default(60),
  attempts_allowed: Joi.number().integer().min(1).max(20).default(3)
});
export const questionCreateSchema = Joi.object({
  text: Joi.string().required(),
  type: Joi.string().valid('single','multiple','truefalse').default('single'),
  points: Joi.number().min(0).default(1),
  position: Joi.number().integer().min(1).default(1),
  options: Joi.array().items(Joi.object({
    text: Joi.string().required(),
    is_correct: Joi.number().valid(0,1).default(0)
  })).min(1).required()
});

export const attemptStartSchema = Joi.object({
  quiz_id: Joi.number().integer().required()
});

export const attemptSubmitSchema = Joi.object({
  answers: Joi.array().items(Joi.object({
    question_id: Joi.number().integer().required(),
    selected_option_ids: Joi.array().items(Joi.number().integer()).required()
  })).required()
});
