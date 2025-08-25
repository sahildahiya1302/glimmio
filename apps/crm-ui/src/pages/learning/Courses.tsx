import { useQuery } from '@tanstack/react-query'
import { api } from '../../lib/api'
import { Link } from 'react-router-dom'

export default function Courses(){
  const { data } = useQuery({ queryKey:['courses'], queryFn: async()=> (await api.get('/learning/courses')).data })
  return (
    <div className="grid md:grid-cols-3 gap-4">
      {(data||[]).map((c:any)=>(
        <Link to={`/learning/course/${c.id}`} key={c.id} className="card hover:shadow-lg transition">
          <div className="text-lg font-semibold">{c.title}</div>
          <div className="text-sm text-gray-600 line-clamp-2">{c.description}</div>
        </Link>
      ))}
    </div>
  )
}
