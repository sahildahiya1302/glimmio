import { useState } from 'react'
import { api } from '../../lib/api'

export default function Invoices(){
  const [id,setId] = useState<number|''>('')
  const [invoice,setInvoice] = useState<any>(null)
  async function load(){ if(typeof id==='number'){ const r = await api.get(`/crm/invoices/${id}`); setInvoice(r.data) } }
  return (
    <div className="card">
      <div className="flex gap-2 mb-3">
        <input className="input" placeholder="Invoice ID…" value={id} onChange={e=>setId(Number(e.target.value)||'')}/>
        <button className="btn btn-primary" onClick={load}>Load</button>
        {invoice && <a className="btn" href={`${import.meta.env.VITE_API_BASE}/crm/invoices/${id}/pdf`} target="_blank">Download PDF</a>}
      </div>
      {invoice && (
        <div>
          <div className="text-sm text-gray-600">#{invoice.invoice_no} · {invoice.currency} · Status: {invoice.status}</div>
          <table className="table mt-2">
            <thead><tr className="text-left"><th>Item</th><th>Qty</th><th>Price</th><th>Tax%</th><th>Amt</th></tr></thead>
            <tbody>
              {invoice.items?.map((it:any)=>(
                <tr key={it.id}><td>{it.name}</td><td>{it.qty}</td><td>{it.price}</td><td>{it.tax_rate}</td><td>{it.amount}</td></tr>
              ))}
            </tbody>
          </table>
          <div className="text-right text-sm mt-2">Subtotal: ₹{invoice.subtotal} · Tax: ₹{invoice.tax_total} · <b>Total: ₹{invoice.total}</b></div>
        </div>
      )}
    </div>
  )
}
