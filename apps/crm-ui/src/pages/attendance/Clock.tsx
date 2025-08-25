import { useState } from 'react'
import { api } from '../../lib/api'

export default function Clock(){
  const [msg,setMsg] = useState('')
  const [busy,setBusy] = useState(false)

  async function hit(path:string, body:any={}){
    setBusy(true); setMsg('')
    try { const res = await api.post(path, body); setMsg(JSON.stringify(res.data)) }
    catch(e:any){ setMsg(e?.response?.data?.message || 'error') }
    finally { setBusy(false) }
  }

  return (
    <div className="card max-w-lg">
      <h2 className="font-semibold mb-2">Clock</h2>
      <div className="flex gap-2">
        <button className="btn btn-primary" disabled={busy} onClick={()=>hit('/attendance/clock-in', { source: 'web' })}>Clock In</button>
        <button className="btn" disabled={busy} onClick={()=>hit('/attendance/clock-out')}>Clock Out</button>
      </div>
      <pre className="mt-3 text-xs bg-gray-50 p-2 rounded">{msg}</pre>
    </div>
  )
}
