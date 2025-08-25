import { verifyAccess } from '../utils/tokens.js';

export function requireAuth(req, res, next) {
  try {
    const hdr = req.headers.authorization || '';
    const token = hdr.startsWith('Bearer ') ? hdr.slice(7) : null;

    if (token) {
      const decoded = verifyAccess(token);
      req.user = decoded;
      return next();
    }
    if (req.session && req.session.user) {
      req.user = req.session.user;
      return next();
    }
    return res.status(401).json({ message: 'Unauthorized' });
  } catch {
    return res.status(401).json({ message: 'Unauthorized' });
  }
}
