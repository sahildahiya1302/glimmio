import { useQuery } from "@tanstack/react-query"
import { api } from "../../lib/api"

export default function Reports(){
  const { data:pipe } = useQuery({ queryKey:["rep-pipeline"],  queryFn: async()=> (await api.get("/crm/reports/pipeline?pipeline_id=1")).data })
  const { data:win }  = useQuery({ queryKey:["rep-win"],       queryFn: async()=> (await api.get("/crm/reports/winrate?days=90")).data })
  const { data:forecast } = useQuery({ queryKey:["rep-forecast"], queryFn: async()=> (await api.get("/crm/reports/forecast?months=4")).data })

  return (
    <div className="grid md:grid-cols-3 gap-4">
      <div className="card">
        <h3 className="font-semibold mb-2">Pipeline</h3>
        <ul className="space-y-1">
          {(pipe||[]).map((s:any)=>(
            <li key={s.stage_id} className="flex justify-between">
              <span>{s.stage_name}</span>
              <span>₹{s.amount}</span>
            </li>
          ))}
        </ul>
      </div>

      <div className="card">
        <h3 className="font-semibold mb-2">Win Rate</h3>
        <div className="text-3xl font-bold">{win?.win_rate_pct ?? 0}%</div>
        <div className="text-sm text-gray-600">Won {win?.won} · Lost {win?.lost}</div>
      </div>

      <div className="card">
        <h3 className="font-semibold mb-2">Forecast</h3>
        <ul className="space-y-1">
          {(forecast||[]).map((m:any)=>(
            <li key={m.month} className="flex justify-between">
              <span>{m.month}</span>
              <span>Won ₹{m.won_amount} / Open ₹{m.open_amount}</span>
            </li>
          ))}
        </ul>
      </div>
    </div>
  )
}
