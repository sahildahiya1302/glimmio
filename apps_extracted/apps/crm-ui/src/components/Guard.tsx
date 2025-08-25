import React from 'react'
import { Navigate } from 'react-router-dom'
import { useAuth } from '../providers/AuthProvider'

export default function Guard({ children }:{ children: React.ReactNode }) {
  const { user, ready } = useAuth()
  if (!ready) return <div className="p-6">Loading…</div>
  if (!user) return <Navigate to="/login" replace />
  return <>{children}</>
}
