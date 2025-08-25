# 🛒 Store - E-commerce Platform

Complete e-commerce platform with advanced features for online retail operations.

## 🎯 Overview

A full-featured e-commerce platform built with modern technologies, providing everything needed for online retail operations.

## 🚀 Features

### Core E-commerce
- **Product Management**: Advanced catalog with variants, categories, and attributes
- **Shopping Cart**: Persistent cart with guest checkout
- **Checkout Process**: Multi-step checkout with address management
- **Payment Processing**: Multiple payment gateways (Stripe, PayPal, Razorpay)
- **Order Management**: Complete order lifecycle management
- **Inventory Management**: Real-time stock tracking and alerts

### Advanced Features
- **Multi-vendor Support**: Marketplace functionality
- **SEO Optimization**: Product SEO and meta management
- **Mobile Responsive**: Mobile-first responsive design
- **Analytics**: Google Analytics and Facebook Pixel integration
- **Reviews & Ratings**: Customer review system
- **Wishlist**: Customer wishlist functionality
- **Discounts**: Coupon codes and promotional campaigns

### Admin Features
- **Dashboard**: Sales analytics and performance metrics
- **Product Management**: Bulk product import/export
- **Order Management**: Order processing and fulfillment
- **Customer Management**: Customer profiles and order history
- **Reports**: Sales reports and analytics
- **Settings**: Store configuration and preferences

## 🛠️ Tech Stack

- **Backend**: PHP 8.1, Laravel 9
- **Database**: MySQL 8.0, Redis
- **Frontend**: React 18, TypeScript
- **Styling**: Tailwind CSS
- **State Management**: Redux Toolkit
- **Payment**: Stripe, PayPal, Razorpay
- **File Storage**: AWS S3
- **CDN**: CloudFront

## 📁 Project Structure

```
store/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Providers/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeds/
│   └── routes/
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── hooks/
│   │   └── services/
│   └── public/
├── storage/
│   ├── app/
│   └── uploads/
└── docs/
    ├── api/
    └── guides/
```

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Redis 6+
- Composer
- Node.js 18+ (for frontend)

### Installation
```bash
# Install backend dependencies
composer install

# Install frontend dependencies
npm install

# Setup environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start development server
php artisan serve
```

### Environment Setup
```bash
# Required
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=store
DB_USERNAME=root
DB_PASSWORD=password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Payment Gateways
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
PAYPAL_CLIENT_ID=your-paypal-client-id
PAYPAL_CLIENT_SECRET=your-paypal-client-secret
```

## 🧪 Testing

```bash
# Run backend tests
php artisan test

# Run frontend tests
npm test

# Run E2E tests
npm run test:e2e
```

## 🚀 Deployment

### Docker
```bash
# Build and run
docker-compose up -d

# Or build manually
docker build -t store .
docker run -p 3003:80 store
```

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-org/store/issues)
- **Email**: support@yourcompany.com
