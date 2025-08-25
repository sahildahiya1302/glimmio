import { useQuery } from '@tanstack/react-query'
import { api } from '../lib/api'

function usePipeline() {
  return useQuery({
    queryKey: ['rep-pipeline'],
    queryFn: async () => (await api.get('/crm/reports/pipeline?pipeline_id=1')).data as Array<{ stage_id:number; stage_name:string; amount:number }>,
  })
}
function useWinrate() {
  return useQuery({
    queryKey: ['rep-winrate'],
    queryFn: async () => (await api.get('/crm/reports/winrate?days=90')).data as { won:number; lost:number; win_rate_pct:number },
  })
}
function useForecast() {
  return useQuery({
    queryKey: ['rep-forecast'],
    queryFn: async () => (await api.get('/crm/reports/forecast?months=4')).data as Array<{ month:string; won_amount:number; open_amount:number }>,
  })
}

export default function Dashboard() {
  const pipeline = usePipeline()
  const win = useWinrate()
  const forecast = useForecast()

  return (
    <div className="space-y-6">
      <div className="grid md:grid-cols-3 gap-4">
        <div className="card">
          <h3 className="font-semibold mb-2">Pipeline (₹)</h3>
          {pipeline.isLoading ? (
            <div className="text-sm text-gray-500">Loading…</div>
          ) : pipeline.isError ? (
            <div className="text-sm text-red-600">Couldn’t load pipeline.</div>
          ) : (
            <ul className="space-y-1">
              {pipeline.data!.map(s => (
                <li key={s.stage_id} className="flex justify-between">
                  <span>{s.stage_name}</span>
                  <span className="tabular-nums">₹{s.amount}</span>
                </li>
              ))}
            </ul>
          )}
        </div>

        <div className="card">
          <h3 className="font-semibold mb-2">Win rate (90d)</h3>
          {win.isLoading ? (
            <div className="text-sm text-gray-500">Loading…</div>
          ) : win.isError ? (
            <div className="text-sm text-red-600">Couldn’t load win rate.</div>
          ) : (
            <>
              <div className="text-3xl font-bold">{win.data!.win_rate_pct ?? 0}%</div>
              <div className="text-sm text-gray-600 mt-1">Won {win.data!.won} · Lost {win.data!.lost}</div>
            </>
          )}
        </div>

        <div className="card">
          <h3 className="font-semibold mb-2">Forecast (next 4 months)</h3>
          {forecast.isLoading ? (
            <div className="text-sm text-gray-500">Loading…</div>
          ) : forecast.isError ? (
            <div className="text-sm text-red-600">Couldn’t load forecast.</div>
          ) : (
            <ul className="space-y-1">
              {forecast.data!.map(m => (
                <li key={m.month} className="flex justify-between">
                  <span>{m.month}</span>
                  <span className="tabular-nums">Won ₹{m.won_amount} / Open ₹{m.open_amount}</span>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>

      <div className="card">
        <h3 className="font-semibold mb-2">Welcome back</h3>
        <p className="text-sm text-gray-600">Use the sidebar to navigate HR and CRM modules.</p>
      </div>
    </div>
  )
}
