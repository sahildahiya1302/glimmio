import { Routes, Route, Navigate, Outlet, useLocation } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { api, getAccessToken, clearTokens } from './lib/api'
import Layout from './components/Layout'
import Dashboard from './pages/Dashboard'
import Login from './pages/Login'

// Checks token presence + verifies with /auth/me.
// If invalid -> redirect to /login
function Guard() {
  const token = getAccessToken()
  const loc = useLocation()

  const { isLoading, isError } = useQuery({
    enabled: !!token,
    queryKey: ['auth', 'me'],
    queryFn: async () => (await api.get('/auth/me')).data,
    retry: false,
  })

  if (!token) {
    return <Navigate to="/login" replace state={{ from: loc }} />
  }
  if (isLoading) {
    return (
      <div className="min-h-screen grid place-items-center">
        <div className="card">Checking your session…</div>
      </div>
    )
  }
  if (isError) {
    clearTokens()
    return <Navigate to="/login" replace state={{ from: loc }} />
  }
  return <Outlet />
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />

      {/* Protected area */}
      <Route element={<Guard />}>
        <Route element={<Layout />}>
          <Route index element={<Dashboard />} />
          {/* Put the rest of your routes here when ready, e.g.:
          <Route path="attendance" element={<Attendance />} />
          <Route path="leaves" element={<Leaves />} />
          <Route path="learning" element={<Courses />} />
          <Route path="crm/kanban" element={<Kanban />} />
          <Route path="crm/leads" element={<Leads />} />
          <Route path="crm/quotes" element={<Quotes />} />
          <Route path="crm/invoices" element={<Invoices />} />
          <Route path="crm/reports" element={<Reports />} />
          <Route path="crm/search" element={<GlobalSearch />} />
          */}
        </Route>
      </Route>

      {/* Fallback */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}
