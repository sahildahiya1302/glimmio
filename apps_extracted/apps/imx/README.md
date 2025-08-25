# 📱 IMX - Influencer Marketing Exchange

Advanced influencer marketing platform connecting brands with influencers across multiple social media platforms.

## 🎯 Overview

IMX is a comprehensive influencer marketing platform that provides:
- **Brand Campaign Management**: Create and manage influencer campaigns
- **Influencer Discovery**: AI-powered influencer matching
- **Performance Analytics**: Real-time campaign tracking
- **Payment Processing**: Automated influencer payments
- **Platform Integration**: Instagram, TikTok, YouTube, Twitter

## 🚀 Features

### For Brands
- **Campaign Creation**: Easy campaign setup with targeting
- **Influencer Discovery**: AI-powered matching algorithm
- **Performance Tracking**: Real-time analytics dashboard
- **Budget Management**: Automated budget allocation
- **Content Approval**: Streamlined approval workflow

### For Influencers
- **Profile Management**: Unified profile across platforms
- **Campaign Discovery**: Browse available campaigns
- **Content Submission**: Easy content upload and tracking
- **Payment Tracking**: Transparent payment system
- **Analytics**: Performance insights and earnings

### Platform Features
- **Multi-Platform Integration**: Instagram, TikTok, YouTube, Twitter
- **AI Matching**: Machine learning-based influencer matching
- **Real-time Analytics**: Live campaign performance tracking
- **Automated Payments**: Smart contract-based payments
- **Content Moderation**: AI-powered content review

## 🛠️ Tech Stack

- **Backend**: Node.js 18, Express.js
- **Database**: MongoDB, Redis
- **Frontend**: React 18, TypeScript
- **State Management**: Redux Toolkit
- **UI Framework**: Material-UI
- **Real-time**: Socket.io
- **File Storage**: AWS S3
- **CDN**: CloudFront

## 📁 Project Structure

```
imx/
├── backend/
│   ├── src/
│   │   ├── controllers/
│   │   ├── services/
│   │   ├── models/
│   │   ├── routes/
│   │   ├── middleware/
│   │   └── utils/
│   └── tests/
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── hooks/
│   │   ├── services/
│   │   └── store/
│   └── public/
├── shared/
│   ├── types/
│   ├── constants/
│   └── utils/
└── docs/
    ├── api/
    └── integration/
```

## 🚀 Getting Started

### Prerequisites
- Node.js 18+
- MongoDB 5+
- Redis 6+
- npm or yarn

### Installation
```bash
# Install dependencies
npm install

# Setup environment
cp .env.example .env

# Start MongoDB and Redis
docker-compose up -d mongodb redis

# Run database migrations
npm run db:migrate

# Start development servers
npm run dev:backend
npm run dev:frontend
```

### Environment Setup
```bash
# Backend
DATABASE_URL=mongodb://localhost:27017/imx
REDIS_URL=redis://localhost:6379
JWT_SECRET=your-jwt-secret
PORT=3002

# Platform APIs
INSTAGRAM_CLIENT_ID=your-instagram-client-id
INSTAGRAM_CLIENT_SECRET=your-instagram-client-secret
TIKTOK_CLIENT_ID=your-tiktok-client-id
YOUTUBE_API_KEY=your-youtube-api-key

# File Storage
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_BUCKET_NAME=your-bucket-name
```

## 🧪 Testing

```bash
# Run all tests
npm test

# Run backend tests
npm run test:backend

# Run frontend tests
npm run test:frontend

# Run E2E tests
npm run test:e2e
```

## 📊 API Documentation

### Authentication
```bash
# Brand Registration
POST /api/auth/register/brand
{
  "email": "brand@company.com",
  "password": "password",
  "companyName": "Brand Inc",
  "website": "https://brand.com"
}

# Influencer Registration
POST /api/auth/register/influencer
{
  "email": "creator@example.com",
  "password": "password",
  "username": "creator123",
  "platforms": ["instagram", "tiktok"]
}
```

### Campaign Management
```bash
# Create Campaign
POST /api/campaigns
{
  "title": "Summer Collection Launch",
  "description": "Promote our new summer collection",
  "budget": 10000,
  "platforms": ["instagram", "tiktok"],
  "requirements": {
    "minFollowers": 10000,
    "engagementRate": 3.0
  }
}

# Get Campaigns
GET /api/campaigns
GET /api/campaigns/:id

# Update Campaign
PUT /api/campaigns/:id
```

### Influencer Discovery
```bash
# Search Influencers
GET /api/influencers/search?platform=instagram&minFollowers=10000

# Get Influencer Profile
GET /api/influencers/:id

# Get Influencer Analytics
GET /api/influencers/:id/analytics
```

### Campaign Analytics
```bash
# Get Campaign Performance
GET /api/campaigns/:id/analytics

# Get Influencer Performance
GET /api/campaigns/:id/influencers/:influencerId/performance
```

## 🚀 Deployment

### Docker
```bash
# Build and run
docker-compose up -d

# Or build manually
docker build -t imx-backend ./backend
docker build -t imx-frontend ./frontend
```

### Environment Variables
```bash
# Required
DATABASE_URL=mongodb://localhost:27017/imx
REDIS_URL=redis://localhost:6379
JWT_SECRET=your-jwt-secret
PORT=3002

# Platform APIs
INSTAGRAM_CLIENT_ID=your-instagram-client-id
INSTAGRAM_CLIENT_SECRET=your-instagram-client-secret
TIKTOK_CLIENT_ID=your-tiktok-client-id
YOUTUBE_API_KEY=your-youtube-api-key

# File Storage
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_BUCKET_NAME=your-bucket-name
```

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-org/imx/issues)
- **Email**: support@yourcompany.com
