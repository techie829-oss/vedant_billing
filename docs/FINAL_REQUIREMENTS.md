# 📘 BILLING & ACCOUNTING SAAS
## COMPREHENSIVE FINAL REQUIREMENTS DOCUMENT

**Version:** 1.2 Final  
**Last Updated:** December 23, 2025  
**Status:** LOCKED - Source of Truth  
**Platforms:** Web (PWA) ONLY

---

## 📋 TABLE OF CONTENTS

1. [Executive Summary](#executive-summary)
2. [Product Overview](#product-overview)
3. [Technical Architecture](#technical-architecture)
4. [Technology Stack](#technology-stack)
5. [Multi-Tenancy Model](#multi-tenancy-model)
6. [Authentication & User Management](#authentication-user-management)
7. [Module Structure](#module-structure)
7. [Module Structure](#module-structure)
8. [Feature Level Strategy](#feature-level-strategy)
9. [SaaS & Feature Management](#saas-feature-management)
10. [Database Design Principles](#database-design-principles)
11. [Accounting & Ledger Model](#accounting-ledger-model)
12. [File & Branding Management](#file-branding-management)
13. [UI/UX Design System](#ui-ux-design-system)
14. [PWA & Offline Requirements](#pwa-offline-requirements)
15. [Security Requirements](#security-requirements)
16. [Performance Requirements](#performance-requirements)
17. [Backup & Data Safety](#backup-data-safety)
18. [Deployment & DevOps](#deployment-devops)
19. [Out of Scope](#out-of-scope)
20. [Success Metrics](#success-metrics)
21. [Lock Statement](#lock-statement)

---

## 1️⃣ EXECUTIVE SUMMARY {#executive-summary}

This document serves as the **single source of truth** for the Billing & Accounting SaaS platform. It is designed for:

- Internal development team
- CTO / architect review
- Future hiring and onboarding
- Investor / partner discussions

### Key Principles

- **Zero data loss** - Data integrity is paramount
- **Stable accounting logic** - Ledger-based, event-driven
- **Feature-based monetization** - Clean SaaS model
- **Minimal future DB changes** - Designed for scale
- **Scalability** - From 10 to 1M+ businesses

---

## 2️⃣ PRODUCT OVERVIEW {#product-overview}

### Product Type

Multi-tenant **Billing, Inventory & Accounting SaaS**
- Offline-first architecture
- PWA-enabled
- Subscription-based monetization

### Target Users

- **Primary:** Small & medium businesses
- **Secondary:** Retailers, service providers
- **Future:** Multi-location businesses, accountants

### Core Goals

1. **Zero data loss** - Robust sync and conflict resolution
2. **Stable accounting logic** - Immutable ledger system
3. **Feature-based SaaS monetization** - Data-driven feature flags
4. **Minimal future DB changes** - Extensible schema design
5. **Scale from 10 → 1M+ businesses** - Multi-tenant architecture

---

## 3️⃣ TECHNICAL ARCHITECTURE {#technical-architecture}

### High-Level Architecture

```
┌─────────────────────────────────────┐
│   Vue 3 PWA (Customer UI)           │
│   - Billing, Invoices               │
│   - Daily business operations       │
└──────────────┬──────────────────────┘
               │ API (JSON)
               ↓
┌─────────────────────────────────────┐
│   Laravel API (Single App)          │
│    ├── Tenant Management (CORE)     │
│    ├── SaaS & Subscriptions         │
│    ├── Feature Flags                │
│    ├── Billing                      │
│    ├── Ledger                       │
│    ├── Inventory                    │
│    ├── Payments                     │
│    └── Reports                      │
└──────────────┬──────────────────────┘
               ↑ Session Auth
               │
┌──────────────┴──────────────────────┐
│   Laravel Blade (Internal Admin UI) │
│   - Tenant management               │
│   - Plans & subscriptions           │
│   - Support & operations            │
└─────────────────────────────────────┘

        ↓
PostgreSQL (Ledger-based, Multi-tenant)
        ↓
Redis (Cache, Queue, Feature Flags)
        ↓
Object Storage (Branding, Temp Files)
```

> **Two UIs, Same Backend** - Vue PWA for customers, Laravel Blade for internal admin

---

## 4️⃣ TECHNOLOGY STACK {#technology-stack}

### Frontend Stack (LOCKED - Web Only)

| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue 3** | Latest | Core framework |
| **Vite** | Latest | Build tool |
| **TypeScript** | Latest | Type safety |
| **Pinia** | Latest | State management |
| **Vue Router** | Latest | Routing |
| **PWA** | - | Service Worker, offline support |
| **IndexedDB (Dexie)** | Latest | Local storage |
| **Tailwind CSS** | Latest | Styling framework |

#### Frontend Responsibilities

- Business selection UI
- Store active `business_id`
- Offline-first data writes
- Feature-gated UI rendering
- Sync status indicators

> ❗ **Frontend never enforces access** - Backend always validates tenant + feature access

---

### Internal Admin UI Stack (LOCKED)

| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel Blade** | - | Server-rendered templates |
| **Tailwind CSS** | Latest | Styling (same as Vue PWA) |
| **Alpine.js** | Latest (optional) | Minimal interactivity |
| **Session Auth** | - | Traditional web auth |

#### Internal UI Responsibilities

- Tenant (business) management
- User management (platform-level)
- Subscription & plan management
- Feature flag configuration
- Support operations
- System monitoring
- Impersonation interface

> ❗ **Internal UI is NOT for customers** - Only for platform administrators and support staff

---

### Dual UI Architecture (CRITICAL)

**You have TWO DIFFERENT UIs:**

| UI | Purpose | Tech | Auth | Users |
|----|---------|------|------|-------|
| **Customer UI** | Billing, invoices, daily usage | Vue 3 PWA | API tokens (Sanctum) | Business users |
| **Internal UI** | Tenant mgmt, plans, support, ops | Laravel Blade | Session auth | Internal staff |

**They must never mix.**

#### Architecture Flow

```
Vue 3 PWA (Customers)
        ↓ JSON API
Laravel API (Same Backend)
        ↑ Session Auth
Laravel Internal UI (Admin)
```

- Same backend
- Different entry points
- Different guards
- Different routing

---

### Why Blade for Internal UI?

✅ **Lightweight** - No SPA complexity  
✅ **No frontend coupling** - Independent from Vue PWA  
✅ **Easy to secure** - Session-based, CSRF protected  
✅ **Easy to maintain** - Server-rendered, no build step  
✅ **Fast to build** - No state management needed  

❌ **DO NOT USE:**
- Starter Kits (Breeze, Jetstream) - Not tenant-aware
- Inertia.js - Unnecessary complexity
- Livewire - Not needed for admin UI

> **Use Plain Blade + Tailwind** - Simple, secure, maintainable

---

### Internal UI Structure

```
app/
 ├── Modules/
 │    ├── Tenant/
 │    ├── Billing/
 │    ├── Ledger/
 │    ├── SaaS/
 │    └── Internal/
 │         ├── Controllers/
 │         │    ├── DashboardController.php
 │         │    ├── TenantController.php
 │         │    ├── PlanController.php
 │         │    └── UserController.php
 │         └── Middleware/
 │              └── InternalOnly.php

resources/
 ├── views/
 │    ├── internal/
 │    │    ├── layouts/
 │    │    │    ├── app.blade.php
 │    │    │    └── guest.blade.php
 │    │    ├── auth/
 │    │    │    ├── login.blade.php
 │    │    │    └── forgot-password.blade.php
 │    │    ├── dashboard.blade.php
 │    │    ├── tenants/
 │    │    │    ├── index.blade.php
 │    │    │    ├── show.blade.php
 │    │    │    └── edit.blade.php
 │    │    ├── plans/
 │    │    ├── subscriptions/
 │    │    └── users/

routes/
 ├── api.php        // Vue PWA routes
 ├── internal.php   // Internal admin routes
 └── web.php        // Redirects only
```

---

### Internal UI Routing Strategy

```php
// routes/internal.php

// Guest routes (login)
Route::middleware(['web'])->prefix('internal')->group(function () {
    Route::get('/login', [InternalAuthController::class, 'showLogin'])
        ->name('internal.login');
    Route::post('/login', [InternalAuthController::class, 'login']);
    Route::post('/logout', [InternalAuthController::class, 'logout'])
        ->name('internal.logout');
});

// Authenticated internal routes
Route::middleware(['web', 'auth', 'internal.only'])
    ->prefix('internal')
    ->name('internal.')
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [InternalDashboardController::class, 'index'])
            ->name('dashboard');
        
        // Tenant management
        Route::resource('tenants', InternalTenantController::class);
        Route::post('tenants/{id}/suspend', [InternalTenantController::class, 'suspend'])
            ->name('tenants.suspend');
        Route::post('tenants/{id}/activate', [InternalTenantController::class, 'activate'])
            ->name('tenants.activate');
        
        // User management
        Route::resource('users', InternalUserController::class);
        Route::post('users/{id}/impersonate', [InternalUserController::class, 'impersonate'])
            ->name('users.impersonate');
        
        // Plan & subscription management
        Route::resource('plans', InternalPlanController::class);
        Route::resource('subscriptions', InternalSubscriptionController::class);
        
        // Feature flags
        Route::resource('features', InternalFeatureController::class);
        
        // Support
        Route::get('support/tickets', [InternalSupportController::class, 'tickets'])
            ->name('support.tickets');
        
});
```

---

### Internal UI Authentication

**Same `users` table, different guard:**

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

**Internal access middleware:**

```php
// app/Modules/Internal/Middleware/InternalOnly.php
public function handle($request, Closure $next)
{
    $user = $request->user();
    
    // Check if user has internal access
    $internalUser = InUser::where('user_id', $user->id)
        ->where('status', 'active')
        ->first();
    
    if (!$internalUser) {
        abort(403, 'Internal access required');
    }
    
    // Set internal context
    $request->merge(['internal_user' => $internalUser]);
    
    return $next($request);
}
```

---

### Styling Strategy (Consistency)

**Use the SAME design system:**

- ✅ Tailwind CSS (same config)
- ✅ Design tokens (same colors, spacing)
- ✅ Color palette (same brand colors)

**But keep separate:**

- ❌ Do not share Vue components
- ❌ Do not share state management
- ❌ Do not share routing
- ❌ Do not share build process

**Result:** Visual consistency, architectural separation

---

### Internal UI Pages (Minimum)

| Page | Route | Purpose |
|------|-------|---------|
| **Login** | `/internal/login` | Internal staff authentication |
| **Dashboard** | `/internal/dashboard` | Overview, metrics, alerts |
| **Tenants** | `/internal/tenants` | List all businesses |
| **Tenant Detail** | `/internal/tenants/{id}` | Business details, subscription, users |
| **Users** | `/internal/users` | Platform user management |
| **Plans** | `/internal/plans` | Subscription plan management |
| **Subscriptions** | `/internal/subscriptions` | Active subscriptions, billing |
| **Features** | `/internal/features` | Feature flag management |
| **Support** | `/internal/support/tickets` | Support ticket management |

---

### 🔒 INTERNAL UI LOCK STATEMENT

✅ **Laravel Blade** - Server-rendered, session-based  
✅ **Same Tailwind CSS** - Visual consistency  
✅ **Separate routing** - `/internal/*` prefix  
✅ **Internal.only middleware** - Access control  
✅ **No starter kits** - Custom, tenant-aware  
✅ **No SPA complexity** - Simple, maintainable  

This is the **mature, enterprise-safe approach** used by serious SaaS platforms.

---

### Backend Stack (LOCKED)

| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 12+ | API framework |
| **PHP** | 8.2+ | Programming language |
| **REST API** | - | API-first architecture |
| **Event System** | - | Event-driven services |
| **Queue Workers** | - | Async processing |
| **Laravel Horizon** | - | Queue monitoring |

#### Backend Responsibilities

- Tenant (Business) lifecycle
- User ↔ Business membership
- Subscription & plan enforcement
- Feature flag evaluation
- Ledger & accounting rules
- Offline sync validation
- Event-driven processing

---

### Database Stack (LOCKED)

| Technology | Version | Purpose |
|------------|---------|---------|
| **PostgreSQL** | 15+ | Primary database |
| **UUID** | - | Primary keys |
| **Soft Deletes** | - | Data retention |
| **JSONB** | - | Meta columns, flexibility |

#### Database Rules

- UUID primary keys
- Soft deletes everywhere
- `business_id` indexed on all core tables
- Ledger-based accounting
- JSONB meta columns for extensibility
- ACID transactions only
- Row-level tenant isolation

---

### Cache & Queue Stack (LOCKED)

| Technology | Purpose |
|------------|---------|
| **Redis** | Cache, Queue, Feature Flags |
| **Laravel Horizon** | Queue management & monitoring |

#### Used For

- Feature flag caching
- Subscription checks
- Background jobs
- PDF & report generation
- Offline sync queues

---

### Storage Stack (LOCKED)

| Type | Purpose |
|------|---------|
| **S3-compatible** | Branding assets, temp files |

#### Storage Rules

- Branding logos (max 2 per business)
- Temporary files auto-delete after 5 days
- No generic uploads initially
- Tenant-scoped paths: `/branding/{business_id}/`

---

### Final Stack Summary

```
Backend     → Laravel 12+ (API, Single App)
Tenant Mgmt → CORE Module (Same App)
Database    → PostgreSQL (Multi-tenant, Ledger-based)
Cache       → Redis
Queue       → Laravel Queues + Horizon
Storage     → S3-compatible (Tenant-aware)
Frontend    → Vue 3 PWA (Web Only)
Styling     → Tailwind CSS
```

---

## 5️⃣ MULTI-TENANCY MODEL {#multi-tenancy-model}

### Tenant Definition

- **Business = Tenant**
- One user can belong to multiple businesses
- All business data isolated via `business_id`
- Tenant resolved per request

### Tenant Management (CORE MODULE)

**Tenant management is a CORE module within the same Laravel app** - not a separate service.

```
Modules/Tenant
 ├── Businesses
 ├── BusinessUsers
 ├── Plans
 ├── Subscriptions
 ├── FeatureFlags
 ├── UsageLimits
 └── TenantContext
```

❌ No separate tenant service  
✅ Part of the same Laravel app

### Core Tenant Tables

```sql
businesses
business_users
plans
subscriptions
features
plan_features
business_feature_overrides
```

### Tenant Resolution Flow

```
Incoming Request
    ↓
Authentication
    ↓
Resolve Active Business
    ↓
Validate Membership
    ↓
Load Subscription
    ↓
Load Feature Flags
    ↓
Proceed to Controller
```

- Middleware-based
- Cached in Redis
- Enforced server-side only

### Mandatory Rules

✅ **MUST DO:**
- Every core table has `business_id` column
- All queries filtered by `business_id`
- Feature access evaluated per business
- Cross-business access strictly blocked
- Global query scopes on all models

❌ **NEVER DO:**
- Share data across businesses
- Allow cross-business queries
- Store business data without `business_id`

### Tenant Isolation Strategy

- **Single database**
- **Row-level isolation**
- `business_id` in every core table
- Global query scopes
- Strict foreign keys

```php
// Laravel global scope
static::addGlobalScope('business', fn ($q) =>
    $q->where('business_id', tenant()->id())
);
```

### Data Isolation

```sql
-- Every query must include business_id
SELECT * FROM invoices 
WHERE business_id = :current_business_id;

-- Enforced at middleware level
-- Enforced at ORM level (global scopes)
```

---

## 6️⃣ AUTHENTICATION & USER MANAGEMENT {#authentication-user-management}

### 🚨 KEY RULE (NON-NEGOTIABLE)

#### ❌ `in_users` MUST NOT be an auth table

#### ✅ `users` is the **ONLY authentication source**

> Even internal users **must authenticate via `users`**  
> `in_users` is a **profile / privilege table**, not identity.

---

### Authentication Architecture

This system uses a **dual-user model** for clean separation of concerns:

1. **`users`** - API authentication (single source of truth)
2. **`in_users`** - Platform access profile (tenant management system)
3. **`business_users`** - Tenant membership (business-level access)

---

### 1️⃣ `users` Table — API AUTH (SINGLE SOURCE OF TRUTH)

```sql
users
  - id (UUID, PK)
  - email (VARCHAR, UNIQUE)
  - password (VARCHAR, hashed)
  - name (VARCHAR)
  - status (ENUM: active, suspended)
  - email_verified_at (TIMESTAMP, nullable)
  - last_login_at (TIMESTAMP, nullable)
  - meta (JSONB)
  - created_at (TIMESTAMP)
  - updated_at (TIMESTAMP)
  - deleted_at (TIMESTAMP, nullable)
```

#### Used For

- ✅ API login (Vue PWA)
- ✅ Token generation (Laravel Sanctum / JWT)
- ✅ Password reset
- ✅ Email verification
- ✅ MFA (future)

#### NOT Used For

- ❌ No tenant logic
- ❌ No roles
- ❌ No platform privileges
- ❌ No business_id

---

### 2️⃣ `in_users` Table (internal_users) — PLATFORM ACCESS PROFILE

```sql
in_users
  - id (UUID, PK)
  - user_id (UUID, FK → users.id, UNIQUE)
  - access_level (ENUM: super_admin, support, ops, finance)
  - permissions (JSONB)
  - status (ENUM: active, suspended)
  - last_access_at (TIMESTAMP, nullable)
  - meta (JSONB)
  - created_at (TIMESTAMP)
  - updated_at (TIMESTAMP)
  - deleted_at (TIMESTAMP, nullable)
```

#### Used For

- ✅ Laravel **tenant management system**
- ✅ SaaS admin panel
- ✅ Cross-tenant access
- ✅ Support operations
- ✅ Feature overrides
- ✅ Impersonation

#### NOT Used For

- ❌ No password
- ❌ No login
- ❌ No auth tokens
- ❌ No direct authentication

> **Critical:** `in_users` is a **profile table** that extends `users`, not a separate auth system.

---

### 3️⃣ `business_users` Table — TENANT MEMBERSHIP

```sql
business_users
  - id (UUID, PK)
  - business_id (UUID, FK → businesses.id)
  - user_id (UUID, FK → users.id)
  - role (ENUM: owner, admin, staff, accountant)
  - status (ENUM: active, invited, suspended)
  - joined_at (TIMESTAMP)
  - meta (JSONB)
  - created_at (TIMESTAMP)
  - updated_at (TIMESTAMP)
  - deleted_at (TIMESTAMP, nullable)
  
  UNIQUE(business_id, user_id)
```

#### Used For

- ✅ Business-level access control
- ✅ Role-based permissions within a business
- ✅ Multi-business membership
- ✅ Invitation management

---

### Authentication & Access Flows

#### 🔐 LOGIN (API — ALL USERS)

```
Email + Password
   ↓
Authenticate via users table
   ↓
Validate status (active)
   ↓
Issue API token (Sanctum)
   ↓
Return token to Vue PWA
```

**Implementation:**

```php
// POST /api/login
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    
    if (!Auth::attempt($credentials)) {
        throw new AuthenticationException();
    }
    
    $user = Auth::user();
    $token = $user->createToken('api-token')->plainTextToken;
    
    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
}
```

---

#### 🏢 TENANT (BUSINESS) ROUTES

```
auth:sanctum
   ↓
Resolve business_id (from request header/context)
   ↓
Check business_users table
   ↓
Validate membership & role
   ↓
Check subscription & features
   ↓
Allow access to business data
```

**Middleware Flow:**

```php
// Middleware: tenant.context
public function handle($request, Closure $next)
{
    $businessId = $request->header('X-Business-ID');
    $user = $request->user();
    
    // Validate membership
    $membership = BusinessUser::where('business_id', $businessId)
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->first();
    
    if (!$membership) {
        throw new UnauthorizedException();
    }
    
    // Set tenant context
    app()->instance('tenant', Business::find($businessId));
    app()->instance('tenant.role', $membership->role);
    
    return $next($request);
}
```

---

#### 🛠️ INTERNAL (TENANT MANAGEMENT) ROUTES

```
auth:sanctum
   ↓
Check in_users exists for user_id
   ↓
Validate access_level
   ↓
Authorize internal permissions
   ↓
Allow platform-level access
```

**Middleware Flow:**

```php
// Middleware: internal.only
public function handle($request, Closure $next)
{
    $user = $request->user();
    
    // Check if user has internal access
    $internalUser = InUser::where('user_id', $user->id)
        ->where('status', 'active')
        ->first();
    
    if (!$internalUser) {
        throw new ForbiddenException('Internal access required');
    }
    
    // Set internal context
    app()->instance('internal.user', $internalUser);
    
    return $next($request);
}
```

> **Note:** Internal users **do not need business_id** - they operate at **platform level**

---

### Middleware Structure (RECOMMENDED)

```php
// routes/api.php

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // User profile routes
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/my-businesses', [UserController::class, 'businesses']);
    
    // Tenant (Business) routes
    Route::middleware(['tenant.context'])->prefix('business')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::apiResource('invoices', InvoiceController::class);
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('products', ProductController::class);
        // ... all business-scoped routes
    });
    
    // Internal platform routes
    Route::middleware(['internal.only'])->prefix('internal')->group(function () {
        Route::get('/businesses', [InternalBusinessController::class, 'index']);
        Route::post('/businesses/{id}/suspend', [InternalBusinessController::class, 'suspend']);
        Route::post('/users/{id}/impersonate', [InternalUserController::class, 'impersonate']);
        Route::get('/subscriptions', [InternalSubscriptionController::class, 'index']);
        // ... all platform-level routes
    });
    
});
```

---

### User Type Matrix

| User Type | `users` | `in_users` | `business_users` | Access Level |
|-----------|---------|------------|------------------|-------------|
| **Regular Business User** | ✅ | ❌ | ✅ | Business data only |
| **Business Owner** | ✅ | ❌ | ✅ (role: owner) | Full business control |
| **Internal Support** | ✅ | ✅ (support) | ❌ | Platform-level, read-only |
| **Internal Admin** | ✅ | ✅ (super_admin) | ❌ | Full platform control |
| **Hybrid User** | ✅ | ✅ | ✅ | Both business + platform |

---

### Why This Works (And Others Fail)

| Concern | This Model |
|---------|------------|
| **One auth system** | ✅ Single `users` table |
| **API-only login** | ✅ No duplicate passwords |
| **Internal admin panel** | ✅ `in_users` profile |
| **Tenant isolation** | ✅ `business_users` membership |
| **Support & ops access** | ✅ Platform-level via `in_users` |
| **No password duplication** | ✅ Only in `users` |
| **Secure & auditable** | ✅ Clear separation |
| **Scalable** | ✅ Clean architecture |

---

### 🚫 WHAT YOU MUST NEVER DO

❌ **`in_users` with passwords** - Authentication only via `users`  
❌ **Separate login flows** - Single auth endpoint  
❌ **`in_users` bypassing auth** - Always authenticate first  
❌ **Business logic inside `users`** - Keep auth separate  
❌ **Platform roles inside `business_users`** - Use `in_users` for platform access  
❌ **Hardcoded role checks** - Use permission system  

---

### Access Level Definitions

#### Internal User Access Levels

```php
// in_users.access_level ENUM

'super_admin' => [
    'manage_all_businesses',
    'manage_all_users',
    'manage_subscriptions',
    'manage_plans',
    'manage_features',
    'view_all_data',
    'impersonate_users',
    'system_settings'
],

'support' => [
    'view_businesses',
    'view_users',
    'view_subscriptions',
    'impersonate_users', // for troubleshooting
    'view_tickets'
],

'ops' => [
    'manage_subscriptions',
    'view_businesses',
    'view_users',
    'manage_billing'
],

'finance' => [
    'view_subscriptions',
    'view_payments',
    'export_financial_reports',
    'manage_invoicing'
]
```

---

### Impersonation Flow (Support Feature)

```php
// POST /api/internal/users/{userId}/impersonate
public function impersonate(Request $request, $userId)
{
    // Verify internal user has permission
    $internalUser = $request->get('internal.user');
    
    if (!in_array($internalUser->access_level, ['super_admin', 'support'])) {
        throw new ForbiddenException();
    }
    
    // Get target user
    $targetUser = User::findOrFail($userId);
    
    // Create impersonation token
    $token = $targetUser->createToken('impersonation', ['impersonated'])->plainTextToken;
    
    // Log impersonation
    AuditLog::create([
        'action' => 'user_impersonated',
        'actor_id' => $request->user()->id,
        'target_id' => $userId,
        'meta' => ['reason' => $request->input('reason')]
    ]);
    
    return response()->json([
        'token' => $token,
        'user' => $targetUser,
        'impersonated' => true
    ]);
}
```

---

### 🔒 FINAL LOCK STATEMENT

✅ **`users`** = API authentication ONLY  
✅ **`in_users`** = Laravel tenant management / platform access  
✅ **`business_users`** = Tenant membership  
✅ **No duplicate auth** - Single source of truth  
✅ **Clean separation** - Auth vs. Authorization  
✅ **Enterprise-safe** - Auditable and scalable  

This is the **correct and only acceptable way** to implement the dual-user model.

---

### Roles & Authorization Strategy

#### 🔐 AUTHORIZATION MODEL (LOCKED)

**Use BOTH middleware AND policies** - for different responsibilities.

> **Middleware = Coarse access (WHO can enter)**  
> **Policy = Fine-grained access (WHAT they can do)**

This is the **correct, enterprise-grade approach**.

---

#### ❌ WHAT NOT TO DO

❌ Do everything in middleware  
❌ Do everything in policies  
❌ Put business logic in middleware  
❌ Hardcode roles everywhere  

All of these lead to **spaghetti authorization**.

---

#### 1️⃣ FIXED ROLES (DATA-LEVEL)

**Tenant Roles** (business scope)

```sql
-- business_users.role ENUM
owner
admin
staff
accountant
```

**Internal Roles** (platform scope)

```sql
-- in_users.access_level ENUM
super_admin
support
ops
finance
```

> Roles are **fixed enums**, not dynamic in Phase-1.

---

#### 2️⃣ MIDDLEWARE FOR ROLE GATES (ENTRY CONTROL)

**Purpose:**
- Decide **who is allowed to access a route group**
- Fast rejection
- No business logic

**Examples:**

```php
// Tenant routes - only owners and admins can access
Route::middleware(['tenant.context', 'role:owner,admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
});

// Internal routes - only super admins
Route::middleware(['internal.only', 'internal.role:super_admin'])->group(function () {
    Route::get('/tenants', [InternalBusinessController::class, 'index']);
    Route::post('/plans', [InternalPlanController::class, 'store']);
});
```

**Middleware answers:** *"Is this user allowed to be here at all?"*

---

#### 3️⃣ POLICIES FOR ACTION-LEVEL CONTROL (BUSINESS LOGIC)

**Purpose:**
- Decide **whether a user can perform an action on a resource**
- Uses data context
- Clean, testable

**Examples:**

```php
// InvoicePolicy
public function update(User $user, Invoice $invoice)
{
    // Owner/admin can update any invoice
    if ($user->hasRole('admin') || $user->hasRole('owner')) {
        return true;
    }
    
    // Staff can only update their own invoices
    if ($user->hasRole('staff') && $invoice->created_by === $user->id) {
        return true;
    }
    
    return false;
}

public function delete(User $user, Invoice $invoice)
{
    // Only owner/admin can delete
    // AND invoice must be in draft status
    return ($user->hasRole('owner') || $user->hasRole('admin'))
        && $invoice->status === 'draft';
}
```

**Policies answer:** *"Can this user do THIS action on THIS record?"*

---

#### Why This Hybrid Model Is Correct

| Concern | Middleware | Policy |
|---------|------------|--------|
| **Route access** | ✅ | ❌ |
| **Resource ownership** | ❌ | ✅ |
| **Business rules** | ❌ | ✅ |
| **Performance** | ✅ | ❌ |
| **Testability** | ❌ | ✅ |
| **Maintainability** | ❌ | ✅ |

**You need both.**

---

#### How This Fits the Tenant Model

**Where roles live:**
- Tenant roles → `business_users.role`
- Internal roles → `in_users.access_level`

**Resolution:**
- Middleware checks role existence
- Policy checks ownership + context + feature flags

---

#### Implementation Blueprint

**Middleware Types:**

```php
// 1. Tenant role middleware
'role:owner,admin'

// 2. Internal role middleware
'internal.role:super_admin,support'
```

**Policy Usage:**

| Resource | Policy | Example Methods |
|----------|--------|-----------------|
| Invoice | InvoicePolicy | view, create, update, delete, finalize |
| Payment | PaymentPolicy | view, create, update, delete, allocate |
| Product | ProductPolicy | view, create, update, delete, adjust_stock |
| Customer | CustomerPolicy | view, create, update, delete, view_ledger |
| Business | BusinessPolicy | view, update, delete, manage_users |

---

#### 🚫 STRICT RULES (IMPORTANT)

**Middleware MUST NOT:**

1. ❌ Perform complex DB queries (except role lookup)
2. ❌ Contain business logic
3. ❌ Check feature flags
4. ❌ Check ownership logic

**Policies CAN check:**

1. ✅ Ownership
2. ✅ Role
3. ✅ Subscription status
4. ✅ Feature flags
5. ✅ Business rules
6. ✅ Resource state

---

#### Complete Authorization Flow

```
Request
    ↓
auth:sanctum (authenticate)
    ↓
tenant.context (resolve business)
    ↓
role:owner,admin (middleware gate)
    ↓
Controller
    ↓
$this->authorize('update', $invoice) (policy)
    ↓
Business Logic
    ↓
Response
```

---

#### Example: Complete Route with Authorization

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {
    
    // All authenticated business users can view
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    
    // Only owner/admin/staff can create
    Route::middleware(['role:owner,admin,staff'])->group(function () {
        Route::post('/invoices', [InvoiceController::class, 'store']);
    });
    
    // Only owner/admin can delete
    Route::middleware(['role:owner,admin'])->group(function () {
        Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
    });
    
});

// InvoiceController
public function update(Request $request, Invoice $invoice)
{
    // Policy checks ownership + business rules
    $this->authorize('update', $invoice);
    
    // Business logic
    $invoice->update($request->validated());
    
    return response()->json($invoice);
}
```

---

#### 🔒 AUTHORIZATION LOCK STATEMENT

✅ **Fixed roles (enum-based)** - No dynamic roles in Phase-1  
✅ **Middleware for role gating** - WHO can access routes  
✅ **Policies for action authorization** - WHAT actions are allowed  
✅ **Tenant-safe** - Business isolation enforced  
✅ **Scalable** - Clean separation of concerns  
✅ **Testable** - Policies are unit-testable  

This approach is **clean, future-proof, and enterprise-correct**.

---

## 7️⃣ MODULE STRUCTURE {#module-structure}

### Core Modules (Always Present)

Modules are **independent but event-connected**.

| # | Module | Description |
|---|--------|-------------|
| 0 | **Tenant Management** | **CORE** - Businesses, subscriptions, feature flags |
| 1 | **Billing** | Invoices, quotes, credit notes |
| 2 | **Inventory** | Products, services, stock management |
| 3 | **Parties** | Customers, vendors, contacts |
| 4 | **Payments & Ledger** | Payment processing, ledger entries |
| 5 | **Tax Engine** | GST, VAT, tax calculations |
| 6 | **Reports** | Financial reports, analytics |
| 7 | **Users & Roles** | Authentication, authorization |
| 8 | **SaaS / Plans** | Subscriptions, feature flags (part of Tenant) |
| 9 | **Branding & Files** | Logo management, temp files |
| 10 | **System & Settings** | Configuration, preferences |

### Module Communication

```
┌─────────────┐
│   Billing   │ ──▶ InvoiceFinalized Event
└─────────────┘
       │
       ▼
┌─────────────┐
│   Ledger    │ ──▶ Creates ledger entries
└─────────────┘
       │
       ▼
┌─────────────┐
│  Inventory  │ ──▶ Deducts stock
└─────────────┘
```

---

## 8️⃣ FEATURE LEVEL STRATEGY {#feature-level-strategy}

### Level 0 – Core Foundation (Internal)

**Purpose:** Always exists, even if UI hidden

- Ledger engine
- Tax calculations
- Event system
- Feature flag engine

---

### Level 1 – MVP (Launch)

#### Billing Module
- GST / Non-GST invoices
- Draft → Final workflow
- PDF generation / Print
- Auto invoice numbering
- Basic templates

#### Inventory Module
- Products & services
- Stock in / stock out
- Auto stock deduction on invoice
- Low stock alerts

#### Parties Module
- Customers & Vendors
- Contact management
- Outstanding balance tracking
- Simple ledger view

#### Payments Module
- Cash / Bank / UPI
- Partial payments
- Due calculation
- Payment allocation

#### Reports Module
- Sales report
- Profit & Loss (basic)
- Stock summary
- Outstanding report
- Tax summary

#### System Module
- Single business
- Single user
- Backup & export
- Basic settings

---

### Level 2 – Paid Core

**Monetization starts here**

- ✅ Branding removal
- ✅ 2 custom logos per business
- ✅ Multi-business support
- ✅ Multi-device sync
- ✅ Advanced reports (custom date ranges, filters)
- ✅ Bulk import/export
- ✅ Credit limits for customers
- ✅ Advance payments
- ✅ Recurring invoices
- ✅ Payment reminders

---

### Level 3 – Advanced / Industry

**Industry-specific features**

- Barcode scanning
- Batch & expiry tracking
- POS integration
- E-Invoice / E-Way Bill (India)
- TDS / TCS (India)
- CRM features (lead tracking)
- Multi-currency (basic)
- Custom invoice templates

---

### Level 4 – Enterprise

**Large business features**

- Multi-branch management
- Advanced roles & approvals
- Third-party integrations
- White-label options
- API access
- SLA guarantees
- Dedicated support
- Custom workflows

---

## 9️⃣ SAAS & FEATURE MANAGEMENT {#saas-feature-management}

### Feature Control Model

Features are **data-driven, not code-driven**.

> ❌ **NEVER:** `if (plan == 'premium') { ... }`  
> ✅ **ALWAYS:** `if (hasFeature('multi_business')) { ... }`

### Core Tables

```sql
-- Plans definition
plans
  - id (UUID)
  - name
  - slug
  - price
  - billing_cycle
  - is_active
  - meta (JSONB)

-- Features catalog
features
  - id (UUID)
  - name
  - slug
  - category
  - is_active
  - meta (JSONB)

-- Plan-Feature mapping
plan_features
  - id (UUID)
  - plan_id
  - feature_id
  - limit (nullable, for quotas)
  - meta (JSONB)

-- Business subscriptions
subscriptions
  - id (UUID)
  - business_id
  - plan_id
  - status
  - starts_at
  - ends_at
  - meta (JSONB)

-- Feature overrides (for custom deals)
business_feature_overrides
  - id (UUID)
  - business_id
  - feature_id
  - is_enabled
  - limit (nullable)
  - expires_at (nullable)
  - meta (JSONB)
```

### Feature Evaluation Flow

```
User Request
    ↓
Business Context
    ↓
Active Subscription
    ↓
Plan Features + Overrides
    ↓
Feature Flag Check
    ↓
UI + API Response
```

### Implementation Example

```php
// Backend
if (!$business->hasFeature('multi_business')) {
    throw new FeatureNotAvailableException();
}

// Frontend
if (hasFeature('advanced_reports')) {
    // Show advanced reports UI
}
```

---

## 🔟 DATABASE DESIGN PRINCIPLES {#database-design-principles}

### Mandatory Rules (LOCKED)

✅ **MUST HAVE:**

1. **UUID primary keys** - No auto-increment integers
2. **Soft deletes everywhere** - `deleted_at` column
3. **Meta JSONB column** - For future extensibility
4. **Foreign keys** - Enforce referential integrity
5. **Ledger-based accounting** - Immutable entries
6. **business_id** - On every core table
7. **Timestamps** - `created_at`, `updated_at`

### Schema Pattern

```sql
CREATE TABLE example_table (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    business_id UUID NOT NULL REFERENCES businesses(id),
    
    -- Core columns
    name VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    
    -- Extensibility
    meta JSONB DEFAULT '{}',
    
    -- Audit
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    CONSTRAINT fk_business FOREIGN KEY (business_id) 
        REFERENCES businesses(id) ON DELETE CASCADE
);

CREATE INDEX idx_example_business ON example_table(business_id);
CREATE INDEX idx_example_deleted ON example_table(deleted_at);
```

### Forbidden Forever

❌ **NEVER DO:**

1. **Totals stored in DB** - Always calculate from ledger
2. **Payments inside invoice table** - Separate payments table
3. **Hardcoded tax logic** - Use tax engine
4. **Plan logic inside UI** - Use feature flags
5. **Denormalized data** - Except for performance (with justification)
6. **Auto-increment IDs** - Use UUIDs
7. **Hard deletes** - Always soft delete

---

## 1️⃣1️⃣ ACCOUNTING & LEDGER MODEL {#accounting-ledger-model}

### Core Principle

**Double-entry bookkeeping** - Every transaction has equal debits and credits

### Core Tables

```sql
-- Chart of accounts
ledgers
  - id (UUID)
  - business_id (UUID)
  - code (VARCHAR) -- e.g., "1000", "2000"
  - name (VARCHAR) -- e.g., "Cash", "Sales"
  - type (ENUM) -- asset, liability, equity, income, expense
  - parent_id (UUID, nullable) -- for hierarchy
  - is_system (BOOLEAN) -- cannot be deleted
  - meta (JSONB)
  - timestamps

-- All financial transactions
ledger_entries
  - id (UUID)
  - business_id (UUID)
  - ledger_id (UUID)
  - transaction_id (UUID) -- groups debit/credit pairs
  - transaction_type (VARCHAR) -- invoice, payment, etc.
  - transaction_reference_id (UUID) -- invoice_id, payment_id
  - entry_type (ENUM) -- debit, credit
  - amount (DECIMAL(15,2))
  - entry_date (DATE)
  - description (TEXT)
  - meta (JSONB)
  - timestamps
  - deleted_at

-- Payments
payments
  - id (UUID)
  - business_id (UUID)
  - party_id (UUID)
  - payment_type (ENUM) -- received, paid
  - payment_method (ENUM) -- cash, bank, upi, card
  - amount (DECIMAL(15,2))
  - payment_date (DATE)
  - reference_number (VARCHAR)
  - notes (TEXT)
  - meta (JSONB)
  - timestamps
  - deleted_at

-- Payment allocations (links payments to invoices)
payment_allocations
  - id (UUID)
  - business_id (UUID)
  - payment_id (UUID)
  - invoice_id (UUID)
  - allocated_amount (DECIMAL(15,2))
  - meta (JSONB)
  - timestamps
  - deleted_at
```

### Key Events

```php
// Invoice finalized
InvoiceFinalized
  → Create ledger entries (Debit: Customer, Credit: Sales)
  → Update inventory (if applicable)

// Payment received
PaymentReceived
  → Create ledger entries (Debit: Cash/Bank, Credit: Customer)
  → Allocate to invoices
  → Update outstanding balances

// Credit note issued
CreditNoteIssued
  → Reverse ledger entries
  → Update inventory

// Stock adjusted
StockAdjusted
  → Create ledger entries (inventory adjustment)
```

### Ledger Integrity Rules

1. **Every transaction must balance** - Sum of debits = Sum of credits
2. **Immutable entries** - Never update, only create new entries
3. **Audit trail** - Every entry linked to source transaction
4. **Reports derived from ledger** - No separate summary tables

---

## 1️⃣2️⃣ FILE & BRANDING MANAGEMENT {#file-branding-management}

### Branding Assets

#### Rules

- **Max 2 logo files per business**
- Types: `logo_primary`, `logo_secondary`
- **No overwrite allowed** - Must delete then upload
- Formats: PNG, JPG, SVG
- Max size: 2MB per file

#### Table Schema

```sql
business_branding_assets
  - id (UUID)
  - business_id (UUID)
  - type (ENUM) -- logo_primary, logo_secondary
  - file_path (VARCHAR)
  - mime_type (VARCHAR)
  - file_size (INTEGER) -- bytes
  - created_at (TIMESTAMP)
  - deleted_at (TIMESTAMP)
```

#### Storage Path

```
/storage/branding/{business_id}/logo_primary.png
/storage/branding/{business_id}/logo_secondary.svg
```

---

### Temporary Files

#### Purpose

- Invoice PDFs
- Report exports
- Generated documents

#### Rules

- **Auto-delete after 5 days**
- No permanent linking
- Regenerate if needed
- Daily cleanup job

#### Table Schema

```sql
temp_files
  - id (UUID)
  - business_id (UUID)
  - purpose (VARCHAR) -- invoice_pdf, report_export
  - file_path (VARCHAR)
  - expires_at (TIMESTAMP)
  - created_at (TIMESTAMP)
```

#### Cleanup Process

```php
// Daily scheduled job
TempFileCleanupJob
  → Find files where expires_at < now()
  → Delete from storage
  → Delete from database
```

---

## 1️⃣3️⃣ UI/UX DESIGN SYSTEM {#ui-ux-design-system}

### Design Philosophy

Finance apps must be:
- **Calm & trustworthy** - Not flashy
- **Structured & predictable** - Consistent layouts
- **Dense data friendly** - Tables, forms, numbers
- **Connected** - Cohesive across screens
- **Offline + mobile** - PWA-first
- **Age well** - 5–10 year lifespan

### CSS Framework (LOCKED)

**Tailwind CSS** ⭐⭐⭐⭐⭐

#### Why Tailwind?

✅ Utility-first → consistent spacing & layout  
✅ Small bundle → great for PWA  
✅ Easy design system enforcement  
✅ Perfect Vue 3 integration  
✅ Long-term stable  
✅ Easy dark mode  
✅ Responsive control  

#### Component Strategy

```
Tailwind CSS
    +
Design Tokens (custom)
    +
Headless UI / Radix
    +
ShadCN-style components
```

---

### Design Tokens (MANDATORY)

All components must use tokens, never hardcoded values.

```css
/* Spacing */
--spacing-xs: 0.25rem;  /* 4px */
--spacing-sm: 0.5rem;   /* 8px */
--spacing-md: 1rem;     /* 16px */
--spacing-lg: 1.5rem;   /* 24px */
--spacing-xl: 2rem;     /* 32px */

/* Border Radius */
--radius-sm: 0.25rem;
--radius-md: 0.5rem;
--radius-lg: 0.75rem;

/* Colors - Light Mode */
--color-primary: #3b82f6;
--color-surface: #ffffff;
--color-border: #e5e7eb;
--color-text-primary: #111827;
--color-text-secondary: #6b7280;

/* Colors - Dark Mode */
--color-primary-dark: #60a5fa;
--color-surface-dark: #1f2937;
--color-border-dark: #374151;
--color-text-primary-dark: #f9fafb;
--color-text-secondary-dark: #9ca3af;
```

---

### Layout Structure (CRITICAL)

**Fixed structure everywhere - never break this:**

```
┌─────────────────────────────────────────┐
│           App Shell                     │
│  ┌──────┬───────────────────────────┐   │
│  │      │  Topbar (context aware)   │   │
│  │      ├───────────────────────────┤   │
│  │ Side │  ┌─────────────────────┐  │   │
│  │ bar  │  │  Page Header        │  │   │
│  │      │  ├─────────────────────┤  │   │
│  │(sta- │  │  Filters / Actions  │  │   │
│  │ ble) │  ├─────────────────────┤  │   │
│  │      │  │                     │  │   │
│  │      │  │  Main Content       │  │   │
│  │      │  │                     │  │   │
│  │      │  └─────────────────────┘  │   │
│  └──────┴───────────────────────────┘   │
└─────────────────────────────────────────┘
```

---

### Spacing & Grid System

- **8px grid system** - All spacing in multiples of 8px
- **Consistent margins** - Same spacing patterns
- **Same card padding** - 16px or 24px everywhere

---

### Component Hierarchy (STRICT)

**Only these building blocks:**

| Component | Purpose |
|-----------|---------|
| **Button** | Primary, secondary, ghost, danger |
| **Input** | Text, number, date, select |
| **Select** | Dropdown, multi-select |
| **Table** | Data tables with sorting, filtering |
| **Card** | Content containers |
| **Modal** | Dialogs, confirmations |
| **Drawer** | Side panels, forms |
| **Toast** | Notifications |
| **Badge** | Status indicators |
| **Tabs** | Navigation within pages |

**No custom random styles per page.**

---

### Tables & Forms (BILLING UI CORE)

#### Tables

- ✅ Sticky headers
- ✅ Column alignment (numbers right-aligned)
- ✅ Zebra rows (subtle)
- ✅ Action column consistent (right-most)
- ✅ Hover states
- ✅ Loading states

#### Forms

- ✅ Labels above inputs
- ✅ Inline validation
- ✅ Grouped sections
- ✅ Keyboard-first navigation
- ✅ Clear error states
- ✅ Auto-save indicators

---

### Dark Mode (TRUST BUILDER)

Finance users LOVE dark mode.

```html
<html class="dark">
```

**Dark mode requirements:**
- Low contrast (easier on eyes)
- Soft borders
- Same layout as light mode
- User preference saved
- System preference detection

---

### Color & Visual Style

#### Avoid

❌ Bright gradients  
❌ Loud colors  
❌ Heavy shadows  
❌ Animations everywhere  

#### Use

✅ Neutral base (grays)  
✅ One strong primary color  
✅ Soft borders  
✅ Subtle shadows  
✅ Purposeful animations  

**Trust > Wow**

---

### Responsive Strategy

#### Desktop (≥1024px)
- Sidebar layout
- Dense tables
- Multi-column forms

#### Tablet (768px - 1023px)
- Collapsible sidebar
- Simplified tables
- Two-column forms

#### Mobile (≤767px)
- Bottom navigation
- Slide-over drawers
- Card-based lists (instead of tables)
- Single-column forms

**Same components, different layout.**

---

## 1️⃣4️⃣ PWA & OFFLINE REQUIREMENTS {#pwa-offline-requirements}

### Offline-First Strategy

```
User Action
    ↓
Write to IndexedDB (immediate)
    ↓
Show success to user
    ↓
Queue for sync (background)
    ↓
Sync when online
    ↓
Resolve conflicts (if any)
```

### Offline Rules

1. **Read from IndexedDB first** - API as fallback
2. **Write to IndexedDB immediately** - Queue for sync
3. **Sync when online** - Background sync
4. **Conflict resolution** - Last-write-wins with timestamp

### Must Work Offline

✅ Invoice creation  
✅ Customer management  
✅ Product selection  
✅ Payment recording  
✅ Stock updates  
✅ Basic reports  

### Requires Online

❌ PDF generation (can queue)  
❌ Multi-device sync  
❌ Subscription changes  
❌ User management  

### Sync Strategy

```javascript
// IndexedDB schema
{
  invoices: {
    id: 'uuid',
    business_id: 'uuid',
    data: {...},
    sync_status: 'pending' | 'synced' | 'conflict',
    last_modified: timestamp,
    server_version: timestamp
  }
}

// Sync process
1. Check online status
2. Get pending items from IndexedDB
3. Send to API
4. Handle conflicts (server timestamp vs local)
5. Update IndexedDB with server response
6. Mark as synced
```

### Conflict Resolution

```
IF local_timestamp > server_timestamp
  → Server wins (safer for accounting)
  → Show conflict notification to user
  → Allow manual resolution
ELSE
  → Accept server version
```

---

## 1️⃣5️⃣ SECURITY REQUIREMENTS {#security-requirements}

### Authentication

- JWT tokens
- Refresh token rotation
- Session management
- Multi-device support
- Logout from all devices

### Authorization

- Role-based access control (RBAC)
- Permission-based actions
- Business-level isolation
- Feature-based access

### Data Security

- Encrypted sensitive fields (tax IDs, bank details)
- HTTPS only
- Secure cookie flags
- CSRF protection
- XSS prevention

### API Security

- Rate limiting (per user, per business)
- Request validation
- SQL injection prevention
- Input sanitization

### Audit Logs (Future)

- User actions
- Data changes
- Login attempts
- Failed authorization

---

## 1️⃣6️⃣ PERFORMANCE REQUIREMENTS {#performance-requirements}

### Response Time Targets

| Action | Target | Max |
|--------|--------|-----|
| Invoice save | < 1s | 3s |
| Page load | < 2s | 5s |
| API response | < 500ms | 2s |
| PDF generation | Async | 10s |
| Report generation | Async | 30s |

### Performance Rules

✅ **DO:**
- Async PDF generation
- Background report processing
- Lazy load components
- Paginate large lists
- Cache frequently accessed data
- Optimize database queries

❌ **DON'T:**
- Block UI for long operations
- Load all data at once
- N+1 queries
- Unoptimized images

### Zero Data Corruption

- Transaction-based operations
- Rollback on failure
- Validation before save
- Integrity checks

---

## 1️⃣7️⃣ BACKUP & DATA SAFETY {#backup-data-safety}

### Backup Strategy

- **Daily automated backups** - PostgreSQL dumps
- **Retention:** 30 days
- **Storage:** Encrypted, off-site
- **Testing:** Monthly restore tests

### Business-Level Export

- Export all business data (JSON/CSV)
- Include invoices, customers, products, payments
- Downloadable from UI
- On-demand generation

### Disaster Recovery

- **RTO (Recovery Time Objective):** < 4 hours
- **RPO (Recovery Point Objective):** < 24 hours
- Manual restore option (admin only)
- Documented recovery procedures

---

## 1️⃣8️⃣ DEPLOYMENT & DEVOPS {#deployment-devops}

### Infrastructure

- **OS:** Ubuntu LTS (22.04+)
- **Web Server:** Nginx
- **PHP:** PHP-FPM 8.2+
- **Process Manager:** Supervisor
- **Cache/Queue:** Redis
- **Database:** PostgreSQL 15+

### CI/CD Pipeline

```
GitHub Push
    ↓
GitHub Actions
    ↓
Run Tests
    ↓
Build Assets
    ↓
Deploy to Staging
    ↓
Manual Approval
    ↓
Deploy to Production (zero-downtime)
```

### Deployment Strategy

- **Zero-downtime deploys** - Blue-green or rolling
- **Database migrations** - Automated, reversible
- **Asset versioning** - Cache busting
- **Health checks** - Before switching traffic

### Monitoring

- Application logs
- Error tracking (Sentry or similar)
- Performance monitoring
- Uptime monitoring
- Queue monitoring (Horizon)

---

## 1️⃣9️⃣ OUT OF SCOPE {#out-of-scope}

### Explicitly NOT Included (Now)

❌ Generic file uploads  
❌ Document attachments  
❌ **Mobile native apps (iOS/Android)** - Web PWA only  
❌ **Desktop apps (Electron)** - Web PWA only  
❌ Marketplace / App store  
❌ AI features  
❌ Social features  
❌ Email marketing  
❌ Advanced CRM  
❌ Project management  
❌ Time tracking  

> **Platform Focus:** Web PWA ONLY - No React Native, no Electron, no native apps

> These features may be added in future phases based on user demand

---

## 2️⃣0️⃣ SUCCESS METRICS {#success-metrics}

### Product Metrics

| Metric | Target |
|--------|--------|
| **Zero data loss** | 100% |
| **Sync conflicts** | < 1% |
| **Ledger integrity** | 100% |
| **Uptime** | > 99.5% |
| **API response time** | < 500ms (p95) |

### Business Metrics

| Metric | Target |
|--------|--------|
| **Free → Paid conversion** | > 5% |
| **Churn rate** | < 3% monthly |
| **Feature adoption** | Track per feature |
| **Customer satisfaction** | > 4.5/5 |
| **Support tickets** | < 10% of users/month |

### Technical Metrics

| Metric | Target |
|--------|--------|
| **Test coverage** | > 80% |
| **Build time** | < 5 minutes |
| **Deploy frequency** | Daily (if needed) |
| **Mean time to recovery** | < 1 hour |

---

## 2️⃣1️⃣ LOCK STATEMENT {#lock-statement}

### This Document Freezes

✅ **Architecture** - High-level system design  
✅ **Core DB assumptions** - Ledger, multi-tenancy, UUIDs  
✅ **Feature system** - Data-driven feature flags  
✅ **Multi-tenancy model** - Business-based isolation  
✅ **Technology stack** - Vue 3 PWA (Web only), Laravel 12+, PostgreSQL, Tailwind  
✅ **Platform focus** - Web PWA only (no native mobile, no desktop)  

### This Document Allows

✅ **Feature expansion** - Without refactoring core  
✅ **New modules** - Following established patterns  
✅ **UI iterations** - Within design system  
✅ **Performance optimizations** - Without breaking contracts  

### Changes Requiring Architectural Review

Any change to:
- Ledger system
- Feature flag system
- Multi-tenancy model
- Database primary key strategy
- Offline sync strategy

**Must go through architectural review before implementation.**

---

## 🚀 NEXT STEPS

### Immediate (Phase 1)

1. ✅ Full ERD (tables + relations)
2. ✅ API contracts (OpenAPI spec)
3. ✅ Vue 3 PWA skeleton
4. ✅ Design system implementation
5. ✅ Sync conflict strategy

### Short-term (Phase 2)

1. Developer onboarding documentation
2. Testing strategy
3. Deployment scripts
4. Monitoring setup
5. User documentation

### Long-term (Phase 3)

1. Advanced features (Level 3+)
2. Mobile apps
3. Integrations
4. White-label options
5. Enterprise features

---

## 📞 DOCUMENT OWNERSHIP

**Maintained by:** Engineering Team  
**Approved by:** CTO / Product Lead  
**Review Cycle:** Quarterly  
**Last Review:** December 23, 2025  

---

**END OF DOCUMENT**
