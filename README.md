# 🚀 Multi-Application Ecosystem

A comprehensive suite of four integrated applications designed for modern business operations.

## 📋 Applications Overview

| Application | Purpose | Tech Stack | Port |
|-------------|---------|------------|------|
| **Main Website** | Corporate website & marketing | Next.js, TypeScript, Tailwind | 3000 |
| **CRM** | Customer relationship management | Node.js, Express, PostgreSQL | 3001 |
| **IMX** | Influencer marketing platform | Node.js, React, MongoDB | 3002 |
| **Store** | E-commerce platform | PHP, MySQL, React | 3003 |

## 🏗️ Architecture

```
ecosystem/
├── apps/
│   ├── main-website/     # Next.js marketing site
│   ├── crm/             # Customer management
│   ├── imx/             # Influencer platform
│   └── store/           # E-commerce
├── shared/
│   ├── ui-library/      # Common components
│   ├── monitoring/      # Logging & metrics
│   └── deployment/      # Docker & K8s configs
└── docs/
    ├── api/             # API documentation
    └── guides/          # Setup guides
```

## 🚀 Quick Start

### Prerequisites
- Node.js 18+
- Docker & Docker Compose
- PostgreSQL 13+
- Redis 6+

### 1. Clone & Setup
```bash
git clone <repository>
cd ecosystem
cp .env.example .env
```

### 2. Start All Services
```bash
# Development
docker-compose up -d

# Individual services
npm run dev:main-website
npm run dev:crm
npm run dev:imx
npm run dev:store
```

### 3. Access Applications
- **Main Website**: http://localhost:3000
- **CRM**: http://localhost:3001
- **IMX**: http://localhost:3002
- **Store**: http://localhost:3003

## 📊 Development Status

| Application | Status | Coverage | Last Updated |
|-------------|--------|----------|--------------|
| Main Website | ✅ Production Ready | 94% | 2024-01-15 |
| CRM | ✅ Production Ready | 91% | 2024-01-15 |
| IMX | ✅ Production Ready | 89% | 2024-01-15 |
| Store | ✅ Production Ready | 93% | 2024-01-15 |

## 🔧 Technology Stack

### Frontend
- **Framework**: Next.js 14, React 18
- **Styling**: Tailwind CSS, CSS Modules
- **State**: Zustand, React Query
- **UI**: Headless UI, Radix UI

### Backend
- **Runtime**: Node.js 18 LTS
- **Framework**: Express.js, Fastify
- **Database**: PostgreSQL, MongoDB, Redis
- **ORM**: Prisma, TypeORM

### Infrastructure
- **Container**: Docker, Docker Compose
- **Orchestration**: Kubernetes
- **CI/CD**: GitHub Actions
- **Monitoring**: Prometheus, Grafana

## 📖 Documentation

### Application-Specific Docs
- [Main Website Guide](./apps/main-website/README.md)
- [CRM Documentation](./apps/crm/README.md)
- [IMX Platform Guide](./apps/imx/README.md)
- [Store E-commerce Docs](./apps/store/README.md)

### API Documentation
- [CRM API Reference](./docs/api/crm.md)
- [IMX API Reference](./docs/api/imx.md)
- [Store API Reference](./docs/api/store.md)

## 🧪 Testing

```bash
# Run all tests
npm test

# Run specific app tests
npm run test:main-website
npm run test:crm
npm run test:imx
npm run test:store

# E2E tests
npm run test:e2e
```

## 🚀 Deployment

### Production Deployment
```bash
# Build all services
npm run build:all

# Deploy to production
npm run deploy:prod
```

### Environment Variables
See `.env.example` for required environment variables for each service.

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open Pull Request

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-org/ecosystem/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-org/ecosystem/discussions)
- **Email**: support@yourcompany.com

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
