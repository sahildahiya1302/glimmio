import { useEffect, useState } from 'react'
import { api } from '../../lib/api'

export default function Quotes(){
  const [id,setId] = useState<number|''>('')   // current quote id to view
  const [quote,setQuote] = useState<any>(null)
  const [form,setForm] = useState({ product_id:'', name:'', price:'', qty:'1', discount:'0', tax_rate:'0' })

  async function fetchQuote(qid:number){ const d = await api.get(`/crm/quotes/${qid}`); setQuote(d.data) }
  useEffect(()=>{ if(typeof id==='number') fetchQuote(id) },[id])

  return (
    <div className="grid md:grid-cols-2 gap-4">
      <div className="card">
        <div className="font-semibold mb-2">Open Quote</div>
        <div className="flex gap-2 mb-3">
          <input className="input" placeholder="Quote ID…" value={id} onChange={e=>setId(Number(e.target.value)||'')}/>
          <button className="btn btn-primary" onClick={()=>typeof id==='number' && fetchQuote(id)}>Load</button>
        </div>

        {quote && (
          <div>
            <div className="mb-2 text-sm text-gray-600">#{quote.quote_no} · {quote.currency}</div>
            <table className="table mb-3">
              <thead><tr className="text-left"><th>Item</th><th>Qty</th><th>Price</th><th>Tax%</th><th>Amt</th></tr></thead>
              <tbody>
                {quote.items?.map((it:any)=>(
                  <tr key={it.id}><td>{it.name}</td><td>{it.qty}</td><td>{it.price}</td><td>{it.tax_rate}</td><td>{it.amount}</td></tr>
                ))}
              </tbody>
            </table>
            <div className="text-right text-sm">Subtotal: ₹{quote.subtotal} · Tax: ₹{quote.tax_total} · <b>Total: ₹{quote.total}</b></div>
          </div>
        )}
      </div>

      <div className="card">
        <div className="font-semibold mb-2">Add Item</div>
        <div className="grid grid-cols-2 gap-2">
          <input className="input" placeholder="Name" value={form.name} onChange={e=>setForm(f=>({...f,name:e.target.value}))}/>
          <input className="input" placeholder="Price" value={form.price} onChange={e=>setForm(f=>({...f,price:e.target.value}))}/>
          <input className="input" placeholder="Qty" value={form.qty} onChange={e=>setForm(f=>({...f,qty:e.target.value}))}/>
          <input className="input" placeholder="Discount" value={form.discount} onChange={e=>setForm(f=>({...f,discount:e.target.value}))}/>
          <input className="input" placeholder="Tax %" value={form.tax_rate} onChange={e=>setForm(f=>({...f,tax_rate:e.target.value}))}/>
        </div>
        <button className="btn btn-primary mt-3" disabled={typeof id!=='number'} onClick={async()=>{
          await api.post(`/crm/quotes/${id}/items`, {
            name: form.name, price:Number(form.price), qty:Number(form.qty||1),
            discount:Number(form.discount||0), tax_rate:Number(form.tax_rate||0)
          })
          fetchQuote(id as number)
        }}>Add</button>
      </div>
    </div>
  )
}
