import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../../lib/api'
import { useState } from 'react'

export default function Leads(){
  const qc = useQueryClient()
  const [q,setQ] = useState('')
  const { data } = useQuery({ queryKey:['leads',q], queryFn: async()=> (await api.get(`/crm/leads?q=${encodeURIComponent(q)}`)).data })
  const mConvert = useMutation({
    mutationFn: async(id:number)=> (await api.post(`/crm/leads/${id}/convert`, { create_deal:true })).data,
    onSuccess:()=> qc.invalidateQueries({queryKey:['leads']})
  })
  return (
    <div className="card">
      <div className="flex gap-2 mb-3">
        <input className="input" placeholder="Search leads…" value={q} onChange={e=>setQ(e.target.value)}/>
      </div>
      <table className="table">
        <thead><tr className="text-left"><th>Name</th><th>Company</th><th>Email</th><th>Status</th><th></th></tr></thead>
        <tbody>
          {(data||[]).map((l:any)=>(
            <tr key={l.id}>
              <td>{l.first_name} {l.last_name}</td>
              <td>{l.company}</td>
              <td>{l.email}</td>
              <td>{l.status}</td>
              <td><button className="btn btn-primary" onClick={()=>mConvert.mutate(l.id)} disabled={l.status==='converted'}>Convert</button></td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
