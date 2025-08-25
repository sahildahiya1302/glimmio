import axios, { AxiosError, type AxiosRequestConfig } from 'axios'

const BASE = import.meta.env.VITE_API_BASE ?? '/api'

// ---- token storage helpers ----
const ACCESS_KEY = 'access_token'
const REFRESH_KEY = 'refresh_token'

export function getAccessToken() {
  return localStorage.getItem(ACCESS_KEY) || ''
}
export function getRefreshToken() {
  return localStorage.getItem(REFRESH_KEY) || ''
}
export function setTokens(tokens: { accessToken?: string; refreshToken?: string }) {
  if (tokens.accessToken) localStorage.setItem(ACCESS_KEY, tokens.accessToken)
  if (tokens.refreshToken) localStorage.setItem(REFRESH_KEY, tokens.refreshToken)
}
export function clearTokens() {
  localStorage.removeItem(ACCESS_KEY)
  localStorage.removeItem(REFRESH_KEY)
}

// ---- axios instance ----
export const api = axios.create({
  baseURL: BASE,
  withCredentials: false,
  timeout: 15_000,
})

// attach Authorization
api.interceptors.request.use((config) => {
  const token = getAccessToken()
  if (token) {
    config.headers = config.headers ?? {}
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// refresh-once queue
let isRefreshing = false
let queue: Array<() => void> = []

function subscribe(cb: () => void) {
  queue.push(cb)
}
function flushQueue() {
  queue.forEach((cb) => cb())
  queue = []
}

// handle 401 -> try refresh -> retry once
api.interceptors.response.use(
  (r) => r,
  async (error: AxiosError) => {
    const original = error.config as (AxiosRequestConfig & { _retry?: boolean }) | undefined
    const status = error.response?.status

    if (status === 401 && original && !original._retry) {
      original._retry = true

      if (!isRefreshing) {
        isRefreshing = true
        try {
          const rt = getRefreshToken()
          if (!rt) throw new Error('No refresh token')
          const res = await axios.post(`${BASE}/auth/refresh`, { refreshToken: rt })
          const { accessToken, refreshToken } = (res.data || {}) as { accessToken: string; refreshToken?: string }
          setTokens({ accessToken, refreshToken })
          isRefreshing = false
          flushQueue()
        } catch (e) {
          isRefreshing = false
          flushQueue()
          clearTokens()
          // bubble up to let UI redirect to /login
          throw e
        }
      }

      // wait until refresh finishes, then retry
      await new Promise<void>((resolve) => subscribe(resolve))
      const token = getAccessToken()
      original.headers = original.headers ?? {}
      if (token) original.headers.Authorization = `Bearer ${token}`
      return api(original)
    }

    throw error
  }
)
