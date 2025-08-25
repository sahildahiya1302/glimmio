import { useParams } from 'react-router-dom'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '../../lib/api'

export default function CourseDetail(){
  const { id } = useParams()
  const qc = useQueryClient()
  const { data:course } = useQuery({ queryKey:['course',id], queryFn: async()=> (await api.get(`/learning/courses/${id}`)).data })
  const mEnroll = useMutation({ mutationFn: async()=> (await api.post('/learning/enroll', { course_id:Number(id) })).data })
  const mComplete = useMutation({
    mutationFn: async(lesson_id:number)=> (await api.post(`/learning/lessons/${lesson_id}/complete`)).data,
    onSuccess:()=> qc.invalidateQueries({queryKey:['course',id]})
  })

  if (!course) return null
  return (
    <div className="space-y-4">
      <div className="card">
        <div className="flex items-center justify-between">
          <div>
            <div className="text-xl font-bold">{course.title}</div>
            <div className="text-sm text-gray-600">{course.category} · {course.level}</div>
          </div>
          <button className="btn btn-primary" onClick={()=>mEnroll.mutate()}>Enroll</button>
        </div>
      </div>

      {course.modules?.map((m:any)=>(
        <div key={m.id} className="card">
          <div className="font-semibold mb-2">{m.title}</div>
          <ul className="space-y-2">
            {m.lessons?.map((l:any)=>(
              <li key={l.id} className="flex items-center justify-between bg-gray-50 p-3 rounded-xl">
                <div>
                  <div className="font-medium">{l.title}</div>
                  <div className="text-xs text-gray-600">{l.type}{l.duration_minutes?` · ${l.duration_minutes}m`:''}</div>
                </div>
                <div className="flex gap-2">
                  {l.file_path && <a href={l.file_path} className="btn" target="_blank">Download</a>}
                  {l.type!=='quiz' && <button className="btn btn-primary" onClick={()=>mComplete.mutate(l.id)}>Mark Done</button>}
                </div>
              </li>
            ))}
          </ul>
        </div>
      ))}
    </div>
  )
}
