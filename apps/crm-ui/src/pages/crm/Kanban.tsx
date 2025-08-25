import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../../lib/api'

export default function Kanban(){
  const qc = useQueryClient()
  const { data } = useQuery({ queryKey:['kanban'], queryFn: async()=> (await api.get('/crm/deals/kanban?pipeline_id=1')).data })
  const mMove = useMutation({
    mutationFn: async ({id, stage_id}:{id:number; stage_id:number}) => (await api.post(`/crm/deals/${id}/move`, { stage_id })).data,
    onSuccess: ()=> qc.invalidateQueries({queryKey:['kanban']})
  })
  return (
    <div className="grid md:grid-cols-4 gap-4">
      {(data||[]).map((col:any)=>(
        <div key={col.stage.id} className="card">
          <div className="font-semibold mb-2">{col.stage.name}</div>
          <div className="space-y-2">
            {col.deals.map((d:any)=>(
              <div key={d.id} className="bg-white border rounded-xl p-3">
                <div className="font-medium">{d.title}</div>
                <div className="text-xs text-gray-600">₹{d.amount?.toLocaleString()} · {d.account_name||'-'}</div>
                <div className="flex gap-2 mt-2 flex-wrap">
                  {(data||[]).map((s:any)=>(
                    <button key={s.stage.id} className="btn" onClick={()=>mMove.mutate({id:d.id, stage_id:s.stage.id})}>{s.stage.name}</button>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  )
}
