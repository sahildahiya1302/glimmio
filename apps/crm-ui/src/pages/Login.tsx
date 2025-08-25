import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { api, setTokens } from '../lib/api'
import { useLocation, useNavigate } from 'react-router-dom'

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
})

type FormData = z.infer<typeof schema>

export default function Login() {
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<FormData>({ resolver: zodResolver(schema) })
  const navigate = useNavigate()
  const loc = useLocation()
  const from = (loc.state as any)?.from?.pathname || '/'

  async function onSubmit(values: FormData) {
    try {
      const res = await api.post('/auth/login', values)
      const { accessToken, refreshToken } = res.data || {}
      setTokens({ accessToken, refreshToken })
      navigate(from, { replace: true })
    } catch (e: any) {
      alert(e?.response?.data?.message || 'Login failed')
    }
  }

  return (
    <div className="min-h-screen grid place-items-center p-4">
      <form onSubmit={handleSubmit(onSubmit)} className="card w-full max-w-sm">
        <h1 className="text-xl font-semibold mb-4">Sign in</h1>

        <label className="block text-sm mb-1">Email</label>
        <input
          type="email"
          {...register('email')}
          className="w-full rounded-lg border px-3 py-2 mb-2"
          placeholder="you@company.com"
        />
        {errors.email && <div className="text-xs text-red-600 mb-2">{errors.email.message}</div>}

        <label className="block text-sm mb-1 mt-2">Password</label>
        <input
          type="password"
          {...register('password')}
          className="w-full rounded-lg border px-3 py-2 mb-2"
          placeholder="••••••••"
        />
        {errors.password && <div className="text-xs text-red-600 mb-3">{errors.password.message}</div>}

        <button disabled={isSubmitting} className="btn w-full justify-center">
          {isSubmitting ? 'Signing in…' : 'Sign in'}
        </button>
      </form>
    </div>
  )
}
