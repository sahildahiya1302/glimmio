# 🏢 CRM System

Comprehensive Customer Relationship Management system with HR integration.

## 🎯 Overview

A full-featured CRM platform with integrated HR capabilities, built with Node.js, Express, and PostgreSQL.

## 🚀 Features

### CRM Modules
- **Accounts**: Customer management with detailed profiles
- **Contacts**: Contact relationship management
- **Deals**: Sales pipeline and opportunity tracking
- **Activities**: Task and interaction tracking
- **Products**: Product catalog management
- **Invoices**: Billing and invoicing system

### HR Modules
- **Employees**: Employee management and profiles
- **Departments**: Organizational structure
- **Attendance**: Time tracking and leave management
- **Payroll**: Salary and compensation tracking
- **Performance**: Employee performance reviews

### Advanced Features
- **Automation**: Workflow automation for repetitive tasks
- **Analytics**: Performance dashboards and reports
- **Integration**: API integrations with external services
- **Mobile**: Responsive design for mobile access

## 🛠️ Tech Stack

- **Backend**: Node.js 18, Express.js
- **Database**: PostgreSQL 13, Redis
- **ORM**: Prisma
- **Authentication**: JWT, Passport.js
- **Validation**: Joi, Yup
- **Testing**: Jest, Supertest
- **Documentation**: Swagger/OpenAPI

## 📁 Project Structure

```
crm/
├── src/
│   ├── modules/
│   │   ├── crm/                    # CRM functionality
│   │   │   ├── accounts/
│   │   │   ├── contacts/
│   │   │   ├── deals/
│   │   │   └── activities/
│   │   ├── hr/                     # HR functionality
│   │   │   ├── employees/
│   │   │   ├── departments/
│   │   │   └── attendance/
│   │   └── analytics/               # Reports & dashboards
│   ├── services/                    # Business logic
│   ├── routes/                      # API endpoints
│   ├── middleware/                  # Authentication & validation
│   └── utils/                       # Utilities & helpers
├── database/
│   ├── migrations/
│   └── seeds/
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
└── docs/
    ├── api/
    └── guides/
```

## 🚀 Getting Started

### Prerequisites
- Node.js 18+
- PostgreSQL 13+
- Redis 6+
- npm or yarn

### Installation
```bash
# Install dependencies
npm install

# Setup database
npm run db:migrate
npm run db:seed

# Start development server
npm run dev

# Start production server
npm start
```

### Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Required environment variables
DATABASE_URL=postgresql://user:password@localhost:5432/crm
REDIS_URL=redis://localhost:6379
JWT_SECRET=your-jwt-secret
PORT=3001
```

## 🧪 Testing

```bash
# Run all tests
npm test

# Run specific test suites
npm run test:unit
npm run test:integration
npm run test:e2e

# Run tests in watch mode
npm run test:watch
```

## 📊 API Documentation

### Authentication
```bash
# Login
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

# Register
POST /api/auth/register
{
  "email": "user@example.com",
  "password": "password",
  "name": "John Doe"
}
```

### CRM Endpoints
```bash
# Accounts
GET    /api/crm/accounts
POST   /api/crm/accounts
GET    /api/crm/accounts/:id
PUT    /api/crm/accounts/:id
DELETE /api/crm/accounts/:id

# Contacts
GET    /api/crm/contacts
POST   /api/crm/contacts
GET    /api/crm/contacts/:id
PUT    /api/crm/contacts/:id
DELETE /api/crm/contacts/:id

# Deals
GET    /api/crm/deals
POST   /api/crm/deals
GET    /api/crm/deals/:id
PUT    /api/crm/deals/:id
DELETE /api/crm/deals/:id
```

## 🚀 Deployment

### Docker
```bash
# Build and run with Docker
docker-compose up -d

# Or build manually
docker build -t crm .
docker run -p 3001:3001 crm
```

### Environment Variables
```bash
# Required
DATABASE_URL=postgresql://user:password@localhost:5432/crm
REDIS_URL=redis://localhost:6379
JWT_SECRET=your-jwt-secret
PORT=3001

# Optional
NODE_ENV=production
LOG_LEVEL=info
```

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-org/crm/issues)
- **Email**: support@yourcompany.com
