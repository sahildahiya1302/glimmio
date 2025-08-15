# 🏗️ Unified Application Architecture

A single, scalable, and secure architecture pattern that serves as the foundation for all three applications (CRM, IMX, Store) with common language, structure, and best practices.

## 🎯 Architecture Philosophy

**"One Architecture, Multiple Personalities"** - A unified codebase that can be configured and deployed as different applications while maintaining consistency, security, and scalability.

## 🏗️ Unified Structure

```
unified-ecosystem/
├── core/
│   ├── shared/
│   │   ├── domain/           # Business logic & entities
│   │   ├── infrastructure/   # Database, caching, storage
│   │   ├── application/      # Use cases & services
│   │   └── presentation/     # API & UI patterns
│   ├── modules/
│   │   ├── auth/            # Authentication & authorization
│   │   ├── user/            # User management
│   │   ├── notification/    # Email, SMS, push notifications
│   │   ├── analytics/       # Tracking & reporting
│   │   └── file/            # File upload & management
│   └── config/
│       ├── app.config.ts    # Application-specific configs
│       ├── database.config.ts
│       └── security.config.ts
├── apps/
│   ├── crm/                 # CRM-specific configurations
│   ├── imx/                 # IMX-specific configurations
│   └── store/               # Store-specific configurations
├── packages/
│   ├── ui/                  # Shared UI components
│   ├── utils/               # Shared utilities
│   └── types/               # Shared TypeScript types
└── deployment/
    ├── docker/
    ├── kubernetes/
    └── terraform/
```

## 🔄 Common Language & Patterns

### 1. Domain-Driven Design (DDD)
```typescript
// core/shared/domain/entities/base.entity.ts
export abstract class BaseEntity {
  readonly id: string;
  readonly createdAt: Date;
  readonly updatedAt: Date;
  
  constructor(props: BaseEntityProps) {
    this.id = props.id || generateId();
    this.createdAt = props.createdAt || new Date();
    this.updatedAt = props.updatedAt || new Date();
  }
}

// core/shared/domain/value-objects/money.vo.ts
export class Money {
  private readonly amount: number;
  private readonly currency: string;
  
  constructor(amount: number, currency: string = 'USD') {
    this.amount = amount;
    this.currency = currency;
  }
  
  add(other: Money): Money {
    if (this.currency !== other.currency) {
      throw new Error('Cannot add money with different currencies');
    }
    return new Money(this.amount + other.amount, this.currency);
  }
}
```

### 2. Clean Architecture Layers
```typescript
// core/shared/application/use-cases/base.use-case.ts
export abstract class BaseUseCase<TRequest, TResponse> {
  abstract execute(request: TRequest): Promise<TResponse>;
  
  protected validateRequest(request: TRequest): void {
    // Common validation logic
  }
}

// core/shared/infrastructure/repositories/base.repository.ts
export abstract class BaseRepository<T extends BaseEntity> {
  abstract findById(id: string): Promise<T | null>;
  abstract findAll(filters?: any): Promise<T[]>;
  abstract save(entity: T): Promise<T>;
  abstract delete(id: string): Promise<void>;
}
```

### 3. CQRS Pattern (Command Query Responsibility Segregation)
```typescript
// core/shared/application/commands/base.command.ts
export abstract class Command<TPayload, TResult> {
  abstract execute(payload: TPayload): Promise<TResult>;
}

// core/shared/application/queries/base.query.ts
export abstract class Query<TResult> {
  abstract execute(): Promise<TResult>;
}
```

## 🔐 Security Architecture

### 1. Authentication & Authorization
```typescript
// core/modules/auth/domain/entities/user.entity.ts
export class User extends BaseEntity {
  private email: Email;
  private password: Password;
  private roles: Role[];
  private permissions: Permission[];
  
  constructor(props: UserProps) {
    super(props);
    this.email = new Email(props.email);
    this.password = new Password(props.password);
    this.roles = props.roles || [];
    this.permissions = props.permissions || [];
  }
  
  hasPermission(permission: string): boolean {
    return this.permissions.some(p => p.name === permission) ||
           this.roles.some(r => r.hasPermission(permission));
  }
}

// core/modules/auth/application/services/auth.service.ts
export class AuthService {
  async authenticate(credentials: Credentials): Promise<AuthResult> {
    const user = await this.userRepository.findByEmail(credentials.email);
    if (!user || !await user.password.matches(credentials.password)) {
      throw new AuthenticationError('Invalid credentials');
    }
    
    const token = this.jwtService.generateToken({
      userId: user.id,
      roles: user.roles.map(r => r.name)
    });
    
    return { user, token };
  }
}
```

### 2. Security Middleware
```typescript
// core/shared/infrastructure/middleware/security.middleware.ts
export class SecurityMiddleware {
  static rateLimit(options: RateLimitOptions) {
    return rateLimit({
      windowMs: options.windowMs || 15 * 60 * 1000, // 15 minutes
      max: options.max || 100,
      message: 'Too many requests from this IP'
    });
  }
  
  static helmet() {
    return helmet({
      contentSecurityPolicy: {
        directives: {
          defaultSrc: ["'self'"],
          styleSrc: ["'self'", "'unsafe-inline'"],
          scriptSrc: ["'self'"],
          imgSrc: ["'self'", "data:", "https:"]
        }
      }
    });
  }
}
```

## 📊 Application-Specific Configurations

### 1. CRM Configuration
```typescript
// apps/crm/config/app.config.ts
export const CRMConfig = {
  name: 'CRM',
  port: 3001,
  modules: {
    accounts: true,
    contacts: true,
    deals: true,
    activities: true,
    employees: true,
    departments: true,
    attendance: true
  },
  features: {
    automation: true,
    analytics: true,
    integrations: ['email', 'calendar', 'social']
  }
};
```

### 2. IMX Configuration
```typescript
// apps/imx/config/app.config.ts
export const IMXConfig = {
  name: 'IMX',
  port: 3002,
  modules: {
    campaigns: true,
    influencers: true,
    brands: true,
    analytics: true,
    payments: true
  },
  features: {
    aiMatching: true,
    realTimeAnalytics: true,
    multiPlatform: ['instagram', 'tiktok', 'youtube', 'twitter'],
    automatedPayments: true
  }
};
```

### 3. Store Configuration
```typescript
// apps/store/config/app.config.ts
export const StoreConfig = {
  name: 'Store',
  port: 3003,
  modules: {
    products: true,
    cart: true,
    checkout: true,
    orders: true,
    payments: true,
    shipping: true,
    inventory: true
  },
  features: {
    multiVendor: true,
    seoOptimization: true,
    mobileResponsive: true,
    analytics: true,
    reviews: true
  }
};
```

## 🚀 Deployment Strategy

### 1. Environment-Based Deployment
```yaml
# deployment/docker/docker-compose.yml
version: '3.8'
services:
  unified-app:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        - APP_TYPE=${APP_TYPE:-crm}
    environment:
      - NODE_ENV=${NODE_ENV:-production}
      - APP_TYPE=${APP_TYPE:-crm}
    ports:
      - "${PORT:-3000}:${PORT:-3000}"
    depends_on:
      - postgres
      - redis
```

### 2. Kubernetes Deployment
```yaml
# deployment/kubernetes/unified-deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: unified-app
spec:
  replicas: 3
  selector:
    matchLabels:
      app: unified-app
  template:
    metadata:
      labels:
        app: unified-app
    spec:
      containers:
      - name: unified-app
        image: unified-app:latest
        env:
        - name: APP_TYPE
          value: "crm"
        - name: NODE_ENV
          value: "production"
```

## 🧪 Testing Strategy

### 1. Unified Testing Framework
```typescript
// core/shared/testing/base.test.ts
export abstract class BaseTest {
  protected app: Application;
  protected database: TestDatabase;
  
  async setup(): Promise<void> {
    this.database = await TestDatabase.create();
    this.app = await createApp({
      database: this.database.connection,
      environment: 'test'
    });
  }
  
  async teardown(): Promise<void> {
    await this.database.cleanup();
    await this.app.close();
  }
}

// apps/crm/tests/integration/account.test.ts
export class AccountIntegrationTest extends BaseTest {
  async testCreateAccount(): Promise<void> {
    const response = await this.app.request('/api/accounts')
      .post({
        name: 'Test Account',
        email: 'test@example.com'
      });
    
    expect(response.status).toBe(201);
    expect(response.body.name).toBe('Test Account');
  }
}
```

## 📊 Monitoring & Observability

### 1. Unified Logging
```typescript
// core/shared/infrastructure/logging/logger.ts
export class UnifiedLogger {
  private logger: winston.Logger;
  
  constructor(private appName: string) {
    this.logger = winston.createLogger({
      level: 'info',
      format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.json()
      ),
      defaultMeta: { app: appName },
      transports: [
        new winston.transports.Console(),
        new winston.transports.File({ 
          filename: `logs/${appName}-error.log`, 
          level: 'error' 
        }),
        new winston.transports.File({ 
          filename: `logs/${appName}-combined.log` 
        })
      ]
    });
  }
}
```

### 2. Health Checks
```typescript
// core/shared/infrastructure/health/health-check.ts
export class HealthCheck {
  async check(): Promise<HealthStatus> {
    const checks = await Promise.all([
      this.checkDatabase(),
      this.checkRedis(),
      this.checkExternalServices()
    ]);
    
    return {
      status: checks.every(c => c.healthy) ? 'healthy' : 'unhealthy',
      checks: checks
    };
  }
}
```

## 🔄 Migration Strategy

### 1. From Unified to Individual Apps
```bash
# Generate specific app
npm run generate:app --type=crm --name=my-crm

# This creates:
# - app-specific package.json
# - app-specific configuration
# - app-specific routes
# - shared core remains the same
```

### 2. Feature Flags
```typescript
// core/shared/config/feature-flags.ts
export class FeatureFlags {
  static isEnabled(feature: string, appType: string): boolean {
    const config = {
      crm: {
        advancedAnalytics: true,
        automation: true,
        multiCurrency: false
      },
      imx: {
        aiMatching: true,
        realTimeAnalytics: true,
        multiCurrency: true
      },
      store: {
        multiVendor: true,
        advancedAnalytics: true,
        multiCurrency: true
      }
    };
    
    return config[appType]?.[feature] || false;
  }
}
```

## 📈 Performance Optimization

### 1. Caching Strategy
```typescript
// core/shared/infrastructure/cache/cache.service.ts
export class CacheService {
  async get<T>(key: string): Promise<T | null> {
    return await this.redis.get(key);
  }
  
  async set<T>(key: string, value: T, ttl: number = 3600): Promise<void> {
    await this.redis.setex(key, ttl, JSON.stringify(value));
  }
  
  async invalidate(pattern: string): Promise<void> {
    const keys = await this.redis.keys(pattern);
    if (keys.length > 0) {
      await this.redis.del(...keys);
    }
  }
}
```

### 2. Database Optimization
```typescript
// core/shared/infrastructure/database/connection.ts
export class DatabaseConnection {
  static create(appType: string) {
    return new DataSource({
      type: 'postgres',
      host: process.env.DB_HOST,
      port: parseInt(process.env.DB_PORT || '5432'),
      username: process.env.DB_USERNAME,
      password: process.env.DB_PASSWORD,
      database: `unified_${appType}`,
      entities: [`core/shared/domain/entities/**/*.entity.ts`],
      migrations: [`core/shared/infrastructure/database/migrations/**/*.ts`],
      synchronize: process.env.NODE_ENV === 'development',
      logging: process.env.NODE_ENV === 'development',
      cache: {
        type: 'redis',
        options: {
          host: process.env.REDIS_HOST,
          port: parseInt(process.env.REDIS_PORT || '6379')
        }
      }
    });
  }
}
```

This unified architecture provides a single, scalable foundation that can be configured for any of the three applications while maintaining consistency, security, and best practices.
