export function notFound(req, res, next) {
  res.status(404).json({ message: 'Not Found', path: req.originalUrl });
}
export function errorHandler(err, req, res, next) {
  console.error(err);
  const status = err.status || err.statusCode || 500;
  const body = { message: err.message || 'Internal Server Error' };
  if (process.env.NODE_ENV !== 'production' && err.stack) body.stack = err.stack;
  res.status(status).json(body);
}
