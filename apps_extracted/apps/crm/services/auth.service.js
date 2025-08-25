import { pool } from '../config/database.js';
import { hashPassword, comparePassword } from '../utils/crypto.js';
import { signAccessToken, signRefreshToken } from '../utils/tokens.js';
import * as OTPAuth from 'otpauth';

export async function register({ username, email, password, role = 'employee' }) {
  const hashed = await hashPassword(password);
  const [res] = await pool.execute(
    `INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)`,
    [username, email, hashed, role]
  );
  const [rows] = await pool.execute(
    `SELECT id, username, email, role FROM users WHERE id=?`,
    [res.insertId]
  );
  return rows[0];
}

export async function login({ usernameOrEmail, password }) {
  const [rows] = await pool.execute(
    `SELECT id, username, email, password, role, mfa_secret
       FROM users
      WHERE username=? OR email=?
      LIMIT 1`,
    [usernameOrEmail, usernameOrEmail]
  );
  if (!rows.length) throw new Error('INVALID_CREDENTIALS');

  const user = rows[0];
  const ok = await comparePassword(password, user.password);
  if (!ok) throw new Error('INVALID_CREDENTIALS');

  const accessToken = signAccessToken({ id: user.id, role: user.role });
  const refreshToken = signRefreshToken({ id: user.id, role: user.role });

  return {
    user: {
      id: user.id,
      username: user.username,
      email: user.email,
      role: user.role,
      mfa_enabled: !!user.mfa_secret
    },
    accessToken,
    refreshToken
  };
}

export async function refresh({ tokenPayload }) {
  const accessToken = signAccessToken({ id: tokenPayload.id, role: tokenPayload.role });
  const refreshToken = signRefreshToken({ id: tokenPayload.id, role: tokenPayload.role });
  return { accessToken, refreshToken };
}

export async function setupMfa(userId) {
  const issuer = 'GlimmioCRM';
  const label = `user:${userId}`;

  // Generate a new random secret and store BASE32 text in DB
  const secret = new OTPAuth.Secret(); // random 20 bytes by default
  const totp = new OTPAuth.TOTP({
    issuer,
    label,
    algorithm: 'SHA1',
    digits: 6,
    period: 30,
    secret
  });
  const uri = totp.toString();
  await pool.execute(`UPDATE users SET mfa_secret=? WHERE id=?`, [secret.base32, userId]);
  return { uri };
}

export async function verifyMfa(userId, code) {
  const [rows] = await pool.execute(`SELECT mfa_secret FROM users WHERE id=?`, [userId]);
  if (!rows.length || !rows[0].mfa_secret) return false;

  const secret = OTPAuth.Secret.fromBase32(rows[0].mfa_secret);
  const totp = new OTPAuth.TOTP({
    algorithm: 'SHA1',
    digits: 6,
    period: 30,
    secret
  });
  const delta = totp.validate({ token: String(code), window: 1 });
  return delta !== null;
}
