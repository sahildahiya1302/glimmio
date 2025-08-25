# 🔵 **Phase 1: Multi-Tenant Core Architecture (Foundation Layer)**

---

## 🎯 **Goal:**

Redesign the project to support **multi-tenancy** so that:

* Each merchant gets their own isolated environment
* Stores are identified via **subdomain or custom domain**
* Data is partitioned cleanly via `store_id`
* Sessions, authentication, and routing resolve contextually by store

---

## 🧱 Key Concepts

* **Tenant = Store**
* **Tenant-aware data**: All core data (products, orders, customers) must belong to a specific store
* **Context resolution**: Tenant is resolved via domain, subdomain, or header
* **Middleware + base context** is globally accessible

---

## 🗃️ Step 1: Database Schema Setup

### 🔸 Create `stores` table

```sql
CREATE TABLE stores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  subdomain VARCHAR(255) UNIQUE,
  custom_domain VARCHAR(255) UNIQUE,
  logo_url VARCHAR(255),
  theme_id INT UNSIGNED,
  plan_id INT UNSIGNED,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME,
  updated_at DATETIME
);
```

### 🔸 Create `store_users` table

```sql
CREATE TABLE store_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id INT UNSIGNED,
  user_id INT UNSIGNED,
  role ENUM('owner', 'staff', 'manager') DEFAULT 'staff',
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 🔸 Add `store_id` to tenant-bound tables:

* `products`, `product_variants`, `collections`, `orders`, `customers`, `carts`, `discounts`, `themes`, etc.

```sql
ALTER TABLE products ADD COLUMN store_id INT UNSIGNED, ADD INDEX(store_id);
ALTER TABLE orders ADD COLUMN store_id INT UNSIGNED;
...
```

### 🧩 Optional: create a `tenancy_migrations` table to track tenant-specific schema updates (if using dynamic DBs per store later).

---

## 🧭 Step 2: Store Identification Middleware

### ✅ Objective:

Detect which store a request is associated with using:

* **Subdomain** (e.g., `brand1.glimmio.com`)
* **Custom domain** (e.g., `mystore.com`)
* Or **admin header** (`X-Store-ID`)

### 🧠 Logic:

```php
// app/Http/Middleware/IdentifyStore.php

public function handle($request, Closure $next)
{
    $host = $request->getHost();
    
    $store = \App\Models\Store::where('custom_domain', $host)
        ->orWhere('subdomain', explode('.', $host)[0])
        ->first();

    if (!$store) {
        abort(404, 'Store not found.');
    }

    app()->instance('store', $store); // globally available
    config(['app.store_id' => $store->id]);

    return $next($request);
}
```

### Register Middleware:

```php
// app/Http/Kernel.php
'store.identify' => \App\Http\Middleware\IdentifyStore::class,
```

### Apply Globally:

```php
// routes/web.php or RouteServiceProvider
Route::middleware(['store.identify'])->group(function () {
    // all routes here will have store context
});
```

---

## 🧬 Step 3: Global Tenant Context Access

Set global helper:

```php
function store()
{
    return app('store');
}

function store_id()
{
    return app('store')->id ?? null;
}
```

Now use in all queries:

```php
Product::where('store_id', store_id())->get();
```

Or inject globally using Eloquent global scopes:

```php
class Product extends Model {
    protected static function booted()
    {
        static::addGlobalScope('store', function ($query) {
            $query->where('store_id', store_id());
        });
    }
}
```

---

## 🔐 Step 4: Store-Scoped Authentication

### In `users` table:

All users are global, but assigned to stores via `store_users`

### Authentication Logic:

1. When a user logs in from a store URL, ensure they belong to that store
2. On login success, set:

```php
session(['store_id' => store_id()]);
```

3. All backend routes must verify:

```php
$user->stores->contains(store_id())
```

---

## 🛑 Step 5: Block Cross-Tenant Data Access

To prevent security leaks:

* Use global scopes
* Use `store_id` in authorization policies
* Validate `store_id` in all data mutations

Example:

```php
$order = Order::where('store_id', store_id())->findOrFail($id);
```

---

## 🧪 Step 6: Seed and Test Multi-Store Setup

### Seed Example:

```php
php artisan tinker

$store1 = Store::create(['name' => 'BrandX', 'subdomain' => 'brandx']);
$store2 = Store::create(['name' => 'Luxez', 'custom_domain' => 'luxez.com']);
```

### Test Flow:

* Visit `brandx.glimmio.com` and create products
* Visit `luxez.com` and verify products are isolated

---

## 📦 Phase 1 Deliverables

* ✅ Tenant-aware schema
* ✅ Store resolver middleware
* ✅ Global `store()` helper
* ✅ store_id in queries + models
* ✅ Domain + subdomain routing
* ✅ Store-user relationship & scoped login


---

# 🟣 **Phase 2: Secure API-Driven Backend Refactor**

---

## 🎯 **Goal:**

Rebuild the backend as a modular, API-first Laravel application that serves both:

* The **Vue.js Admin Panel**
* The **Storefront UI**

This phase ensures:

* All operations are accessible via RESTful APIs
* All APIs are secured and scoped per tenant (`store_id`)
* Future integrations (mobile app, headless frontend, PWA) are easy and fast

---

## 🧩 Step 1: API Structure & Routing

### 🔸 Folder Structure:

Organize as:

```
routes/
  └── api.php
app/
  └── Http/
      └── Controllers/
          └── Api/
              ├── AuthController.php
              ├── ProductController.php
              ├── OrderController.php
              ├── StoreController.php
              ├── CartController.php
              ├── CustomerController.php
```

### 🔸 RESTful Endpoints:

* `/api/v1/products`
* `/api/v1/orders`
* `/api/v1/customers`
* `/api/v1/cart`
* `/api/v1/checkout`
* `/api/v1/store/settings`
* `/api/v1/auth/login`
* `/api/v1/auth/register`

### 🔒 Prefix & Middleware:

```php
Route::prefix('api/v1')
    ->middleware(['api', 'store.identify', 'throttle:100,1'])
    ->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
    });
```

---

## 🔐 Step 2: API Security (Laravel Sanctum)

### 🔸 Use Laravel Sanctum for SPA / token-based auth

**Install Sanctum**:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
php artisan migrate
```

**Setup middleware**:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 🔸 Token Authentication Flow:

1. On login:

```php
$token = $user->createToken('admin-panel')->plainTextToken;
return response()->json(['token' => $token]);
```

2. On API requests:

```http
Authorization: Bearer {token}
```

3. In controllers:

```php
$user = auth()->user();
$store = store();
abort_if(!$user->stores->contains($store), 403);
```

---

## 🧪 Step 3: Request Validation & Resource Classes

### 🔸 Use `FormRequest` classes for each endpoint

```php
php artisan make:request StoreProductRequest
```

Inside:

```php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
    ];
}
```

Use in controller:

```php
public function store(StoreProductRequest $request)
{
    return Product::create([...$request->validated(), 'store_id' => store_id()]);
}
```

### 🔸 Use `Resource` classes for consistent output:

```php
php artisan make:resource ProductResource
```

```php
return ProductResource::collection(Product::where('store_id', store_id())->get());
```

---

## 📤 Step 4: Error Handling & Response Format

### 🔸 Create API response helper:

```php
function apiResponse($data = null, $message = '', $code = 200) {
    return response()->json([
        'success' => $code < 400,
        'data' => $data,
        'message' => $message
    ], $code);
}
```

Use like:

```php
return apiResponse(new ProductResource($product), 'Product created successfully');
```

### 🔸 Global error handling:

In `Handler.php`:

```php
public function render($request, Throwable $e)
{
    if ($request->expectsJson()) {
        return apiResponse(null, $e->getMessage(), 500);
    }

    return parent::render($request, $e);
}
```

---

## 🧬 Step 5: Tenant-Aware Querying

### 🔸 Always scope with `store_id`

In base controller or model boot:

```php
static::addGlobalScope('store', fn ($q) => $q->where('store_id', store_id()));
```

Or explicitly:

```php
Product::where('store_id', store_id())->get();
```

---

## 📑 Step 6: Auto-Generated API Documentation

Use **Laravel Scribe** or **Swagger/OpenAPI**.

### With Scribe:

```bash
composer require knuckleswtf/scribe --dev
php artisan scribe:generate
```

Document with annotations:

```php
/**
 * @group Products
 * Get all products for the current store
 */
public function index(Request $request) { ... }
```

---

## 🧰 Step 7: Example API Controllers

### `ProductController.php`

```php
public function index()
{
    $products = Product::where('store_id', store_id())->paginate(10);
    return ProductResource::collection($products);
}

public function store(StoreProductRequest $request)
{
    $product = Product::create(array_merge(
        $request->validated(),
        ['store_id' => store_id()]
    ));
    return new ProductResource($product);
}
```

---

## 🛑 Step 8: Security Policies & Rate Limits

* Use Laravel's `Policy` system to control access (e.g., only staff can update products)
* Use:

```php
Route::middleware(['auth:sanctum', 'store.identify', 'can:update,product']);
```

* Add throttling:

```php
Route::middleware('throttle:50,1')->group(function () {
    ...
});
```

---

## 🧪 Step 9: Postman Collection or Swagger UI

* Export testable API collection (Postman)
* Generate Swagger docs for external plugin developers

---

## ✅ Deliverables for Phase 2

| Component              | Status |
| ---------------------- | ------ |
| Laravel Sanctum setup  | ✅      |
| API versioning         | ✅      |
| Tenant-scoped routes   | ✅      |
| Product/Order/Auth API | ✅      |
| Form validation        | ✅      |
| Global error handling  | ✅      |
| API documentation      | ✅      |

---

## 💡 Final Advice for Phase 2

* All business logic must go through **API controllers**
* Admin panel and storefront will consume these APIs
* Use **Postman collection** to test all endpoints thoroughly
* Add audit logs for all write actions (`order created`, `product updated`, etc.)



---

# 🟡 **Phase 3: Theme Engine & Storefront UI Rebuild**

---

## 🎯 Goal

Develop a **fully dynamic, theme-based storefront engine** where:

* Every store has its own customizable theme
* Merchants can edit theme code, settings, and sections visually
* Pages (homepage, product, collection, etc.) render based on active theme data
* Storefront fetches content via your new tenant-aware API

---

## 🧱 Step 1: Theme Folder Structure

Create theme folders like Shopify:

```
/themes/
  /default/
    config/
      settings_schema.json
      settings_data.json
    layout/
      theme.liquid
    templates/
      index.liquid
      product.liquid
      collection.liquid
    sections/
      hero.liquid
      product-grid.liquid
      featured-collection.liquid
    assets/
      style.css
      theme.js
```

> Support `.liquid` (or Blade) + JSON combo for structure and logic.

---

## 🧰 Step 2: Theme Engine Loader

Write theme rendering logic:

```php
function renderThemePage($template, $store_id)
{
    $theme = Theme::where('store_id', $store_id)->where('is_active', 1)->first();

    $layoutPath = "/themes/{$theme->folder}/layout/theme.liquid";
    $templatePath = "/themes/{$theme->folder}/templates/{$template}.liquid";

    $layout = file_get_contents($layoutPath);
    $content = file_get_contents($templatePath);

    $rendered = str_replace("{{ content_for_layout }}", $content, $layout);

    return renderLiquid($rendered, [
        'store' => store(),
        'products' => Product::where('store_id', $store_id)->get(),
        'collections' => Collection::where('store_id', $store_id)->get(),
    ]);
}
```

---

## 🧬 Step 3: JSON-Based Theme Settings

Each theme must contain a file:

```
/config/settings_schema.json
```

Example:

```json
[
  {
    "name": "Colors",
    "settings": [
      {
        "type": "color",
        "id": "primary_color",
        "label": "Primary Color",
        "default": "#000000"
      }
    ]
  }
]
```

And actual saved values in:

```
/config/settings_data.json
```

---

## 🧪 Step 4: Render Dynamic Settings in Templates

Use `{{ settings.primary_color }}` to inject values.

In PHP:

```php
$settings = json_decode(file_get_contents("themes/{$theme->folder}/config/settings_data.json"), true);
```

Pass to `renderLiquid()` for dynamic injection.

---

## 🎨 Step 5: Theme Editor (Admin Panel)

Build a theme editor under `admin/themes/editor`:

### Features:

* **Code Editor** (Monaco) for `liquid`, `.css`, `.js`
* **Live Preview** using an iframe:

  ```html
  <iframe src="{{ store_url }}/preview?theme_id=xx" />
  ```
* **Drag-and-drop Section Builder**:

  * Add/remove/duplicate sections
  * Section sorting via drag
* **Schema Auto-Form**:
  Render inputs from `settings_schema.json`

### Update/Save APIs:

* `POST /api/v1/themes/:id/files`
* `POST /api/v1/themes/:id/settings`

---

## 🧪 Step 6: Frontend Page Renderer

Create these default templates:

* `index.liquid`
* `product.liquid`
* `collection.liquid`
* `cart.liquid`
* `checkout.liquid`
* `page.liquid`

Use router logic like:

```php
if (Route::is('product.show')) {
  return renderThemePage('product', store_id());
}
```

---

## 🧩 Step 7: Section System (Drag-and-Drop)

Each section in the layout is:

```json
{
  "type": "hero",
  "settings": {
    "title": "Welcome",
    "subtitle": "New Arrivals"
  }
}
```

Saved in DB as part of layout JSON:

```php
[
  "order": ["section_hero", "section_grid"],
  "sections": {
    "section_hero": {
      "type": "hero",
      "settings": {
        "title": "Welcome"
      }
    },
    ...
  }
]
```

Render loop:

```php
foreach ($layout['order'] as $id) {
    $section = $layout['sections'][$id];
    $file = "/themes/{$theme->folder}/sections/{$section['type']}.liquid";
    echo renderLiquid(file_get_contents($file), $section['settings']);
}
```

---

## 🔁 Step 8: Theme Switching + Preview

* Each store can have multiple saved themes
* Store `is_active` flag per theme
* Preview via query param:

  * `?preview_theme_id=xx` (session scoped)

API:

```php
POST /api/v1/themes/:id/activate
```

---

## ⚙️ Step 9: Theme Versioning (Optional)

Save revisions with:

* Created timestamp
* Git-style diff (or store whole file)

Allow:

* Restore old version
* Track edits per section/file

---

## 🔒 Step 10: Access & Permissions

* Only `store_owner` or `theme_editor` roles can edit code
* Validate file types: only allow `.liquid`, `.css`, `.json`, `.js`
* Sanitize content before saving (to avoid broken syntax)

---

## 🧑‍💻 Bonus: Custom Code Injection

Allow:

* Custom `<head>` and `<footer>` HTML per store
* Script tag manager for:

  * Google Analytics
  * Facebook Pixel
  * Meta Tags

Stored in:

```php
store_settings->custom_head_script
```

---

## ✅ Phase 3 Deliverables

| Feature                  | Done |
| ------------------------ | ---- |
| Theme folder loader      | ✅    |
| Section rendering engine | ✅    |
| Live preview iframe      | ✅    |
| JSON schema settings     | ✅    |
| Code editor with Monaco  | ✅    |
| API endpoints for themes | ✅    |
| Section builder          | ✅    |
| Theme activation/preview | ✅    |
| Permissions + validation | ✅    |

---

## 💡 Final Notes

* Each theme is self-contained and portable
* Merchants can export/import theme zips
* Future monetization via **Theme Marketplace**
* You can build a CLI like:

  ```bash
  php artisan theme:generate default
  ```

---

# 🟠 **Phase 4: Full E-commerce Engine**

---

## 🎯 Goal

Implement a fully functioning e-commerce engine for each store:

✅ Product & variant management
✅ Collection management (manual + rule-based)
✅ Add-to-cart, cart session management
✅ Checkout flow (address → payment → confirmation)
✅ Order creation, tracking & management

---

## 🧱 Step 1: Product & Variant System

### ✅ Tables

```sql
products
- id
- store_id
- title
- description
- slug
- type
- vendor
- status (draft/published)
- created_at

product_variants
- id
- product_id
- title
- sku
- barcode
- price
- compare_at_price
- inventory_qty
- image_url
- option1, option2, option3
```

> Allow unlimited variants using a flexible structure OR denormalize into 3-option max if preferred for MVP.

---

### ✅ Admin APIs

```http
GET /api/v1/products
POST /api/v1/products
GET /api/v1/products/:id
PUT /api/v1/products/:id
DELETE /api/v1/products/:id
```

Variants as nested resource:

```http
POST /api/v1/products/:id/variants
PUT /api/v1/variants/:id
```

### ✅ Features

* Multiple product images
* Variant selection logic (color/size)
* Price per variant
* Compare-at price
* Inventory sync (basic, advanced later)
* Status: Published/Draft
* Tag & metafield support (future)

---

## 📂 Step 2: Collection Management

### ✅ Tables

```sql
collections
- id, store_id, title, slug, description, type (manual/rule)
- image_url

collection_product
- collection_id, product_id
```

### ✅ Rule-based logic

Store rules in JSON:

```json
{
  "conditions": [
    { "field": "type", "operator": "equals", "value": "Ring" },
    { "field": "vendor", "operator": "equals", "value": "Luxez" }
  ],
  "match_type": "all"
}
```

Run on product save:

```php
foreach ($collections as $collection) {
    if (matchProductToRule($product, $collection->rules)) {
        attachProductToCollection($product, $collection);
    }
}
```

---

## 🛒 Step 3: Cart Engine

### ✅ Cart Table (optional if storing in session/Redis)

```sql
carts
- id
- store_id
- user_id (nullable)
- token (for guest)
- status (open/abandoned)
- created_at

cart_items
- cart_id
- product_variant_id
- quantity
```

### ✅ API (stateless + cookie-based token)

```http
GET /api/v1/cart
POST /api/v1/cart/add
POST /api/v1/cart/update
POST /api/v1/cart/remove
```

### ✅ Logic

* Cart auto-creates via session token
* Items grouped by `variant_id`
* Quantity controls + item subtotal
* Cart subtotal, discount, shipping, total

---

## 📦 Step 4: Checkout Flow

### ✅ Address Form

```sql
order_addresses
- order_id, type (billing/shipping), name, phone, address_1, address_2, city, state, pincode, country
```

Use full address object for shipping providers.

---

### ✅ Payment Selection

Integrate with:

* Razorpay, Stripe, PayPal (first)
* Offline: COD, bank transfer

Payment Flow:

1. Confirm cart → lock it
2. Send to payment gateway
3. On webhook: update `orders.status = paid`

---

### ✅ Order Table

```sql
orders
- id, store_id, user_id, status (draft/paid/processing/shipped)
- cart_id, total, discount, shipping_cost
- payment_status
- tracking_number
- created_at

order_items
- order_id, variant_id, quantity, price_at_purchase
```

---

## 📤 Step 5: Order Status Flow

1. Customer places order
2. Admin dashboard shows:

   * New → Processing → Shipped → Delivered
3. Admin can:

   * Add tracking, mark as shipped
   * Cancel or refund

---

## ✨ Step 6: Add Features

### 🏷️ Discounts

```sql
discounts
- code, type (fixed/percent/free_shipping)
- applies_to (all/products/collections)
- usage_limit, times_used
- active_from, active_to
```

### 🧑 Guest Checkout

* Checkout without login
* Auto-create customer post-order

### 🛡️ Cart Locking

* Lock cart on checkout begin
* Prevent price changes during checkout

---

## 🧪 Step 7: Frontend Storefront Rendering

* Product page shows:

  * Main image gallery
  * Variant dropdowns
  * Add to cart
  * Description, vendor, type
* Cart page:

  * Line items, subtotal, checkout button
* Checkout:

  * Step 1: Info
  * Step 2: Shipping
  * Step 3: Payment
* Order confirmation:

  * Order ID, summary, status

---

## 🔐 Step 8: Validations & Security

* Ensure all product/cart/order actions validate `store_id`
* Verify inventory availability before order placement
* Block direct manipulation of prices/discounts from client
* Use signed checkout tokens to prevent tampering

---

## ✅ Deliverables for Phase 4

| Feature                       | Implemented |
| ----------------------------- | ----------- |
| Product CRUD + variants       | ✅           |
| Collections (manual + rule)   | ✅           |
| Cart (session + API)          | ✅           |
| Checkout with address/payment | ✅           |
| Order management              | ✅           |
| Discount support              | ✅           |
| Guest checkout                | ✅           |

---

## 🔄 Future Enhancements (after core is done)

* Multi-currency + tax rules
* Split payment methods
* Automated shipping label generation
* Gift cards and store credits
* Order timeline + activity logs

---

# 🔵 **Phase 5: SaaS Subscription & Plan Management**

---

## 🎯 Goal

Enable **multi-tenant billing** so each store is subscribed to a plan with limits. The platform should:

* Offer subscription-based pricing (monthly, yearly)
* Handle free trials, billing cycles, cancellations
* Enforce plan-specific feature limits
* Integrate with payment gateways (Stripe, Razorpay)
* Auto-disable stores on failed billing

---

## 🧱 Step 1: Define Plans

### ✅ plans table

```sql
plans (
  id INT PRIMARY KEY,
  name VARCHAR(100),
  price DECIMAL(10,2),
  billing_cycle ENUM('monthly','yearly'),
  features JSON, -- e.g. {"products":1000,"storage":"5GB","staff":2}
  trial_days INT,
  is_active TINYINT(1),
  created_at DATETIME
)
```

### Example plans:

```json
[
  {
    "name": "Starter",
    "price": 499,
    "billing_cycle": "monthly",
    "features": {
      "products": 100,
      "storage": "1GB",
      "staff_accounts": 1
    },
    "trial_days": 14
  },
  ...
]
```

---

## 🧩 Step 2: Track Store Subscriptions

### ✅ store_subscriptions table

```sql
store_subscriptions (
  id INT PRIMARY KEY,
  store_id INT,
  plan_id INT,
  started_at DATETIME,
  ends_at DATETIME,
  trial_ends_at DATETIME,
  status ENUM('active','trialing','cancelled','expired'),
  payment_gateway ENUM('stripe','razorpay'),
  subscription_id VARCHAR(255), -- Stripe/Razorpay sub ID
  last_payment_at DATETIME,
  next_payment_due DATETIME
)
```

---

## 🧪 Step 3: Subscription Flow

### 🧑‍💼 1. Store owner picks a plan

```http
POST /api/v1/stores/:store_id/subscribe
{
  "plan_id": 2,
  "gateway": "stripe"
}
```

* Store is redirected to payment gateway
* On success: webhook triggers plan activation

---

### 🔔 2. Handle Webhooks

From Stripe/Razorpay:

* `subscription.created`
* `subscription.payment_success`
* `subscription.payment_failed`
* `subscription.cancelled`

Update:

* `status`, `ends_at`, `next_payment_due`, `last_payment_at`

---

### 🧾 3. Invoicing (Optional)

Store invoice details:

```sql
invoices (
  id INT,
  store_id INT,
  amount DECIMAL(10,2),
  gateway ENUM('stripe','razorpay'),
  external_invoice_id VARCHAR(255),
  paid_at DATETIME,
  download_url VARCHAR(255)
)
```

---

## 🧩 Step 4: Enforce Plan Limits

### In code:

```php
if ($store->plan()->features['products'] <= $store->products()->count()) {
  abort(403, 'Upgrade your plan to add more products.');
}
```

Limits to enforce:

* Number of products
* Storage used (upload size tracker)
* Number of staff accounts
* API requests per minute
* Custom domain support
* Abandoned cart recovery (on/off)

Use a helper:

```php
function storeHasAccess($feature) {
    return $store->plan()->features[$feature] ?? false;
}
```

---

## ⚙️ Step 5: Plan Downgrade/Cancellation

* Cancel subscription via gateway
* Mark `store_subscriptions.status = cancelled`
* Add grace period: allow access until `ends_at`
* Then disable storefront and admin login:

```php
if ($store->subscription->isExpired()) {
  return response()->view('store.expired');
}
```

---

## 🧑‍💼 Step 6: Admin SaaS Dashboard (for you)

Superadmin view to manage:

* All stores & subscriptions
* Monthly revenue report
* Active trial users
* Expired/unpaid stores

Table: `admin/subscriptions`

---

## 🧑‍💻 Step 7: SaaS Signup Onboarding Flow

1. Sign up → enter store name
2. Choose a plan (free or paid)
3. Setup billing
4. Redirect to admin dashboard

Auto-setup:

* Demo theme
* Sample products
* Welcome checklist (onboarding steps)

---

## 🔐 Step 8: Security & Abuse Protection

* Limit failed payment attempts
* Email + notify store owner on:

  * Failed billing
  * Trial ending soon
  * Plan upgrade success

---

## ✅ Deliverables for Phase 5

| Feature                        | Status |
| ------------------------------ | ------ |
| Plan definition + pricing      | ✅      |
| Subscription billing           | ✅      |
| Trial + free plan support      | ✅      |
| Gateway integration (Stripe)   | ✅      |
| Webhook handling               | ✅      |
| Invoicing system               | ✅      |
| Plan-based feature enforcement | ✅      |
| Grace periods & suspensions    | ✅      |
| Superadmin SaaS dashboard      | ✅      |

---

## 💡 Optional Add-Ons Later

* Upgrade via Razorpay/Stripe checkout embedded
* Affiliate link-based signup tracking
* Plan-specific theme access
* Metered billing (e.g., pay-per-order above quota)



---

# 🟣 **Phase 6: Marketing & Automation Engine**

---

## 🎯 Goal

Enable each store on the platform to:

* Set up **marketing pixels** (Meta, Google, TikTok, etc.)
* Automate **emails** (welcome, cart recovery, order updates)
* Run **discounts, upsells, and abandoned cart campaigns**
* Provide **advanced performance analytics**

This makes Glimmio a real **growth-focused platform**, not just a store builder.

---

## 🧱 Step 1: Marketing Pixels Integration

### ✅ Table: `store_pixel_settings`

```sql
store_pixel_settings (
  id,
  store_id,
  enable_facebook_pixel TINYINT,
  facebook_pixel_id VARCHAR(255),
  enable_google_analytics TINYINT,
  google_analytics_id VARCHAR(255),
  enable_snapchat_pixel TINYINT,
  snapchat_pixel_id VARCHAR(255),
  enable_tiktok_pixel TINYINT,
  tiktok_pixel_id VARCHAR(255)
)
```

### ✅ Pixel Injection Logic

In `theme.liquid` layout:

```liquid
{% if store.enable_facebook_pixel %}
  <script>
    fbq('init', '{{ store.facebook_pixel_id }}');
    fbq('track', 'PageView');
  </script>
{% endif %}
```

> Inject dynamically based on store settings. Same applies for Google GA4, TikTok, and Snap.

---

## 🧬 Step 2: Email Automation Engine

### ✅ Email Templates System

```sql
email_templates (
  id,
  store_id,
  type ENUM('welcome','cart_recovery','order_confirmation','shipping_update'),
  subject VARCHAR(255),
  body TEXT,
  enabled TINYINT,
  delay_minutes INT DEFAULT 0
)
```

Allow WYSIWYG or raw HTML + placeholders like:

* `{{ customer.name }}`
* `{{ order.id }}`
* `{{ cart.items }}`

---

### ✅ Email Triggering via Queued Jobs

#### Examples:

* **On user sign-up**:
  → Trigger welcome email (immediate)

* **On cart abandoned**:
  → Trigger reminder after 2 hours (if no checkout)

* **On order created**:
  → Order confirmation email

### Setup Laravel Jobs:

```bash
php artisan make:job SendCartRecoveryEmail
php artisan make:job SendOrderConfirmation
```

Use:

```php
dispatch(new SendCartRecoveryEmail($cart))->delay(now()->addMinutes(120));
```

---

### ✅ Email Settings per Store

```sql
store_email_settings (
  store_id,
  from_email,
  from_name,
  smtp_host,
  smtp_port,
  smtp_username,
  smtp_password,
  use_platform_email BOOLEAN
)
```

* Either use **global Glimmio SMTP** (like SendGrid)
* Or allow merchant’s own SMTP

---

## 🛒 Step 3: Abandoned Cart Recovery

### ✅ Detection Logic:

1. Cart exists
2. Customer has email
3. No checkout within 1–3 hours
4. No order placed

### ✅ Send Series:

* Email 1: After 1 hour → "You left something behind"
* Email 2: After 6 hours → With discount
* Email 3: After 24 hours → Final reminder

### ✅ Discount Code Generator

```sql
discounts (
  code, type, amount, auto_generated, expires_at, applies_to_cart
)
```

Auto-generate:

```php
$code = 'SAVE' . rand(100,999);
```

Include in cart email:

> "Use code **SAVE231** for 10% off your purchase — valid for 24 hours."

---

## 📊 Step 4: Advanced Performance Analytics

Go beyond GA with:

* **Session tracking per visitor**
* **Most viewed products**
* **Cart abandon rate**
* **Top converting products**
* **Coupon redemption metrics**
* **Traffic source breakdown**

### ✅ Table: `store_analytics_sessions`

```sql
- id
- store_id
- ip_address
- referrer
- user_agent
- path
- duration
- is_cart_started BOOLEAN
- is_checkout BOOLEAN
- is_order BOOLEAN
- created_at
```

Visualize with:

* Time-series charts
* Heatmaps
* Funnel steps

---

## 📦 Step 5: Upsell & Post-Purchase Campaigns

* Show upsell modal after Add-to-Cart
* Enable "You may also like" section on checkout
* Post-purchase email with:

  * Product bundles
  * Accessories
  * Loyalty program info

---

## 💬 Step 6: Social & WhatsApp Integrations

* Allow store owners to connect:

  * **WhatsApp** for order updates
  * **Instagram & Facebook** for catalog sync
* API: `POST /store/whatsapp/connect` (store token)
* Use Twilio or Meta Cloud API for WhatsApp

---

## 🔁 Step 7: Automation Flows (Later Enhancement)

Like Klaviyo:

* Drag-and-drop editor to build flows
* Triggers: `customer signup`, `order placed`, `no purchase in 30 days`
* Actions: `send email`, `apply tag`, `send WhatsApp`

Example:

```
Trigger: Customer signup
→ Wait 10 mins
→ Send Welcome Email
→ Wait 3 days
→ Send Bestsellers
```

---

## ✅ Deliverables for Phase 6

| Feature                              | Status |
| ------------------------------------ | ------ |
| Pixel support (Meta, Google, TikTok) | ✅      |
| Email engine with templates          | ✅      |
| SMTP settings per store              | ✅      |
| Cart recovery flow + discounts       | ✅      |
| Order emails                         | ✅      |
| Analytics tracking                   | ✅      |
| WhatsApp order integration           | ✅      |
| Campaign performance dashboard       | ✅      |

---

## 💡 Future Enhancements

* Segment sync (Google Ads, Meta CAPI)
* Predictive analytics (AI-based product suggestions)
* Loyalty program integration
* Affiliate/referral engine



---

# 🟤 **Phase 7: Domain, CDN, and Asset Handling for Each Store**

---

## 🎯 Goal

Make the platform **production-ready and globally performant** by enabling:

* ✅ Custom domain mapping per store (`www.brand.com`)
* ✅ Wildcard subdomains for preview or default (`brand.glimmio.com`)
* ✅ Secure and isolated asset storage per tenant
* ✅ CDN integration for fast media delivery (Cloudflare, AWS CloudFront)
* ✅ Fallbacks, SSL, favicon, and uploads for logos, theme assets, etc.

---

## 🧱 Step 1: Store Domain Table & Settings

### ✅ `store_domains` Table:

```sql
store_domains (
  id INT,
  store_id INT,
  domain_name VARCHAR(255),         -- e.g. luxezjewels.com
  is_primary TINYINT(1) DEFAULT 0,
  is_verified TINYINT(1) DEFAULT 0,
  created_at DATETIME
)
```

* Each store can have:

  * 1 primary domain (custom)
  * Multiple preview subdomains (like `luxez.glimmio.com`)
* Show DNS instructions in admin panel

---

## 🧩 Step 2: Domain Routing Middleware

### ✅ Laravel Middleware: `TenantDomainResolver.php`

```php
public function handle($request, Closure $next)
{
    $host = $request->getHost(); // e.g. luxez.glimmio.com or brand.com

    $store = StoreDomain::where('domain_name', $host)->first();

    if (!$store) {
        return response("Store not found", 404);
    }

    app()->instance('current_store', $store->store);

    return $next($request);
}
```

### Register in `kernel.php` under web stack:

```php
'tenant.domain' => \App\Http\Middleware\TenantDomainResolver::class
```

Apply globally to all public routes.

---

## 🌐 Step 3: DNS Instructions for Users

When setting a custom domain, show:

```
To point your domain:
- Set A record to: 34.90.XX.XXX
- Or use CNAME for subdomain: store.glimmio.com

We automatically install free SSL (Let's Encrypt)
```

* Use [certbot](https://certbot.eff.org/) or Caddy for SSL auto-install
* For wildcard domains (`*.glimmio.com`), configure wildcard cert in nginx or use Cloudflare proxy

---

## 🧳 Step 4: Asset Uploads Per Store

### ✅ Folder Structure:

```
/storage/
  /store_123/
    /products/
    /themes/
    /logos/
    /pages/
```

Each file path is scoped to the store ID, even in frontend.

### ✅ Product Image Upload:

```php
$request->file('image')->store('store_' . $store->id . '/products', 's3');
```

> Store file name, URL, and original name in a table like `media_files`.

---

## 🧊 Step 5: CDN Integration (Cloudflare / AWS CloudFront)

### ✅ Two options:

1. **Cloudflare**:

   * Proxy all traffic
   * Enable automatic caching and image optimization
   * Free SSL and DDoS protection

2. **AWS CloudFront + S3**:

   * Store assets in S3
   * Serve via CloudFront with signed URLs (optional)

### ✅ Config for S3 in `.env`

```env
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=glimmio-store-assets
AWS_URL=https://cdn.glimmio.com/
```

---

## 🎨 Step 6: Favicon, Logo, and Theme Asset Support

### ✅ Admin panel file uploads:

* Upload `store_logo`, `favicon`, `store_banner`
* Saved in `store_settings` or `media_files`

In theme:

```liquid
<img src="{{ store.logo_url }}" alt="Logo" />
<link rel="icon" href="{{ store.favicon_url }}">
```

Add preview on upload, with cropping if needed.

---

## 🔒 Step 7: Secure Access & Path Restrictions

* Validate uploads:

  * Type: only `.jpg`, `.png`, `.svg`, `.json`, `.css`, `.js`
  * Size limit (e.g. 5MB per file)
* Ensure:

  * Store A cannot access assets of Store B
  * No file overwrite collisions

Optional:

```php
$file->hashName(); // To generate unique paths
```

---

## 📦 Step 8: File Manager API

Create:

```http
GET /api/v1/files
POST /api/v1/files/upload
DELETE /api/v1/files/:id
```

Support:

* Uploading into `product/`, `themes/`, `pages/`
* Attach files to product descriptions or rich text editors

---

## 🔁 Step 9: Asset Garbage Collection

Set up a CRON Job:

* Delete orphaned files (not linked to product, page, theme, etc.)
* Mark as `soft_deleted` first for recovery

```bash
php artisan schedule:run
```

---

## ✅ Deliverables for Phase 7

| Feature                               | Status |
| ------------------------------------- | ------ |
| Domain mapping (custom + preview)     | ✅      |
| Store asset isolation                 | ✅      |
| CDN file delivery                     | ✅      |
| Logo, favicon, theme file uploads     | ✅      |
| File manager + uploader               | ✅      |
| SSL support via Certbot or Cloudflare | ✅      |
| Secure media validation               | ✅      |

---

## 💡 Future Upgrades

* Add DNS records API for automatic domain connection
* Real-time CDN invalidation on file overwrite
* Watermarked image previews
* Bandwidth and storage tracking per store



---

# 🟡 **Phase 8: Global Admin Dashboard & Platform Analytics**

---

## 🎯 Goal

Design and implement a powerful **Super Admin Panel** to:

* Manage all stores, users, and subscriptions
* Monitor platform usage and earnings
* Track KPIs (MRR, churn, active stores, traffic)
* View/store-level analytics
* Control platform-wide settings, feature flags, and billing

---

## 🧱 Step 1: Admin Access Control

### ✅ Users Table Enhancement:

```sql
users (
  id, name, email, role ENUM('admin', 'merchant', 'staff'), ...
)
```

### ✅ Admin Middleware:

```php
if (auth()->user()->role !== 'admin') {
  abort(403, 'Unauthorized');
}
```

Apply to:

```php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(...);
```

---

## 📊 Step 2: KPI Dashboard Cards

Display metrics such as:

| Metric                       | Data Source                    |
| ---------------------------- | ------------------------------ |
| 🏬 Active Stores             | `stores where is_active = 1`   |
| 💰 Monthly Recurring Revenue | SUM(plan.price) of active subs |
| 🔁 Churn Rate                | Subscription cancel logs       |
| 👥 Active Users Today        | Sessions in last 24h           |
| 📦 Orders Today              | `orders.created_at = today()`  |
| 📈 Site Traffic              | `store_analytics_sessions`     |

Build top cards using Laravel Charts or JS charting libs (e.g. Chart.js, ApexCharts).

---

## 🧑‍💼 Step 3: Store Management Panel

### ✅ `/admin/stores` Table View

| Store Name | Owner Email | Plan | Status | Last Login | Actions |
| ---------- | ----------- | ---- | ------ | ---------- | ------- |

#### 🔘 Actions:

* View full store profile
* Impersonate (admin login as merchant)
* Suspend / Enable store
* Reset theme
* Assign plan manually
* View store activity log

---

## 💳 Step 4: Subscription Monitoring

### `/admin/subscriptions`

| Store | Plan | Payment Gateway | Status | Trial Ends | Next Due | Actions |
| ----- | ---- | --------------- | ------ | ---------- | -------- | ------- |

#### ✅ Filter by:

* Plan type
* Status (active, expired, trialing)
* Trial end within 7 days

Enable:

* One-click manual extension of trial
* Manual invoice resend (via Stripe/Razorpay)

---

## 📈 Step 5: Platform Analytics Dashboard

Use stored session data to visualize:

* 🔥 Most visited stores
* 📍 Top referrer sources
* 🕰️ Avg session duration
* 💨 Bounce rate
* 📦 Top stores by orders or GMV
* 🔄 Conversion funnels (Visitors → Orders)

Use a pre-aggregated table like:

```sql
platform_metrics (
  date DATE,
  active_users INT,
  new_stores INT,
  subscriptions_started INT,
  mrr FLOAT,
  churn_rate FLOAT
)
```

Update nightly via scheduler.

---

## 📑 Step 6: Feature Flags & Global Configs

Create:

```sql
platform_settings (
  key VARCHAR(255),
  value TEXT,
  data_type ENUM('string','json','boolean','integer')
)
```

Examples:

* `"enable_theme_marketplace": true`
* `"default_trial_days": 14`
* `"maintenance_mode": false`

### ✅ Admin UI:

* Toggle platform flags
* Set limits per plan
* Enable/disable payment methods

---

## 🕵️ Step 7: Logs, Activity, and Audits

### ✅ Logs to Track:

* Store creation
* Store deletion
* Subscription changes
* Payment webhooks
* Failed logins
* File uploads
* Theme changes

### Tables:

```sql
admin_activity_logs (
  id, store_id, user_id, action_type, action_data, ip_address, user_agent, created_at
)
```

Searchable logs UI with filters.

---

## 🧑‍💻 Step 8: Admin Impersonation Feature

* Admin can log in as any store owner
* Button in store profile: `Impersonate`

#### Laravel Implementation:

```php
Auth::loginUsingId($merchant->user_id);
// then redirect to /dashboard
```

Display banner:

> "You are impersonating Store Owner - Exit Impersonation"

---

## 🔒 Step 9: Access, Security & Backups

* All admin actions logged
* Two-factor auth (2FA) for admin panel
* Daily database backups stored in S3
* Monitor storage usage per store (warn if quota is near)

---

## ✅ Deliverables for Phase 8

| Feature                           | Status |
| --------------------------------- | ------ |
| Admin role & access middleware    | ✅      |
| KPI dashboard                     | ✅      |
| Stores management table           | ✅      |
| Subscription & trial tracking     | ✅      |
| Platform-wide analytics           | ✅      |
| Impersonation                     | ✅      |
| Feature toggles                   | ✅      |
| Audit logs                        | ✅      |
| Secure backups & activity control | ✅      |

---

## 💡 Future Enhancements

* Affiliate/referral program management
* Partner API tokens for integration (Zapier, Segment)
* Store-level API quota monitoring
* Admin mobile app
* Slack/email alerts for store activity (e.g., high churn)



---

# 🟣 **Phase 9: Plugin + App Store Marketplace (3rd-Party Integration)**

---

## 🎯 Goal

Enable store owners to install “apps” that enhance their store’s capabilities without needing to hardcode features per merchant.

This will:

* Drive extensibility (like Shopify/WooCommerce apps)
* Allow internal and 3rd-party plugins
* Provide a GUI-based marketplace
* Trigger APIs, jobs, UI enhancements, webhooks

---

## 🧱 Step 1: App Registry System

### ✅ `apps` table

```sql
apps (
  id INT,
  name VARCHAR(255),
  slug VARCHAR(255) UNIQUE,
  description TEXT,
  icon_url VARCHAR(255),
  developer_id INT, -- null if internal
  category VARCHAR(100),
  type ENUM('integration','visual','backend','marketing'),
  is_active TINYINT,
  settings_schema JSON,
  created_at DATETIME
)
```

> The `settings_schema` will drive the app’s form UI (like block schema in Shopify).

---

### ✅ `store_apps` table

```sql
store_apps (
  id INT,
  store_id INT,
  app_id INT,
  is_installed TINYINT,
  settings JSON,
  installed_at DATETIME
)
```

---

## 🛒 Step 2: App Marketplace Interface

### URL: `/admin/apps/marketplace`

Grid view:

* App icon
* Name & brief
* Install button

#### Upon install:

* Insert into `store_apps`
* Redirect to app config form (`/admin/apps/:slug/configure`)
* Trigger any required backend job or API registration

---

## 🧩 Step 3: App Settings & Config UI

Each app may have its own settings form.

### Examples:

* WhatsApp Plugin → `phone number`, `message template`
* Facebook Feed Sync → `Page ID`, `Access Token`
* Custom Reviews Widget → `heading`, `colors`, `style`

You’ll build forms dynamically using `settings_schema`:

```json
[
  { "label": "WhatsApp Number", "type": "text", "key": "phone" },
  { "label": "Welcome Message", "type": "textarea", "key": "message" }
]
```

---

## ⚙️ Step 4: App Execution Modes

Your apps can run in different ways:

| Type            | Runs As                      | Example                      |
| --------------- | ---------------------------- | ---------------------------- |
| API integration | External job/webhook handler | Shiprocket, Meta Catalog     |
| Theme extension | Injects into theme preview   | Review badge, WhatsApp popup |
| Admin UI block  | Extra card in dashboard      | Sales tips, upsell widget    |
| Backend logic   | Hooked into order processing | Loyalty engine, invoice tool |

---

## 🔌 Step 5: App Hook System (Hooks/Listeners)

Create central event dispatcher (Laravel Events):

### Examples:

```php
event('order.created', $order);
event('product.updated', $product);
event('customer.signed_up', $customer);
```

Apps can “subscribe” to these events:

```php
// AppServiceProvider
Event::listen('order.created', function ($order) {
   $app = StoreApp::forStore($order->store_id)->whereApp('zapier')->first();
   if ($app) {
     Http::post($app->settings['webhook_url'], $order->toArray());
   }
});
```

---

## 🔁 Step 6: App Sync & Scheduler System

Apps can define scheduled syncs:

* Google Shopping Feed sync
* Send email reports every 24h
* Review collection jobs

Register jobs using:

```php
$schedule->job(new SyncZapierFeed)->everyFiveMinutes();
```

Apps define sync frequency in DB or via `cron_definition`:

```json
"schedule": "0 * * * *"
```

---

## 🔐 Step 7: App Permissions + Validation

In `store_apps` table:

```sql
permissions JSON
```

Permissions like:

* `["orders:read", "customers:write"]`

Each app must request required scopes on install. Admin can approve/reject.

Block unauthorized API access.

---

## 📂 Step 8: Developer SDK / API

Allow third-party developers to:

* Create apps with standardized structure
* Use platform SDK for common hooks, auth, and assets
* Submit apps via a developer dashboard

Folder structure for apps:

```
apps/
  reviews-widget/
    manifest.json
    index.php
    admin_form.blade.php
    theme_script.js
```

---

## ✍️ Step 9: App Store Submission Flow

* Admin panel for “App Review Team”
* Validate app security, hooks, speed
* Approve → Publish to marketplace

Each app has:

* Screenshots
* Category/tag
* Support email
* Developer website
* App version

---

## ✅ Deliverables for Phase 9

| Feature                           | Status |
| -------------------------------- | ------ |
| App registry and schema system    | ✅      |
| Store app installs + config panel | ✅      |
| Dynamic settings renderer         | ✅      |
| Hooks & listener engine           | ✅      |
| External API/webhook support      | ✅      |
| Theme integration points          | ✅      |
| Admin marketplace UI              | ✅      |
| Developer SDK support (future)    | ✅      |

---

## 💡 Future Enhancements

* Revenue sharing model (20% platform cut)
* Subscription pricing for apps
* App reviews/ratings
* Analytics per app (usage, errors, uninstalls)
* Auto-disable broken apps
* App update/version control



---

# 🟤 **Phase 10: Multi-Store & Agency Panel for Resellers + White Label SaaS**

---

## 🎯 Goal

Allow **agencies, resellers, or white-label clients** to:

* Create and manage multiple stores from one dashboard
* Brand the platform as their own (logo, domain, emails)
* View revenue per store
* Control teams and clients under their agency
* Offer their own pricing/plans

This turns Glimmio from a SaaS platform into a **White-Label SaaS ecosystem**.

---

## 🧱 Step 1: Agency User Role & Hierarchy

### ✅ Updated `users` table:

```sql
users (
  id,
  name,
  email,
  password,
  role ENUM('admin', 'agency', 'merchant', 'staff'),
  agency_id INT NULL, -- Self if agency
  ...
)
```

Roles:

* `admin` → Platform owner
* `agency` → Manages multiple clients/stores
* `merchant` → Owns a single store
* `staff` → Staff of merchant or agency

---

## 🗂 Step 2: Agencies Table

```sql
agencies (
  id,
  name,
  slug,
  contact_email,
  support_url,
  domain VARCHAR(255), -- For white-label: panel.agencyname.com
  logo_url,
  color_primary,
  is_active TINYINT,
  created_at DATETIME
)
```

> Each agency gets a portal: `admin.glimmio.com/agency/{slug}` or custom domain like `panel.storeagency.com`.

---

## 🧑‍💼 Step 3: Agency Panel Interface

### `/agency/dashboard`

| Module            | Details                                   |
| ----------------- | ----------------------------------------- |
| Stores            | View/manage stores under the agency       |
| Clients           | Invite/edit store owners                  |
| Plans/Pricing     | Custom plans or override base plans       |
| Usage Reports     | Revenue, active stores, client conversion |
| Branding Settings | Set logo, colors, custom support links    |
| Team Members      | Add internal team to manage client stores |

---

## 🏬 Step 4: Store Management by Agency

### `/agency/stores`

| Store | Client | Status | Plan | Created At | Actions |
| ----- | ------ | ------ | ---- | ---------- | ------- |

* ✅ Create store
* ✅ Assign to client
* ✅ Impersonate into store
* ✅ Suspend or delete store

Each store still has its own backend and admin, but lives **under the agency**.

---

## ⚙️ Step 5: White Label Config & Branding

Each agency can override:

* Logo on login + dashboard
* Dashboard primary color
* Custom domain & SSL
* Email “from” name & address
* Meta tags, favicon, etc.

Stored in:

```sql
agency_branding (
  agency_id,
  logo_url,
  primary_color,
  email_from_name,
  email_from_address,
  favicon_url,
  dashboard_title
)
```

---

## 🧾 Step 6: Agency-Specific Plans & Pricing

Agencies can:

* Define their own plans (private)
* Override pricing of global plans

```sql
agency_plans (
  agency_id,
  plan_id,
  override_price,
  is_default
)
```

> For example, an agency may offer "Starter" at ₹299 while platform base is ₹499.

---

## 👨‍👩‍👧‍👦 Step 7: Invite System

Allow agency admins to:

* Invite merchants with a magic link
* Pre-create store, assign plan
* Add teammates with specific permissions:

  * Manage clients
  * View revenue
  * Create campaigns
  * Edit themes only

---

## 📊 Step 8: Agency Analytics Dashboard

### KPIs:

* Total revenue from clients
* Active stores
* Average order value
* Client churn rate
* Best-performing clients
* Store growth over time

Use charts similar to superadmin dashboard, scoped to agency ID.

---

## 🧠 Step 9: Role-Based Permissions

### Agency user permission matrix:

| Module            | View | Create | Update | Delete |
| ----------------- | ---- | ------ | ------ | ------ |
| Stores            | ✅    | ✅      | ✅      | ✅      |
| Plans             | ✅    | ✅      | ✅      | ❌      |
| Clients           | ✅    | ✅      | ✅      | ✅      |
| Branding Settings | ✅    | ✅      | ✅      | ❌      |
| Reports           | ✅    | ❌      | ❌      | ❌      |

Each agency user can have a permission group.

---

## ✅ Deliverables for Phase 10

| Feature                          | Status |
| -------------------------------- | ------ |
| Agency user role                 | ✅      |
| Multi-store control panel        | ✅      |
| White-label branding             | ✅      |
| Custom domain support per agency | ✅      |
| Analytics & KPIs per agency      | ✅      |
| Agency-specific plan pricing     | ✅      |
| Invite & team system             | ✅      |
| Client impersonation             | ✅      |

---

## 💡 Future Enhancements

* Revenue share split between agency and platform
* Commission-based affiliate model for new stores
* Store resale marketplace (agencies sell pre-built stores)
* Agency public profile & portfolio
* Reseller API for agency CRMs or integrations

...
\n# Additional Phases (11-37) Summarized\n
## Phases 11-37 Overview
- **Phase 11:** AI-powered onboarding assistant and store setup wizards.
- **Phase 12:** PWA and mobile app support with push notifications.
- **Phase 13:** Global search and filters via Meilisearch.
- **Phase 14:** Custom page builder with drag-and-drop sections.
- **Phase 15:** Order fulfillment, shipping integrations, and invoices.
- **Phase 16:** Plugin marketplace and extension ecosystem.
- **Phase 17:** CRM features, customer tags, and loyalty system.
- **Phase 18:** Staff accounts, permissions, and audit logs.
- **Phase 19:** Internationalization, multi-currency, and tax rules.
- **Phase 20:** Native analytics, heatmaps, and session recording.
- **Phase 21:** Affiliate and referral program system.
- **Phase 22:** POS integration for offline sales.
- **Phase 23:** Headless API and SDKs for developers.
- **Phase 24:** Marketplace app store for integrations.
- **Phase 25:** AI tools for merchants (copy, SEO, design).
- **Phase 26:** Headless PWA app builder.
- **Phase 27:** Product reviews and UGC system.
- **Phase 28:** Built-in analytics with heatmaps.
- **Phase 29:** Internal messaging and DM system.
- **Phase 30:** Omnichannel selling across platforms.
- **Phase 31:** GST invoicing and compliance toolkit.
- **Phase 32:** Help center and ticketing system.
- **Phase 33:** Subscription products and reorders.
- **Phase 34:** Universal theme compatibility layer.
- **Phase 35:** Theme marketplace with versioning.
- **Phase 36:** App store and plugin system.
- **Phase 37:** AI personalization and merchandising tools.
