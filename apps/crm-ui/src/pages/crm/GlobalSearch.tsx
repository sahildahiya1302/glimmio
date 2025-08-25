import { useLocation } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { api } from '../../lib/api'

export default function GlobalSearch(){
  const q = new URLSearchParams(useLocation().search).get('q') || ''
  const { data } = useQuery({ queryKey:['search',q], queryFn: async()=> (await api.get(`/crm/search?q=${encodeURIComponent(q)}`)).data })
  if (!q) return <div className="card">Type something in the search bar.</div>
  return (
    <div className="grid md:grid-cols-2 gap-4">
      {['accounts','contacts','deals','leads'].map((k:any)=>(
        <div key={k} className="card">
          <div className="font-semibold capitalize mb-2">{k}</div>
          <ul className="space-y-1 text-sm">
            {(data?.[k]||[]).map((r:any)=> <li key={`${k}-${r.id}`} className="flex justify-between"><span>{r.name || r.title || r.domain}</span><span className="text-gray-500">#{r.id}</span></li>)}
          </ul>
        </div>
      ))}
    </div>
  )
}
