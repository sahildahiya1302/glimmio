import { NavLink, useNavigate, Outlet } from 'react-router-dom'
import { LayoutDashboard, Timer, CalendarDays, GraduationCap, Users, FileText, LineChart, Search, LogOut } from 'lucide-react'
import { clearTokens } from '../lib/api'

const linkClass = ({ isActive }: { isActive: boolean }) =>
  `flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 ${isActive ? 'bg-gray-100 font-medium' : ''}`

export default function Layout() {
  const navigate = useNavigate()

  function logout() {
    clearTokens()
    navigate('/login', { replace: true })
  }

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900 grid md:grid-cols-[240px_1fr]">
      {/* Sidebar */}
      <aside className="bg-white border-r p-4 md:sticky md:top-0 md:h-screen">
        <div className="mb-6">
          <div className="text-xl font-bold">Glimmio CRM</div>
          <div className="text-xs text-gray-500">Enterprise Suite</div>
        </div>
        <nav className="space-y-1 text-sm">
          <NavLink to="/" end className={linkClass}><LayoutDashboard size={16}/> Dashboard</NavLink>
          <NavLink to="/attendance" className={linkClass}><Timer size={16}/> Attendance</NavLink>
          <NavLink to="/leaves" className={linkClass}><CalendarDays size={16}/> Leaves</NavLink>
          <NavLink to="/learning" className={linkClass}><GraduationCap size={16}/> Learning</NavLink>

          <div className="mt-4 text-xs uppercase tracking-wide text-gray-500 px-3">CRM</div>
          <NavLink to="/crm/leads" className={linkClass}><Users size={16}/> Leads</NavLink>
          <NavLink to="/crm/quotes" className={linkClass}><FileText size={16}/> Quotes</NavLink>
          <NavLink to="/crm/invoices" className={linkClass}><FileText size={16}/> Invoices</NavLink>
          <NavLink to="/crm/reports" className={linkClass}><LineChart size={16}/> Reports</NavLink>
          <NavLink to="/crm/search" className={linkClass}><Search size={16}/> Global Search</NavLink>
        </nav>

        <button onClick={logout} className="btn mt-6 w-full flex justify-center items-center">
          <LogOut size={16} /> Logout
        </button>
      </aside>

      {/* Main */}
      <main className="p-6">
        <Outlet />
      </main>
    </div>
  )
}
