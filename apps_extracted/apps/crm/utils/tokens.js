// /utils/tokens.js
import jwt from 'jsonwebtoken';

export function signAccessToken(payload, expiresIn = '30m') {
  return jwt.sign(payload, process.env.JWT_ACCESS_SECRET, { expiresIn });
}

export function signRefreshToken(payload, expiresIn = '30d') {
  return jwt.sign(payload, process.env.JWT_REFRESH_SECRET, { expiresIn });
}

export function verifyAccess(token) {
  return jwt.verify(token, process.env.JWT_ACCESS_SECRET);
}

export function verifyRefresh(token) {
  return jwt.verify(token, process.env.JWT_REFRESH_SECRET);
}
