export type Tokens = { accessToken: string; refreshToken?: string }
const ACCESS_KEY = 'gl_access'; const REFRESH_KEY = 'gl_refresh';

export function saveTokens(t: Tokens) {
  localStorage.setItem(ACCESS_KEY, t.accessToken)
  if (t.refreshToken) localStorage.setItem(REFRESH_KEY, t.refreshToken)
}
export function getAccess() { return localStorage.getItem(ACCESS_KEY) || '' }
export function getRefresh() { return localStorage.getItem(REFRESH_KEY) || '' }
export function clearTokens() { localStorage.removeItem(ACCESS_KEY); localStorage.removeItem(REFRESH_KEY) }
