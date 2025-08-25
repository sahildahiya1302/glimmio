# 📋 Product & Technical Requirements Document
## Microservices-Based CRM, IMX & Store Platform

**Version:** 1.0  
**Date:** 2024-01-15  
**Status:** Draft  

---

## 🎯 Executive Summary

This document outlines the complete product and technical requirements for rebuilding the CRM, IMX (Influencer Marketing Exchange), and Store applications from scratch using a modern microservices architecture. The goal is to preserve all existing features while enhancing UI/UX and adding advanced functionalities to create a scalable, maintainable, and user-friendly ecosystem.

### Key Objectives
- **Preserve**: All existing functionality from current applications
- **Enhance**: Modern UI/UX with improved user experience
- **Scale**: Microservices architecture for horizontal scaling
- **Integrate**: Seamless data flow between applications
- **Modernize**: Latest technology stack and best practices

---

## 📊 Current State Analysis

### Existing Applications Overview

| Application | Technology | Database | Status | Core Features |
|-------------|------------|-----------|--------|---------------|
| **CRM** | Node.js/Express | MySQL | Production | Customer management, HR, Analytics |
| **IMX** | Node.js/Express | MongoDB | Production | Influencer marketing, Campaign management |
| **Store** | PHP/Laravel | MySQL | Production | E-commerce, Multi-vendor support |

### Identified Pain Points
1. **Technology Inconsistency**: Mixed tech stack (Node.js + PHP)
2. **Data Silos**: Limited integration between applications
3. **Scalability Issues**: Monolithic architecture constraints
4. **UI/UX Fragmentation**: Inconsistent user experience
5. **Maintenance Overhead**: Multiple deployment processes

---

## 🏗️ Proposed Microservices Architecture

### Service Boundaries & Responsibilities

#### 1. Core Services

##### 1.1 **Authentication Service** (`auth-service`)
- **Responsibility**: User authentication, authorization, SSO
- **Port**: 3010
- **Database**: PostgreSQL
- **Features**:
  - JWT token management
  - Multi-factor authentication (MFA)
  - Social login integration
  - Role-based access control (RBAC)
  - Session management
  - Password policies

##### 1.2 **User Management Service** (`user-service`)
- **Responsibility**: User profiles, preferences, settings
- **Port**: 3011
- **Database**: PostgreSQL
- **Features**:
  - User profile management
  - Preference settings
  - Avatar/image management
  - Account verification
  - User analytics

##### 1.3 **Notification Service** (`notification-service`)
- **Responsibility**: Email, SMS, push notifications
- **Port**: 3012
- **Database**: PostgreSQL + Redis
- **Features**:
  - Multi-channel notifications
  - Template management
  - Scheduling and queuing
  - Delivery tracking
  - Preference management

##### 1.4 **File Management Service** (`file-service`)
- **Responsibility**: File upload, storage, CDN integration
- **Port**: 3013
- **Database**: MongoDB
- **Features**:
  - File upload/download
  - Image processing
  - CDN integration
  - File versioning
  - Access control

#### 2. CRM Services

##### 2.1 **CRM Core Service** (`crm-core-service`)
- **Responsibility**: Accounts, contacts, deals, activities
- **Port**: 3020
- **Database**: PostgreSQL
- **Features**:
  - Account management
  - Contact relationships
  - Deal pipeline
  - Activity tracking
  - Task management

##### 2.2 **CRM Analytics Service** (`crm-analytics-service`)
- **Responsibility**: Reports, dashboards, KPIs
- **Port**: 3021
- **Database**: PostgreSQL + ClickHouse
- **Features**:
  - Sales analytics
  - Performance metrics
  - Custom reports
  - Data visualization
  - Forecasting

##### 2.3 **HR Management Service** (`hr-service`)
- **Responsibility**: Employee management, attendance, payroll
- **Port**: 3022
- **Database**: PostgreSQL
- **Features**:
  - Employee profiles
  - Department management
  - Attendance tracking
  - Leave management
  - Performance reviews

#### 3. IMX Services

##### 3.1 **IMX Core Service** (`imx-core-service`)
- **Responsibility**: Campaigns, influencers, brands
- **Port**: 3030
- **Database**: MongoDB
- **Features**:
  - Campaign management
  - Influencer profiles
  - Brand management
  - Content management
  - Approval workflows

##### 3.2 **IMX Analytics Service** (`imx-analytics-service`)
- **Responsibility**: Performance tracking, ROI analysis
- **Port**: 3031
- **Database**: MongoDB + ClickHouse
- **Features**:
  - Campaign analytics
  - Influencer performance
  - ROI tracking
  - Engagement metrics
  - Trend analysis

##### 3.3 **Social Integration Service** (`social-service`)
- **Responsibility**: Platform APIs, data synchronization
- **Port**: 3032
- **Database**: MongoDB + Redis
- **Features**:
  - Instagram integration
  - TikTok integration
  - YouTube integration
  - Twitter integration
  - Data synchronization

#### 4. Store Services

##### 4.1 **Product Catalog Service** (`catalog-service`)
- **Responsibility**: Products, categories, inventory
- **Port**: 3040
- **Database**: PostgreSQL + Elasticsearch
- **Features**:
  - Product management
  - Category hierarchy
  - Inventory tracking
  - Search functionality
  - Recommendations

##### 4.2 **Order Management Service** (`order-service`)
- **Responsibility**: Cart, checkout, orders
- **Port**: 3041
- **Database**: PostgreSQL
- **Features**:
  - Shopping cart
  - Checkout process
  - Order processing
  - Order tracking
  - Returns management

##### 4.3 **Payment Service** (`payment-service`)
- **Responsibility**: Payment processing, refunds
- **Port**: 3042
- **Database**: PostgreSQL
- **Features**:
  - Multiple payment gateways
  - Payment processing
  - Refund management
  - Subscription billing
  - Invoice generation

##### 4.4 **Vendor Management Service** (`vendor-service`)
- **Responsibility**: Multi-vendor marketplace
- **Port**: 3043
- **Database**: PostgreSQL
- **Features**:
  - Vendor onboarding
  - Commission management
  - Vendor analytics
  - Payout processing
  - Vendor dashboard

#### 5. Shared Services

##### 5.1 **API Gateway** (`gateway-service`)
- **Responsibility**: Request routing, rate limiting, authentication
- **Port**: 3000
- **Features**:
  - Request routing
  - Load balancing
  - Rate limiting
  - Authentication middleware
  - API versioning

##### 5.2 **Event Bus Service** (`event-service`)
- **Responsibility**: Inter-service communication
- **Port**: 3050
- **Database**: Redis
- **Features**:
  - Event publishing
  - Event subscription
  - Message queuing
  - Dead letter queue
  - Event replay

---

## 🎨 UI/UX Enhancement Plan

### Design System

#### 1. **Unified Design Language**
- **Color Palette**: Modern, accessible color scheme
- **Typography**: System fonts with clear hierarchy
- **Spacing**: 8px grid system for consistency
- **Components**: Reusable component library
- **Icons**: Consistent icon library (Heroicons/Lucide)

#### 2. **Responsive Design**
- **Mobile-First**: Mobile-optimized layouts
- **Breakpoints**: Standard responsive breakpoints
- **Touch-Friendly**: Optimized for touch interfaces
- **Performance**: Optimized for mobile performance

### Screen-by-Screen UI Flows

#### CRM Application Screens

##### 1. **Authentication Flow**
```
Login → MFA Verification → Dashboard
     ↓
Registration → Email Verification → Profile Setup → Dashboard
     ↓
Password Reset → Email Link → New Password → Login
```

**Wireframe Requirements:**
- Clean, minimal login form
- Social login options
- Clear error states
- Loading indicators
- Responsive design

##### 2. **Dashboard Screen**
```
Navigation Sidebar | Main Content Area | Quick Actions Panel
- Apps Menu        | KPI Cards Row     | Recent Activities
- User Profile     | Charts Section    | Quick Create
- Search Global    | Recent Items      | Notifications
- Settings         | Activity Feed     | Help Center
```

**Features:**
- Customizable KPI widgets
- Real-time data updates
- Drag-and-drop dashboard
- Export functionality
- Mobile-responsive cards

##### 3. **Accounts Management**
```
List View:
Search Bar + Filters → Data Table → Pagination
- Quick filters       - Sortable columns  - Page controls
- Advanced search      - Bulk actions      - Items per page
- Export options       - Row actions       - Navigation

Detail View:
Header Info → Tabs Navigation → Content Area
- Account name  - Overview tab      - Account details
- Action buttons - Contacts tab      - Related records
- Status badge   - Deals tab         - Activity timeline
```

##### 4. **Contact Management**
```
Contact Profile:
Avatar + Basic Info → Contact Details → Interaction History
- Profile picture    - Contact information - Email history
- Name and title     - Social links        - Call logs
- Company info       - Custom fields       - Meeting notes
- Tags and labels    - Preferences         - Activity feed
```

##### 5. **Deal Pipeline**
```
Kanban Board:
Stage Columns → Deal Cards → Quick Actions
- Lead stage      - Deal summary    - Move between stages
- Qualified       - Value and owner - Edit inline
- Proposal        - Progress indicators - Archive/Delete
- Negotiation     - Due dates       - Notes and attachments
- Closed Won/Lost - Probability     - Activity tracking
```

##### 6. **HR Dashboard**
```
Employee Overview:
Summary Cards → Employee List → Quick Actions
- Total employees  - Employee table   - Add employee
- Active/Inactive  - Filter options   - Bulk operations
- Departments      - Search function  - Export data
- Recent hires     - Sort capabilities - Import employees
```

##### 7. **Attendance Tracking**
```
Time Clock Interface:
Clock In/Out → Time Summary → Attendance Calendar
- Current time     - Daily hours      - Monthly view
- Clock status     - Weekly summary   - Attendance history
- Break timer      - Overtime tracking - Leave requests
- Location check   - Time corrections - Approval workflow
```

#### IMX Application Screens

##### 1. **Campaign Dashboard**
```
Campaign Overview:
Campaign List → Performance Metrics → Quick Actions
- Active campaigns  - Reach metrics     - Create campaign
- Draft campaigns   - Engagement rates  - Clone campaign
- Completed         - ROI tracking      - Archive campaigns
- Search/Filter     - Budget utilization - Bulk operations
```

##### 2. **Influencer Discovery**
```
Search Interface:
Filter Panel → Influencer Grid → Profile Preview
- Platform filters   - Influencer cards  - Quick stats
- Follower range     - Profile images    - Engagement rate
- Location filter    - Basic metrics     - Contact info
- Category filter    - Availability      - Portfolio samples
- Rate filter        - Rating/Reviews    - Collaboration history
```

##### 3. **Campaign Creation Wizard**
```
Step-by-Step Flow:
Campaign Details → Target Audience → Budget & Timeline → Influencer Selection → Review & Launch
- Campaign name      - Demographics      - Budget allocation - Influencer search  - Final review
- Description        - Platform focus    - Timeline setup   - Selection criteria - Launch confirmation
- Objectives         - Interest targeting - Payment terms    - Invitation sending - Success metrics
```

##### 4. **Analytics Dashboard**
```
Performance Overview:
KPI Cards → Charts Section → Detailed Reports
- Total reach        - Engagement trends  - Campaign reports
- Engagement rate    - ROI analysis       - Influencer reports
- Conversion rate    - Platform breakdown - Custom reports
- Total impressions  - Time-based metrics - Export options
```

##### 5. **Content Management**
```
Content Library:
Content Grid → Preview Panel → Approval Workflow
- Content thumbnails  - Full preview       - Approval status
- Filter by status    - Metadata display   - Review comments
- Search content      - Edit capabilities  - Version history
- Bulk actions        - Download options   - Publishing queue
```

#### Store Application Screens

##### 1. **Store Homepage**
```
Hero Section → Featured Products → Categories → Footer
- Main banner        - Product carousel   - Category grid     - Links and info
- Call-to-action     - Quick view         - Visual navigation - Newsletter signup
- Search bar         - Add to cart        - Browse all        - Social links
- Navigation menu    - Wishlist option    - Special offers    - Support links
```

##### 2. **Product Catalog**
```
Filter Sidebar → Product Grid → Pagination
- Category filters   - Product cards      - Page navigation
- Price range        - Product images     - Items per page
- Brand filters      - Quick actions      - Sort options
- Rating filter      - Price display      - View modes
- Availability       - Add to cart        - Total count
```

##### 3. **Product Detail Page**
```
Product Gallery → Product Info → Related Products
- Image carousel     - Product title      - Recommendations
- Zoom functionality - Price and offers   - Recently viewed
- Video support      - Variant selection  - Similar products
- 360° view          - Add to cart        - Cross-sell items
```

##### 4. **Shopping Cart**
```
Cart Items → Order Summary → Checkout Actions
- Product list       - Subtotal          - Continue shopping
- Quantity controls  - Tax calculation   - Proceed to checkout
- Remove items       - Shipping estimate - Save for later
- Update cart        - Total amount      - Apply coupon
```

##### 5. **Checkout Process**
```
Shipping Info → Payment Method → Order Review → Confirmation
- Address form       - Payment options   - Order summary     - Order confirmation
- Delivery options   - Billing address   - Terms acceptance  - Tracking info
- Special requests   - Save payment      - Final review      - Receipt download
```

##### 6. **Admin Dashboard**
```
Analytics Overview → Quick Actions → Management Tools
- Sales metrics      - Add product       - Product management
- Order summary      - Process orders    - Order management
- Customer stats     - Customer support  - Customer management
- Inventory alerts   - Reports access    - Settings panel
```

---

## 🛠️ Technology Stack

### Backend Services
- **Runtime**: Node.js 20 LTS
- **Framework**: Express.js with TypeScript
- **API**: RESTful APIs + GraphQL for complex queries
- **Authentication**: JWT + Passport.js
- **Validation**: Joi/Yup for request validation
- **Documentation**: Swagger/OpenAPI 3.0

### Databases
- **Primary**: PostgreSQL 15 (ACID compliance)
- **Document**: MongoDB 7.0 (flexible schemas)
- **Cache**: Redis 7.0 (session, caching)
- **Search**: Elasticsearch 8.0 (product search)
- **Analytics**: ClickHouse (time-series data)

### Frontend Applications
- **Framework**: React 18 with TypeScript
- **Routing**: React Router v6
- **State Management**: Redux Toolkit + RTK Query
- **UI Library**: Tailwind CSS + Headless UI
- **Forms**: React Hook Form + Yup validation
- **Charts**: Recharts/Chart.js
- **Testing**: Jest + React Testing Library

### Infrastructure
- **Containerization**: Docker + Docker Compose
- **Orchestration**: Kubernetes
- **API Gateway**: Kong/Ambassador
- **Message Queue**: Redis + Bull Queue
- **File Storage**: AWS S3 + CloudFront CDN
- **Monitoring**: Prometheus + Grafana
- **Logging**: ELK Stack (Elasticsearch, Logstash, Kibana)

### DevOps & CI/CD
- **Version Control**: Git + GitHub
- **CI/CD**: GitHub Actions
- **Code Quality**: ESLint + Prettier + Husky
- **Testing**: Jest + Supertest + Cypress
- **Security**: Snyk + SonarQube
- **Deployment**: Helm charts for Kubernetes

---

## 📋 Detailed Feature Specifications

### CRM Features

#### Core CRM Functionality

##### 1. **Account Management**
- **Create/Edit Accounts**: Company information, industry, size
- **Account Hierarchy**: Parent-child company relationships
- **Custom Fields**: Configurable additional fields
- **Account Scoring**: Lead scoring and account prioritization
- **Account History**: Complete interaction timeline
- **Territory Management**: Geographic/industry-based territories

##### 2. **Contact Management**
- **Contact Profiles**: Personal and professional information
- **Relationship Mapping**: Contact relationships within accounts
- **Communication Preferences**: Email, phone, social preferences
- **Contact Scoring**: Individual contact scoring
- **Social Integration**: LinkedIn, Twitter profile linking
- **Duplicate Detection**: Automated duplicate prevention

##### 3. **Deal/Opportunity Management**
- **Pipeline Management**: Customizable sales stages
- **Deal Tracking**: Amount, probability, close date
- **Product Association**: Link products to opportunities
- **Competitor Tracking**: Competitive analysis
- **Deal Forecasting**: Revenue forecasting
- **Win/Loss Analysis**: Post-deal analysis

##### 4. **Activity Management**
- **Task Management**: To-dos, reminders, assignments
- **Event Scheduling**: Meetings, calls, appointments
- **Email Integration**: Email tracking and templates
- **Call Logging**: Call notes and outcomes
- **Document Management**: Proposal and contract tracking
- **Activity Automation**: Workflow-based task creation

#### HR Management Features

##### 1. **Employee Management**
- **Employee Profiles**: Personal, professional, emergency contacts
- **Organizational Chart**: Visual org structure
- **Department Management**: Department hierarchy
- **Role and Permission Management**: Fine-grained access control
- **Employee Onboarding**: Digital onboarding workflows
- **Offboarding Process**: Exit interview and asset recovery

##### 2. **Attendance and Time Tracking**
- **Clock In/Out**: Web and mobile clock functionality
- **Geolocation Tracking**: Location-based attendance
- **Break Management**: Break time tracking
- **Overtime Calculation**: Automatic overtime computation
- **Shift Management**: Flexible shift scheduling
- **Attendance Reports**: Detailed attendance analytics

##### 3. **Leave Management**
- **Leave Request**: Employee leave applications
- **Approval Workflow**: Multi-level approval process
- **Leave Balance**: Automatic balance calculation
- **Leave Types**: Vacation, sick, personal, etc.
- **Holiday Calendar**: Company holiday management
- **Leave Analytics**: Leave pattern analysis

##### 4. **Performance Management**
- **Goal Setting**: SMART goal configuration
- **Performance Reviews**: Periodic review cycles
- **360-Degree Feedback**: Multi-source feedback
- **Performance Analytics**: Performance trend analysis
- **Development Plans**: Career development tracking
- **Skill Assessment**: Competency evaluation

#### Advanced CRM Features

##### 1. **Automation and Workflows**
- **Lead Assignment**: Automatic lead distribution
- **Email Automation**: Drip campaigns and sequences
- **Task Automation**: Rule-based task creation
- **Notification Automation**: Smart alert system
- **Data Validation**: Automatic data quality checks
- **Approval Workflows**: Multi-step approval processes

##### 2. **Analytics and Reporting**
- **Sales Dashboard**: Real-time sales metrics
- **Custom Reports**: Drag-and-drop report builder
- **Performance Analytics**: Team and individual metrics
- **Forecasting**: AI-powered sales forecasting
- **ROI Analysis**: Marketing and sales ROI tracking
- **Behavioral Analytics**: User activity patterns

##### 3. **Integration and API**
- **Email Integration**: Gmail, Outlook synchronization
- **Calendar Integration**: Calendar sync and scheduling
- **VoIP Integration**: Call center integration
- **Marketing Automation**: HubSpot, Mailchimp integration
- **Accounting Integration**: QuickBooks, Xero integration
- **Social Media Integration**: Social selling tools

### IMX (Influencer Marketing) Features

#### Core IMX Functionality

##### 1. **Campaign Management**
- **Campaign Creation**: Objective-based campaign setup
- **Target Audience**: Demographic and psychographic targeting
- **Budget Management**: Campaign budget allocation
- **Timeline Management**: Campaign scheduling and milestones
- **Content Guidelines**: Brand guidelines and requirements
- **Approval Workflows**: Content approval processes

##### 2. **Influencer Discovery and Management**
- **Influencer Database**: Comprehensive influencer profiles
- **AI-Powered Matching**: Algorithm-based influencer suggestions
- **Performance Analytics**: Historical performance data
- **Audience Analysis**: Influencer audience demographics
- **Content Portfolio**: Previous work and content samples
- **Rate Management**: Pricing and negotiation tools

##### 3. **Content Management**
- **Content Calendar**: Campaign content scheduling
- **Content Creation Tools**: Basic editing capabilities
- **Content Approval**: Review and approval workflows
- **Content Library**: Asset management and storage
- **Performance Tracking**: Content engagement metrics
- **Rights Management**: Usage rights and licensing

##### 4. **Payment and Contract Management**
- **Contract Generation**: Automated contract creation
- **Payment Processing**: Automated influencer payments
- **Tax Management**: 1099 generation and tax compliance
- **Milestone Payments**: Performance-based payments
- **Dispute Resolution**: Payment dispute handling
- **Financial Reporting**: Payment and expense tracking

#### Advanced IMX Features

##### 1. **AI and Machine Learning**
- **Influencer Matching**: AI-powered influencer recommendations
- **Fraud Detection**: Fake follower and engagement detection
- **Content Analysis**: Automated content quality assessment
- **Trend Analysis**: Social media trend identification
- **Predictive Analytics**: Campaign performance prediction
- **Sentiment Analysis**: Brand mention sentiment tracking

##### 2. **Multi-Platform Integration**
- **Instagram Integration**: Direct Instagram API integration
- **TikTok Integration**: TikTok for Business API
- **YouTube Integration**: YouTube Creator API
- **Twitter Integration**: Twitter API v2
- **Snapchat Integration**: Snapchat Marketing API
- **Platform Analytics**: Cross-platform performance tracking

##### 3. **Advanced Analytics**
- **ROI Tracking**: Campaign return on investment
- **Attribution Modeling**: Multi-touch attribution
- **Audience Overlap**: Cross-campaign audience analysis
- **Competitive Analysis**: Competitor campaign tracking
- **Brand Lift Studies**: Brand awareness measurement
- **Custom Dashboards**: Personalized analytics dashboards

### Store (E-commerce) Features

#### Core E-commerce Functionality

##### 1. **Product Management**
- **Product Catalog**: Hierarchical product organization
- **Product Variants**: Size, color, style variations
- **Inventory Management**: Real-time stock tracking
- **Price Management**: Dynamic pricing and promotions
- **Product Images**: Multi-image product galleries
- **Product Videos**: Video content integration

##### 2. **Shopping Experience**
- **Product Search**: Advanced search with filters
- **Product Recommendations**: AI-powered suggestions
- **Shopping Cart**: Persistent cart functionality
- **Wishlist**: Save for later functionality
- **Product Comparison**: Side-by-side comparisons
- **Quick View**: Modal product previews

##### 3. **Checkout and Payments**
- **Guest Checkout**: No-registration checkout option
- **Multiple Payment Methods**: Credit card, PayPal, digital wallets
- **Address Management**: Shipping and billing addresses
- **Shipping Options**: Multiple delivery options
- **Tax Calculation**: Automated tax computation
- **Order Confirmation**: Email and SMS confirmations

##### 4. **Order Management**
- **Order Processing**: Automated order workflows
- **Order Tracking**: Real-time order status updates
- **Inventory Updates**: Automatic stock adjustments
- **Shipping Integration**: Carrier API integration
- **Return Management**: Return and refund processing
- **Customer Notifications**: Order status notifications

#### Multi-Vendor Marketplace Features

##### 1. **Vendor Management**
- **Vendor Onboarding**: Vendor registration and verification
- **Vendor Dashboard**: Dedicated vendor portal
- **Product Approval**: Admin review of vendor products
- **Commission Management**: Automated commission calculation
- **Payout Processing**: Automated vendor payouts
- **Performance Analytics**: Vendor performance metrics

##### 2. **Marketplace Features**
- **Vendor Search**: Find products by vendor
- **Vendor Ratings**: Customer vendor reviews
- **Vendor Communication**: Direct messaging system
- **Bulk Operations**: Vendor bulk product management
- **Vendor Promotions**: Vendor-specific promotions
- **Dispute Resolution**: Vendor-customer dispute handling

#### Advanced E-commerce Features

##### 1. **SEO and Marketing**
- **SEO Optimization**: Meta tags, URLs, sitemaps
- **Social Commerce**: Social media integration
- **Email Marketing**: Abandoned cart emails
- **Loyalty Programs**: Customer loyalty rewards
- **Affiliate Marketing**: Affiliate program management
- **Influencer Integration**: IMX platform integration

##### 2. **Analytics and Intelligence**
- **Sales Analytics**: Revenue and sales metrics
- **Customer Analytics**: Customer behavior analysis
- **Product Analytics**: Product performance metrics
- **Conversion Optimization**: A/B testing tools
- **Personalization**: AI-powered personalization
- **Predictive Analytics**: Demand forecasting

---

## 🔄 Data Integration and APIs

### Inter-Service Communication

#### 1. **Event-Driven Architecture**
```typescript
// Event types for cross-service communication
interface CustomerCreatedEvent {
  type: 'customer.created';
  customerId: string;
  customerData: {
    name: string;
    email: string;
    accountId?: string;
  };
  timestamp: Date;
}

interface OrderPlacedEvent {
  type: 'order.placed';
  orderId: string;
  customerId: string;
  orderData: {
    total: number;
    items: OrderItem[];
    vendorId?: string;
  };
  timestamp: Date;
}
```

#### 2. **API Specifications**

##### Authentication Service API
```yaml
paths:
  /auth/login:
    post:
      summary: User login
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                email:
                  type: string
                password:
                  type: string
                mfaCode:
                  type: string
      responses:
        '200':
          description: Login successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  token:
                    type: string
                  refreshToken:
                    type: string
                  user:
                    $ref: '#/components/schemas/User'
```

##### CRM Service API
```yaml
paths:
  /crm/accounts:
    get:
      summary: List accounts
      parameters:
        - name: page
          in: query
          schema:
            type: integer
        - name: limit
          in: query
          schema:
            type: integer
        - name: search
          in: query
          schema:
            type: string
      responses:
        '200':
          description: Account list
          content:
            application/json:
              schema:
                type: object
                properties:
                  accounts:
                    type: array
                    items:
                      $ref: '#/components/schemas/Account'
                  pagination:
                    $ref: '#/components/schemas/Pagination'
```

#### 3. **Database Schema Design**

##### User Management Schema
```sql
-- Users table (shared across all services)
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url TEXT,
    is_active BOOLEAN DEFAULT true,
    email_verified BOOLEAN DEFAULT false,
    last_login_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Roles and permissions
CREATE TABLE roles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    permissions JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    user_id UUID REFERENCES users(id),
    role_id UUID REFERENCES roles(id),
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id)
);
```

##### CRM Schema
```sql
-- Accounts
CREATE TABLE crm_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    industry VARCHAR(100),
    website VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    address JSONB,
    parent_account_id UUID REFERENCES crm_accounts(id),
    owner_id UUID REFERENCES users(id),
    account_type VARCHAR(50),
    annual_revenue DECIMAL(15,2),
    employee_count INTEGER,
    custom_fields JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contacts
CREATE TABLE crm_contacts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    account_id UUID REFERENCES crm_accounts(id),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(50),
    title VARCHAR(100),
    department VARCHAR(100),
    social_profiles JSONB,
    custom_fields JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Deals/Opportunities
CREATE TABLE crm_deals (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    account_id UUID REFERENCES crm_accounts(id),
    contact_id UUID REFERENCES crm_contacts(id),
    owner_id UUID REFERENCES users(id),
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2),
    probability INTEGER CHECK (probability >= 0 AND probability <= 100),
    stage VARCHAR(100),
    close_date DATE,
    description TEXT,
    competitors JSONB,
    custom_fields JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

##### Store Schema
```sql
-- Products
CREATE TABLE store_products (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    vendor_id UUID REFERENCES store_vendors(id),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    short_description TEXT,
    sku VARCHAR(100) UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    compare_price DECIMAL(10,2),
    cost_price DECIMAL(10,2),
    track_inventory BOOLEAN DEFAULT true,
    inventory_quantity INTEGER DEFAULT 0,
    weight DECIMAL(8,2),
    requires_shipping BOOLEAN DEFAULT true,
    is_active BOOLEAN DEFAULT true,
    seo_title VARCHAR(255),
    seo_description TEXT,
    custom_fields JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders
CREATE TABLE store_orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id UUID REFERENCES users(id),
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    financial_status VARCHAR(50) DEFAULT 'pending',
    fulfillment_status VARCHAR(50) DEFAULT 'unfulfilled',
    currency VARCHAR(3) DEFAULT 'USD',
    subtotal_price DECIMAL(10,2),
    total_tax DECIMAL(10,2),
    total_shipping DECIMAL(10,2),
    total_price DECIMAL(10,2),
    billing_address JSONB,
    shipping_address JSONB,
    payment_method VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔐 Security Requirements

### Authentication and Authorization

#### 1. **Multi-Factor Authentication (MFA)**
- **TOTP Support**: Time-based one-time passwords
- **SMS Verification**: SMS-based second factor
- **Email Verification**: Email-based verification
- **Backup Codes**: Recovery codes for account access
- **App-based MFA**: Support for authenticator apps

#### 2. **Role-Based Access Control (RBAC)**
```typescript
interface Permission {
  resource: string;
  action: string;
  conditions?: Record<string, any>;
}

interface Role {
  id: string;
  name: string;
  permissions: Permission[];
  inherits?: string[]; // Role inheritance
}

// Example roles
const roles = {
  admin: {
    permissions: [
      { resource: '*', action: '*' }
    ]
  },
  sales_manager: {
    permissions: [
      { resource: 'accounts', action: 'read' },
      { resource: 'accounts', action: 'write', conditions: { owner: '$user.id' } },
      { resource: 'deals', action: '*' }
    ]
  },
  sales_rep: {
    permissions: [
      { resource: 'accounts', action: 'read' },
      { resource: 'deals', action: 'read' },
      { resource: 'deals', action: 'write', conditions: { owner: '$user.id' } }
    ]
  }
};
```

#### 3. **API Security**
- **JWT Tokens**: Secure token-based authentication
- **Rate Limiting**: API rate limiting per user/IP
- **Input Validation**: Comprehensive input sanitization
- **SQL Injection Protection**: Parameterized queries
- **XSS Protection**: Content Security Policy headers
- **CSRF Protection**: CSRF token validation

### Data Protection

#### 1. **Data Encryption**
- **At Rest**: Database encryption (TDE)
- **In Transit**: TLS 1.3 for all communications
- **Field Level**: Sensitive field encryption (PII)
- **Key Management**: AWS KMS/HashiCorp Vault
- **Backup Encryption**: Encrypted backup storage

#### 2. **Privacy Compliance**
- **GDPR Compliance**: EU data protection compliance
- **CCPA Compliance**: California privacy compliance
- **Data Retention**: Configurable data retention policies
- **Right to Deletion**: User data deletion capabilities
- **Data Portability**: User data export functionality

#### 3. **Audit and Monitoring**
- **Access Logging**: All access attempts logged
- **Change Tracking**: Data modification audit trails
- **Security Monitoring**: Real-time security alerting
- **Compliance Reporting**: Automated compliance reports
- **Incident Response**: Security incident procedures

---

## 📈 Performance and Scalability

### Performance Requirements

#### 1. **Response Time Targets**
- **API Responses**: < 200ms for 95th percentile
- **Page Load Times**: < 2 seconds for initial load
- **Search Results**: < 500ms for product search
- **Dashboard Loads**: < 1 second for dashboard rendering
- **Report Generation**: < 5 seconds for standard reports

#### 2. **Throughput Requirements**
- **Concurrent Users**: Support 10,000+ concurrent users
- **API Requests**: Handle 100,000+ requests per minute
- **Database Operations**: 50,000+ operations per second
- **File Uploads**: Support 1000+ concurrent uploads
- **Real-time Updates**: Handle 10,000+ WebSocket connections

#### 3. **Availability Requirements**
- **Uptime**: 99.9% availability (8.77 hours downtime/year)
- **Recovery Time**: < 15 minutes for service recovery
- **Data Backup**: Daily automated backups
- **Disaster Recovery**: 4-hour RTO, 1-hour RPO
- **Geographic Distribution**: Multi-region deployment

### Scalability Architecture

#### 1. **Horizontal Scaling**
```yaml
# Kubernetes deployment example
apiVersion: apps/v1
kind: Deployment
metadata:
  name: crm-core-service
spec:
  replicas: 3
  selector:
    matchLabels:
      app: crm-core-service
  template:
    metadata:
      labels:
        app: crm-core-service
    spec:
      containers:
      - name: crm-core
        image: crm-core:latest
        ports:
        - containerPort: 3020
        env:
        - name: DATABASE_URL
          valueFrom:
            secretKeyRef:
              name: database-secret
              key: url
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
---
apiVersion: v1
kind: Service
metadata:
  name: crm-core-service
spec:
  selector:
    app: crm-core-service
  ports:
  - port: 80
    targetPort: 3020
  type: LoadBalancer
---
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: crm-core-hpa
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: crm-core-service
  minReplicas: 3
  maxReplicas: 20
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 80
```

#### 2. **Database Scaling**
- **Read Replicas**: Multiple read-only database replicas
- **Connection Pooling**: Database connection optimization
- **Query Optimization**: Automated query performance monitoring
- **Caching Strategy**: Multi-level caching (Redis, CDN)
- **Data Partitioning**: Horizontal database partitioning

#### 3. **Caching Strategy**
```typescript
// Multi-level caching implementation
class CacheManager {
  private redisClient: RedisClient;
  private memoryCache: Map<string, CacheItem>;

  async get<T>(key: string): Promise<T | null> {
    // 1. Check memory cache first
    const memoryResult = this.memoryCache.get(key);
    if (memoryResult && !this.isExpired(memoryResult)) {
      return memoryResult.value;
    }

    // 2. Check Redis cache
    const redisResult = await this.redisClient.get(key);
    if (redisResult) {
      const parsed = JSON.parse(redisResult);
      // Update memory cache
      this.memoryCache.set(key, {
        value: parsed,
        expiry: Date.now() + 60000 // 1 minute
      });
      return parsed;
    }

    return null;
  }

  async set<T>(key: string, value: T, ttl: number = 3600): Promise<void> {
    // Set in both caches
    this.memoryCache.set(key, {
      value,
      expiry: Date.now() + Math.min(ttl, 300) * 1000 // Max 5 minutes in memory
    });

    await this.redisClient.setex(key, ttl, JSON.stringify(value));
  }
}
```

---

## 👥 Team Structure and Roles

### Development Team Organization

#### 1. **Team Structure**
```
Technical Leadership
├── Tech Lead (1)
│   ├── Backend Team (4-5 developers)
│   │   ├── Senior Backend Developer (2)
│   │   └── Mid-level Backend Developer (2-3)
│   ├── Frontend Team (3-4 developers)
│   │   ├── Senior Frontend Developer (2)
│   │   └── Mid-level Frontend Developer (1-2)
│   ├── DevOps Team (2 engineers)
│   │   ├── Senior DevOps Engineer (1)
│   │   └── DevOps Engineer (1)
│   └── QA Team (2-3 testers)
│       ├── Senior QA Engineer (1)
│       └── QA Engineer (1-2)

Product & Design
├── Product Manager (1)
├── UX/UI Designer (2)
└── Technical Writer (1)
```

#### 2. **Role Responsibilities**

##### **Tech Lead**
- Overall technical architecture decisions
- Code review and quality standards
- Team coordination and mentoring
- Technical roadmap planning
- Cross-team communication

##### **Senior Backend Developer**
- Microservice architecture design
- Database schema design
- API design and implementation
- Performance optimization
- Junior developer mentoring

##### **Senior Frontend Developer**
- UI/UX implementation
- Component library development
- State management architecture
- Performance optimization
- Code review and standards

##### **DevOps Engineer**
- CI/CD pipeline setup
- Infrastructure automation
- Monitoring and alerting
- Security implementation
- Deployment strategies

##### **Product Manager**
- Feature requirement gathering
- User story creation
- Sprint planning
- Stakeholder communication
- Product roadmap management

##### **UX/UI Designer**
- User experience design
- Interface design
- Design system creation
- User research
- Prototype development

### Development Methodology

#### 1. **Agile/Scrum Process**
- **Sprint Duration**: 2-week sprints
- **Sprint Planning**: Sprint planning sessions
- **Daily Standups**: 15-minute daily updates
- **Sprint Reviews**: Demo and feedback sessions
- **Retrospectives**: Process improvement meetings

#### 2. **Code Quality Standards**
- **Code Reviews**: Mandatory peer reviews
- **Testing**: 80% minimum code coverage
- **Documentation**: API and code documentation
- **Style Guide**: Consistent coding standards
- **Static Analysis**: Automated code quality checks

---

## 🗓️ Development Roadmap

### Phase 1: Foundation (Months 1-3)

#### Month 1: Infrastructure Setup
**Week 1-2: Project Setup**
- [x] Repository structure
- [x] Development environment setup
- [x] CI/CD pipeline configuration
- [x] Kubernetes cluster setup
- [x] Database setup (PostgreSQL, MongoDB, Redis)

**Week 3-4: Core Services**
- [ ] Authentication service development
- [ ] User management service
- [ ] API Gateway setup
- [ ] Basic monitoring and logging

#### Month 2: Authentication & User Management
**Week 1-2: Authentication System**
- [ ] JWT authentication implementation
- [ ] Multi-factor authentication
- [ ] Role-based access control
- [ ] Session management
- [ ] Password policies

**Week 3-4: User Management**
- [ ] User profile management
- [ ] User preferences
- [ ] Avatar/image handling
- [ ] Account verification
- [ ] Basic user analytics

#### Month 3: Shared Services
**Week 1-2: Notification Service**
- [ ] Email notification system
- [ ] SMS integration
- [ ] Push notification setup
- [ ] Template management
- [ ] Delivery tracking

**Week 3-4: File Management**
- [ ] File upload/download
- [ ] Image processing
- [ ] CDN integration
- [ ] File versioning
- [ ] Access control

### Phase 2: CRM Development (Months 4-6)

#### Month 4: CRM Core Features
**Week 1-2: Account Management**
- [ ] Account CRUD operations
- [ ] Account hierarchy
- [ ] Custom fields
- [ ] Account search and filtering
- [ ] Account analytics

**Week 3-4: Contact Management**
- [ ] Contact CRUD operations
- [ ] Contact-account relationships
- [ ] Contact communication tracking
- [ ] Contact scoring
- [ ] Duplicate detection

#### Month 5: Deal & Activity Management
**Week 1-2: Deal Pipeline**
- [ ] Deal CRUD operations
- [ ] Pipeline stage management
- [ ] Deal analytics
- [ ] Forecasting
- [ ] Win/loss tracking

**Week 3-4: Activity Management**
- [ ] Task management
- [ ] Event scheduling
- [ ] Email integration
- [ ] Call logging
- [ ] Activity automation

#### Month 6: HR Features
**Week 1-2: Employee Management**
- [ ] Employee profiles
- [ ] Organizational structure
- [ ] Department management
- [ ] Role assignments
- [ ] Onboarding workflows

**Week 3-4: Attendance & Performance**
- [ ] Attendance tracking
- [ ] Leave management
- [ ] Performance reviews
- [ ] Reporting system
- [ ] Analytics dashboard

### Phase 3: IMX Development (Months 7-9)

#### Month 7: Campaign Management
**Week 1-2: Campaign Core**
- [ ] Campaign CRUD operations
- [ ] Campaign targeting
- [ ] Budget management
- [ ] Timeline management
- [ ] Campaign analytics

**Week 3-4: Influencer Management**
- [ ] Influencer profiles
- [ ] Influencer discovery
- [ ] Performance tracking
- [ ] Rating system
- [ ] Communication tools

#### Month 8: Content & Social Integration
**Week 1-2: Content Management**
- [ ] Content upload/storage
- [ ] Content approval workflows
- [ ] Content calendar
- [ ] Performance tracking
- [ ] Rights management

**Week 3-4: Social Platform Integration**
- [ ] Instagram API integration
- [ ] TikTok API integration
- [ ] YouTube API integration
- [ ] Twitter API integration
- [ ] Data synchronization

#### Month 9: Advanced Features
**Week 1-2: Analytics & AI**
- [ ] Advanced analytics dashboard
- [ ] AI-powered matching
- [ ] Fraud detection
- [ ] Trend analysis
- [ ] Predictive analytics

**Week 3-4: Payment & Contracts**
- [ ] Contract generation
- [ ] Payment processing
- [ ] Tax management
- [ ] Dispute resolution
- [ ] Financial reporting

### Phase 4: Store Development (Months 10-12)

#### Month 10: Product & Catalog
**Week 1-2: Product Management**
- [ ] Product CRUD operations
- [ ] Product variants
- [ ] Inventory management
- [ ] Price management
- [ ] Product search

**Week 3-4: Catalog Features**
- [ ] Category management
- [ ] Product recommendations
- [ ] Advanced search
- [ ] Product comparison
- [ ] SEO optimization

#### Month 11: Shopping & Checkout
**Week 1-2: Shopping Experience**
- [ ] Shopping cart
- [ ] Wishlist functionality
- [ ] Quick view
- [ ] Product reviews
- [ ] User recommendations

**Week 3-4: Checkout & Payments**
- [ ] Checkout process
- [ ] Multiple payment methods
- [ ] Address management
- [ ] Shipping options
- [ ] Tax calculation

#### Month 12: Multi-vendor & Advanced Features
**Week 1-2: Multi-vendor Marketplace**
- [ ] Vendor onboarding
- [ ] Vendor dashboard
- [ ] Commission management
- [ ] Payout processing
- [ ] Vendor analytics

**Week 3-4: Advanced E-commerce**
- [ ] Order management
- [ ] Shipping integration
- [ ] Return management
- [ ] Loyalty programs
- [ ] Advanced analytics

### Phase 5: Integration & Testing (Months 13-15)

#### Month 13: Cross-Platform Integration
**Week 1-2: Data Integration**
- [ ] Customer data synchronization
- [ ] Order-CRM integration
- [ ] IMX-Store integration
- [ ] Analytics integration
- [ ] Event-driven updates

**Week 3-4: Advanced Features**
- [ ] Unified search
- [ ] Cross-platform analytics
- [ ] Automated workflows
- [ ] Advanced reporting
- [ ] Performance optimization

#### Month 14: Testing & Quality Assurance
**Week 1-2: Comprehensive Testing**
- [ ] Unit testing completion
- [ ] Integration testing
- [ ] End-to-end testing
- [ ] Performance testing
- [ ] Security testing

**Week 3-4: User Acceptance Testing**
- [ ] UAT environment setup
- [ ] User training
- [ ] Feedback collection
- [ ] Bug fixes
- [ ] Performance tuning

#### Month 15: Pre-production & Launch
**Week 1-2: Production Preparation**
- [ ] Production environment setup
- [ ] Data migration
- [ ] Security audit
- [ ] Performance optimization
- [ ] Monitoring setup

**Week 3-4: Launch Preparation**
- [ ] Deployment automation
- [ ] Rollback procedures
- [ ] User documentation
- [ ] Support procedures
- [ ] Go-live preparation

### Phase 6: Launch & Optimization (Month 16+)

#### Post-Launch Activities
- [ ] Production monitoring
- [ ] Performance optimization
- [ ] User feedback collection
- [ ] Bug fixes and improvements
- [ ] Feature enhancements
- [ ] Scaling adjustments

---

## 📊 Success Metrics and KPIs

### Technical Metrics

#### 1. **Performance Metrics**
- **Response Time**: 95th percentile < 200ms
- **Uptime**: > 99.9% availability
- **Error Rate**: < 0.1% error rate
- **Throughput**: > 100,000 requests/minute
- **Database Performance**: < 50ms average query time

#### 2. **Code Quality Metrics**
- **Test Coverage**: > 80% code coverage
- **Code Duplication**: < 5% duplicate code
- **Cyclomatic Complexity**: < 10 average complexity
- **Technical Debt**: < 10% technical debt ratio
- **Security Vulnerabilities**: 0 high/critical vulnerabilities

#### 3. **DevOps Metrics**
- **Deployment Frequency**: Daily deployments
- **Lead Time**: < 2 hours from commit to production
- **MTTR**: < 15 minutes mean time to recovery
- **Change Failure Rate**: < 5% failed deployments
- **Monitoring Coverage**: 100% service monitoring

### Business Metrics

#### 1. **User Experience Metrics**
- **User Satisfaction**: > 4.5/5 user rating
- **Task Completion Rate**: > 95% task success rate
- **Time to Complete Tasks**: 50% reduction vs. current
- **User Adoption**: > 90% user adoption rate
- **Support Tickets**: < 2% users requiring support

#### 2. **CRM Metrics**
- **Sales Productivity**: 30% increase in sales activities
- **Deal Conversion**: 20% improvement in conversion rate
- **User Adoption**: > 95% daily active users
- **Data Quality**: 99% data accuracy
- **Report Generation**: < 5 seconds average time

#### 3. **IMX Metrics**
- **Campaign Efficiency**: 40% faster campaign setup
- **Influencer Matching**: 90% successful matches
- **ROI Tracking**: 100% campaign ROI visibility
- **Platform Integration**: Real-time data sync
- **Payment Processing**: < 24 hours payment processing

#### 4. **Store Metrics**
- **Conversion Rate**: 25% improvement in conversion
- **Cart Abandonment**: < 20% cart abandonment rate
- **Search Performance**: < 500ms search response
- **Mobile Experience**: 100% mobile-responsive design
- **Vendor Satisfaction**: > 4.0/5 vendor rating

---

## 🚀 Implementation Guidelines

### Development Best Practices

#### 1. **Code Standards**
```typescript
// TypeScript configuration
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "commonjs",
    "lib": ["ES2020"],
    "outDir": "./dist",
    "rootDir": "./src",
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "forceConsistentCasingInFileNames": true,
    "resolveJsonModule": true,
    "declaration": true,
    "declarationMap": true,
    "sourceMap": true
  },
  "include": ["src/**/*"],
  "exclude": ["node_modules", "dist", "tests"]
}

// ESLint configuration
{
  "extends": [
    "@typescript-eslint/recommended",
    "prettier"
  ],
  "rules": {
    "@typescript-eslint/no-unused-vars": "error",
    "@typescript-eslint/explicit-function-return-type": "warn",
    "prefer-const": "error",
    "no-var": "error"
  }
}
```

#### 2. **API Design Guidelines**
```typescript
// Consistent API response format
interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: {
    code: string;
    message: string;
    details?: any;
  };
  meta?: {
    page?: number;
    limit?: number;
    total?: number;
    hasNext?: boolean;
  };
}

// Error handling middleware
export const errorHandler = (
  error: Error,
  req: Request,
  res: Response,
  next: NextFunction
) => {
  const apiError: ApiResponse<null> = {
    success: false,
    error: {
      code: error.name || 'INTERNAL_ERROR',
      message: error.message || 'An unexpected error occurred'
    }
  };

  res.status(500).json(apiError);
};

// Validation middleware
export const validateRequest = (schema: Joi.Schema) => {
  return (req: Request, res: Response, next: NextFunction) => {
    const { error } = schema.validate(req.body);
    if (error) {
      const apiError: ApiResponse<null> = {
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'Invalid request data',
          details: error.details
        }
      };
      return res.status(400).json(apiError);
    }
    next();
  };
};
```

#### 3. **Database Best Practices**
```typescript
// Database connection pool
import { Pool } from 'pg';

export class DatabaseManager {
  private pool: Pool;

  constructor() {
    this.pool = new Pool({
      host: process.env.DB_HOST,
      port: parseInt(process.env.DB_PORT || '5432'),
      database: process.env.DB_NAME,
      user: process.env.DB_USER,
      password: process.env.DB_PASSWORD,
      max: 20, // Maximum number of connections
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 2000,
    });
  }

  async query<T>(text: string, params?: any[]): Promise<T[]> {
    const client = await this.pool.connect();
    try {
      const result = await client.query(text, params);
      return result.rows;
    } finally {
      client.release();
    }
  }

  async transaction<T>(callback: (client: any) => Promise<T>): Promise<T> {
    const client = await this.pool.connect();
    try {
      await client.query('BEGIN');
      const result = await callback(client);
      await client.query('COMMIT');
      return result;
    } catch (error) {
      await client.query('ROLLBACK');
      throw error;
    } finally {
      client.release();
    }
  }
}
```

### Testing Strategy

#### 1. **Unit Testing**
```typescript
// Jest configuration
export default {
  preset: 'ts-jest',
  testEnvironment: 'node',
  collectCoverageFrom: [
    'src/**/*.{ts,tsx}',
    '!src/**/*.d.ts',
    '!src/types/**/*'
  ],
  coverageThreshold: {
    global: {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    }
  },
  setupFilesAfterEnv: ['<rootDir>/tests/setup.ts']
};

// Example unit test
describe('AccountService', () => {
  let accountService: AccountService;
  let mockRepository: jest.Mocked<AccountRepository>;

  beforeEach(() => {
    mockRepository = {
      findById: jest.fn(),
      save: jest.fn(),
      delete: jest.fn(),
      findAll: jest.fn()
    } as jest.Mocked<AccountRepository>;
    
    accountService = new AccountService(mockRepository);
  });

  describe('createAccount', () => {
    it('should create account successfully', async () => {
      const accountData = {
        name: 'Test Company',
        industry: 'Technology',
        email: 'test@company.com'
      };

      mockRepository.save.mockResolvedValue({
        id: '123',
        ...accountData,
        createdAt: new Date(),
        updatedAt: new Date()
      });

      const result = await accountService.createAccount(accountData);

      expect(result).toBeDefined();
      expect(result.name).toBe(accountData.name);
      expect(mockRepository.save).toHaveBeenCalledWith(
        expect.objectContaining(accountData)
      );
    });
  });
});
```

#### 2. **Integration Testing**
```typescript
// Integration test example
describe('CRM API Integration', () => {
  let app: Express;
  let database: DatabaseManager;

  beforeAll(async () => {
    // Setup test database
    database = new DatabaseManager();
    app = createApp(database);
    await database.migrate();
  });

  afterAll(async () => {
    await database.cleanup();
  });

  beforeEach(async () => {
    await database.seed();
  });

  afterEach(async () => {
    await database.clearData();
  });

  describe('POST /api/accounts', () => {
    it('should create account with valid data', async () => {
      const accountData = {
        name: 'Integration Test Company',
        industry: 'Technology'
      };

      const response = await request(app)
        .post('/api/accounts')
        .set('Authorization', `Bearer ${getTestToken()}`)
        .send(accountData)
        .expect(201);

      expect(response.body.success).toBe(true);
      expect(response.body.data.name).toBe(accountData.name);

      // Verify in database
      const account = await database.query(
        'SELECT * FROM crm_accounts WHERE id = $1',
        [response.body.data.id]
      );
      expect(account[0]).toBeDefined();
    });
  });
});
```

#### 3. **E2E Testing**
```typescript
// Cypress E2E test
describe('CRM User Journey', () => {
  beforeEach(() => {
    cy.login('sales@company.com', 'password');
  });

  it('should complete account creation flow', () => {
    // Navigate to accounts
    cy.visit('/crm/accounts');
    cy.get('[data-testid="create-account-btn"]').click();

    // Fill account form
    cy.get('[data-testid="account-name"]').type('Test Company');
    cy.get('[data-testid="account-industry"]').select('Technology');
    cy.get('[data-testid="account-email"]').type('test@company.com');
    cy.get('[data-testid="account-phone"]').type('+1-555-123-4567');

    // Submit form
    cy.get('[data-testid="save-account-btn"]').click();

    // Verify success
    cy.get('[data-testid="success-message"]').should('be.visible');
    cy.url().should('include', '/crm/accounts/');
    cy.get('[data-testid="account-name-display"]').should('contain', 'Test Company');
  });

  it('should handle account editing', () => {
    // Go to existing account
    cy.visit('/crm/accounts/test-account-id');
    cy.get('[data-testid="edit-account-btn"]').click();

    // Edit account
    cy.get('[data-testid="account-name"]').clear().type('Updated Company Name');
    cy.get('[data-testid="save-account-btn"]').click();

    // Verify update
    cy.get('[data-testid="account-name-display"]').should('contain', 'Updated Company Name');
  });
});
```

### Deployment Strategy

#### 1. **Environment Configuration**
```yaml
# Development environment
apiVersion: v1
kind: ConfigMap
metadata:
  name: app-config-dev
data:
  NODE_ENV: "development"
  LOG_LEVEL: "debug"
  DB_HOST: "postgres-dev"
  REDIS_HOST: "redis-dev"
  API_RATE_LIMIT: "1000"

---
# Production environment
apiVersion: v1
kind: ConfigMap
metadata:
  name: app-config-prod
data:
  NODE_ENV: "production"
  LOG_LEVEL: "info"
  DB_HOST: "postgres-prod"
  REDIS_HOST: "redis-prod"
  API_RATE_LIMIT: "10000"
```

#### 2. **Blue-Green Deployment**
```yaml
# Blue-green deployment script
apiVersion: argoproj.io/v1alpha1
kind: Rollout
metadata:
  name: crm-core-rollout
spec:
  replicas: 5
  strategy:
    blueGreen:
      activeService: crm-core-active
      previewService: crm-core-preview
      autoPromotionEnabled: false
      scaleDownDelaySeconds: 30
      prePromotionAnalysis:
        templates:
        - templateName: health-check
        args:
        - name: service-name
          value: crm-core-preview
      postPromotionAnalysis:
        templates:
        - templateName: performance-test
        args:
        - name: service-name
          value: crm-core-active
  selector:
    matchLabels:
      app: crm-core
  template:
    metadata:
      labels:
        app: crm-core
    spec:
      containers:
      - name: crm-core
        image: crm-core:{{.Values.image.tag}}
        ports:
        - containerPort: 3020
```

---

## 🎉 Conclusion

This comprehensive Product & Technical Requirements Document provides the foundation for rebuilding the CRM, IMX, and Store applications using a modern microservices architecture. The document covers:

### Key Achievements
1. **Preserved Functionality**: All existing features maintained and enhanced
2. **Modern Architecture**: Scalable microservices design
3. **Enhanced UX**: Improved user interface and experience
4. **Integration**: Seamless data flow between applications
5. **Scalability**: Built for growth and performance

### Next Steps
1. **Team Formation**: Assemble development team based on outlined structure
2. **Environment Setup**: Configure development and staging environments
3. **Sprint Planning**: Begin Phase 1 development sprints
4. **Stakeholder Alignment**: Regular review sessions with stakeholders
5. **Progress Tracking**: Monitor development against defined KPIs

### Success Factors
- **Clear Requirements**: Detailed specifications for all features
- **Modern Tech Stack**: Industry-standard technologies and practices
- **Quality Assurance**: Comprehensive testing and monitoring
- **Team Collaboration**: Defined roles and responsibilities
- **Continuous Improvement**: Agile methodology with regular retrospectives

This roadmap provides a clear path to transform the existing applications into a world-class, scalable, and maintainable platform that will serve business needs for years to come.