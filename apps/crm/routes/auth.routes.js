import express from 'express';
import QRCode from 'qrcode';
import { register, login, refresh, setupMfa, verifyMfa } from '../services/auth.service.js';
import { verifyRefresh } from '../utils/tokens.js';

export default function (csrfProtection) {
  const router = express.Router();

  // Views
  router.get('/login', csrfProtection, (req, res) => {
    res.render('auth/login', { csrfToken: req.csrfToken() });
  });
  router.get('/register', csrfProtection, (req, res) => {
    res.render('auth/register', { csrfToken: req.csrfToken() });
  });

  // Web login (session) + optional MFA
  router.post('/login', csrfProtection, async (req, res, next) => {
    try {
      const { username, password, mfa_code } = req.body;
      const { user, refreshToken } = await login({ usernameOrEmail: username, password });

      if (user.mfa_enabled) {
        if (!mfa_code) {
          return res.status(401).render('auth/login', { csrfToken: req.csrfToken(), error: 'Enter OTP' });
        }
        const ok = await verifyMfa(user.id, mfa_code);
        if (!ok) {
          return res.status(401).render('auth/login', { csrfToken: req.csrfToken(), error: 'Invalid OTP' });
        }
      }

      req.session.user = user;
      res.cookie('rt', refreshToken, { httpOnly: true, sameSite: 'lax', maxAge: 1000 * 60 * 60 * 24 * 30 });
      return res.redirect('/');
    } catch (e) {
      next(e);
    }
  });

  // Web register
  router.post('/register', csrfProtection, async (req, res, next) => {
    try {
      const { username, email, password } = req.body;
      await register({ username, email, password });
      res.redirect('/auth/login');
    } catch (e) {
      next(e);
    }
  });

  // API login (JWT)
  router.post('/api/login', async (req, res, next) => {
    try {
      const { usernameOrEmail, password } = req.body;
      const result = await login({ usernameOrEmail, password });
      res.json(result);
    } catch (e) {
      next(e);
    }
  });

  // API refresh
  router.post('/api/refresh', async (req, res, next) => {
    try {
      const { refreshToken } = req.body;
      const payload = verifyRefresh(refreshToken);
      const result = await refresh({ tokenPayload: payload });
      res.json(result);
    } catch (e) {
      next(e);
    }
  });

  // MFA setup (render QR)
  router.get('/mfa/setup', async (req, res, next) => {
    try {
      const user = req.session?.user;
      if (!user) return res.redirect('/auth/login');
      const { uri } = await setupMfa(user.id);
      const qr = await QRCode.toDataURL(uri);
      res.render('auth/mfa_setup', { qr });
    } catch (e) {
      next(e);
    }
  });

  router.get('/logout', (req, res) => {
    req.session.destroy(() => res.redirect('/auth/login'));
  });

  return router;
}
