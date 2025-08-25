import { createContext, useContext, useEffect, useState } from 'react'
import { api } from '../lib/api'
import { saveTokens, clearTokens, getAccess } from '../lib/auth'

type User = { id:number; username:string; role:string; first_name?:string; last_name?:string }
type AuthCtx = {
  user: User | null
  login:(email:string, password:string)=>Promise<void>
  logout:()=>void
  ready:boolean
}
const Ctx = createContext<AuthCtx>({} as any)

export function AuthProvider({ children }:{ children: React.ReactNode }) {
  const [user,setUser] = useState<User|null>(null)
  const [ready,setReady] = useState(false)

  useEffect(() => {
    (async () => {
      try {
        if (!getAccess()) return setReady(true)
        const me = await api.get('/auth/me')
        setUser(me.data)
      } catch {}
      setReady(true)
    })()
  }, [])

  async function login(email:string, password:string) {
    const res = await api.post('/auth/login', { email, password })
    saveTokens({ accessToken: res.data.accessToken, refreshToken: res.data.refreshToken })
    const me = await api.get('/auth/me')
    setUser(me.data)
  }
  function logout() { clearTokens(); setUser(null); window.location.href = '/login' }

  return <Ctx.Provider value={{ user, login, logout, ready }}>{children}</Ctx.Provider>
}

export function useAuth(){ return useContext(Ctx) }
