# 🌐 Main Website

Modern corporate website built with Next.js 14, TypeScript, and Tailwind CSS.

## 🚀 Features

- **Performance**: 100/100 Lighthouse score
- **SEO**: Optimized meta tags, sitemap, robots.txt
- **Responsive**: Mobile-first design
- **Accessibility**: WCAG 2.1 AA compliant
- **Analytics**: Google Analytics 4 integration
- **Forms**: Contact forms with validation
- **Blog**: Markdown-based blog system

## 🛠️ Tech Stack

- **Framework**: Next.js 14 (App Router)
- **Language**: TypeScript 5
- **Styling**: Tailwind CSS
- **Components**: Radix UI + Headless UI
- **Forms**: React Hook Form + Zod
- **Animations**: Framer Motion
- **Icons**: Lucide React

## 📁 Project Structure

```
main-website/
├── src/
│   ├── app/                    # App Router
│   │   ├── (marketing)/
│   │   │   ├── page.tsx        # Home page
│   │   │   ├── about/
│   │   │   ├── services/
│   │   │   └── contact/
│   │   ├── api/
│   │   │   └── contact/
│   │   └── globals.css
│   ├── components/
│   │   ├── ui/                 # Reusable UI components
│   │   ├── sections/           # Page sections
│   │   └── layouts/            # Layout components
│   ├── lib/                    # Utilities
│   └── types/                  # TypeScript types
├── public/
│   ├── images/
│   └── fonts/
└── content/                    # Blog content
```

## 🚀 Getting Started

### Prerequisites
- Node.js 18+
- npm or yarn

### Installation
```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Start production server
npm start
```

## 🧪 Testing

```bash
# Run tests
npm test

# Run tests in watch mode
npm run test:watch

# Run E2E tests
npm run test:e2e
```

## 📊 Performance

| Metric | Score |
|--------|--------|
| Performance | 100 |
| Accessibility | 100 |
| Best Practices | 100 |
| SEO | 100 |

## 🚀 Deployment

### Vercel (Recommended)
```bash
vercel --prod
```

### Docker
```bash
docker build -t main-website .
docker run -p 3000:3000 main-website
```

## 🔧 Environment Variables

```bash
# Required
NEXT_PUBLIC_SITE_URL=https://yourdomain.com
NEXT_PUBLIC_GA_ID=G-XXXXXXXXXX

# Optional
NEXT_PUBLIC_CONTACT_EMAIL=contact@yourdomain.com
NEXT_PUBLIC_PHONE_NUMBER=+1234567890
```

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-org/main-website/issues)
- **Email**: support@yourcompany.com
