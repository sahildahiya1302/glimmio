import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../../lib/api'
import { useState } from 'react'

export default function Leaves(){
  const qc = useQueryClient()
  const { data:types } = useQuery({ queryKey:['leave-types'], queryFn: async()=> (await api.get('/leaves/types/active')).data })
  const { data:reqs } = useQuery({ queryKey:['leave-reqs'], queryFn: async()=> (await api.get('/leaves/requests')).data })

  const [form,setForm] = useState({ type_id:'', start_date:'', end_date:'', reason:'' })
  const mApply = useMutation({
    mutationFn: async ()=> (await api.post('/leaves/requests', { ...form, type_id:Number(form.type_id) })).data,
    onSuccess: ()=> { setForm({ type_id:'', start_date:'', end_date:'', reason:'' }); qc.invalidateQueries({queryKey:['leave-reqs']}) }
  })

  return (
    <div className="grid md:grid-cols-2 gap-4">
      <div className="card">
        <h2 className="font-semibold mb-2">Apply for Leave</h2>
        <div className="space-y-2">
          <div>
            <label className="label">Type</label>
            <select className="input" value={form.type_id} onChange={e=>setForm(f=>({...f,type_id:e.target.value}))}>
              <option value="">Select…</option>
              {(types||[]).map((t:any)=><option key={t.id} value={t.id}>{t.name}</option>)}
            </select>
          </div>
          <div className="grid grid-cols-2 gap-2">
            <div><label className="label">Start</label><input type="date" className="input" value={form.start_date} onChange={e=>setForm(f=>({...f,start_date:e.target.value}))}/></div>
            <div><label className="label">End</label><input type="date" className="input" value={form.end_date} onChange={e=>setForm(f=>({...f,end_date:e.target.value}))}/></div>
          </div>
          <div><label className="label">Reason</label><input className="input" value={form.reason} onChange={e=>setForm(f=>({...f,reason:e.target.value}))}/></div>
          <button className="btn btn-primary" onClick={()=>mApply.mutate()} disabled={mApply.isPending}>Submit</button>
          {mApply.isError && <div className="text-sm text-red-600 mt-2">{(mApply.error as any)?.response?.data?.message || 'Error'}</div>}
          {mApply.isSuccess && <div className="text-sm text-green-700 mt-2">Submitted.</div>}
        </div>
      </div>
      <div className="card">
        <h2 className="font-semibold mb-2">My Requests</h2>
        <table className="table">
          <thead><tr className="text-left"><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
          <tbody>
            {(reqs||[]).map((r:any)=>(
              <tr key={r.id}><td>{r.type_name}</td><td>{r.start_date?.slice(0,10)} → {r.end_date?.slice(0,10)}</td><td>{r.days}</td>
                <td><span className={`badge ${r.status==='approved'?'badge-green':'badge-yellow'}`}>{r.status}</span></td></tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
