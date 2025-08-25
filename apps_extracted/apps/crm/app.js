cat > /tmp/app.js.new <<'EOF'
import express from 'express'
import dotenv from 'dotenv'
import helmet from 'helmet'
import rateLimit from 'express-rate-limit'
import cors from 'cors'

import authRoutes from './routes/auth.routes.js'
import userRoutes from './routes/users.routes.js'
import attendanceRoutes from './routes/attendance.routes.js'
import leavesRoutes from './routes/leaves.routes.js'
import learningRoutes from './routes/learning.routes.js'
import leadsRoutes from './routes/leads.routes.js'
import tasksRoutes from './routes/tasks.routes.js'
import hrDepartmentsRoutes from './routes/hr.departments.routes.js'
import hrDesignationsRoutes from './routes/hr.designations.routes.js'
import hrEmployeesRoutes from './routes/hr.employees.routes.js'
import crmAccountsRoutes from './routes/crm.accounts.routes.js'
import crmContactsRoutes from './routes/crm.contacts.routes.js'
import crmPipelinesRoutes from './routes/crm.pipelines.routes.js'
import crmDealsRoutes from './routes/crm.deals.routes.js'
import crmActivitiesRoutes from './routes/crm.activities.routes.js'
import crmProductsRoutes from './routes/crm.products.routes.js'
import crmQuotesRoutes from './routes/crm.quotes.routes.js'
import crmInvoicesRoutes from './routes/crm.invoices.routes.js'
import crmLeadsRoutes from './routes/crm.leads.routes.js'
import crmReportsRoutes from './routes/crm.reports.routes.js'
import crmSearchRoutes from './routes/crm.search.routes.js'

import { errorHandler, notFound } from './middlewares/error.js'

dotenv.config()
const app = express()
const PORT = process.env.PORT || 3002

app.set('trust proxy', 1)

// Security
app.use(helmet())
app.disable('x-powered-by')

// Parsers
app.use(express.json({ limit: '1mb' }))
app.use(express.urlencoded({ extended: true }))

// CORS only in dev
if (process.env.NODE_ENV !== 'production') {
  app.use(cors({ origin: ['http://localhost:5173','http://127.0.0.1:5173'], credentials: false }))
}

// Rate limit
const limiter = rateLimit({ windowMs: 15 * 60 * 1000, limit: 500 })
app.use(limiter)

// Health
app.get('/health', (_req, res) => res.json({ ok: true }))

// Routes
app.use('/auth', authRoutes)
app.use('/users', userRoutes)
app.use('/attendance', attendanceRoutes)
app.use('/leaves', leavesRoutes)
app.use('/learning', learningRoutes)
app.use('/leads', leadsRoutes)
app.use('/tasks', tasksRoutes)
app.use('/hr/departments', hrDepartmentsRoutes)
app.use('/hr/designations', hrDesignationsRoutes)
app.use('/hr/employees', hrEmployeesRoutes)
app.use('/crm/accounts', crmAccountsRoutes)
app.use('/crm/contacts', crmContactsRoutes)
app.use('/crm/pipelines', crmPipelinesRoutes)
app.use('/crm/deals', crmDealsRoutes)
app.use('/crm/activities', crmActivitiesRoutes)
app.use('/crm/products', crmProductsRoutes)
app.use('/crm/quotes', crmQuotesRoutes)
app.use('/crm/invoices', crmInvoicesRoutes)
app.use('/crm/leads', crmLeadsRoutes)
app.use('/crm/reports', crmReportsRoutes)
app.use('/crm/search', crmSearchRoutes)

// JSON 404 + error
app.use(notFound)
app.use(errorHandler)

// Bind to loopback; Apache proxies /api -> 127.0.0.1:3002
app.listen(PORT, '127.0.0.1', () => {
  console.log(`CRM API listening on 127.0.0.1:${PORT}`)
})
EOF

