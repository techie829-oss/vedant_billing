Below is a **FINAL, LOCKED, END-TO-END REQUIREMENT DOCUMENT** for your **scalable Billing & Accounting SaaS**.
This is written in **product + engineering language**, suitable for:

* Internal dev team
* CTO / architect review
* Future hiring
* Investor / partner discussions

You can treat this as **source of truth (SoT)**.

---

# 📘 BILLING & ACCOUNTING SAAS

## FULL REQUIREMENT DOCUMENT (FINAL)

---

## 1️⃣ PRODUCT OVERVIEW

### Product Type

Multi-tenant **Billing, Inventory & Accounting SaaS**
Offline-first, PWA-enabled, subscription-based

### Target Users

* Small & medium businesses
* Retailers, service providers
* Multi-location businesses (future)
* Accountants (future)

### Core Goals

* Zero data loss
* Stable accounting logic
* Feature-based SaaS monetization
* Minimal future DB changes
* Scale from **10 → 1M+ businesses**

---

## 2️⃣ HIGH-LEVEL ARCHITECTURE

```
PWA (Vue 3)
   ↓
API Gateway
   ↓
Laravel Modular Backend
   ↓
Event Bus + Queue
   ↓
PostgreSQL (Ledger-based)
   ↓
Object Storage (Branding + Temp)
```

---

## 3️⃣ TECH STACK (LOCKED)

### Frontend

* Vue 3
* Vite
* TypeScript
* Pinia (state)
* Vue Router
* PWA (Service Worker)
* IndexedDB (Dexie)

### Backend

* Laravel (PHP 8.2+)
* REST API (API-first)
* Event-driven services
* Queue workers

### Database

* PostgreSQL 15+
* UUID primary keys
* Soft deletes
* JSONB meta columns

### Cache & Queue

* Redis
* Laravel Horizon

### Storage

* Local / S3-compatible
* No generic uploads (initially)

---

## 4️⃣ MULTI-TENANCY MODEL

### Tenant Definition

* **Business = Tenant**
* One user can belong to multiple businesses
* All business data isolated via `business_id`

### Mandatory Rules

* Every core table has `business_id`
* Cross-business access strictly blocked
* Feature access evaluated per business

---

## 5️⃣ MODULE STRUCTURE (FINAL)

### Core Modules (Always Present)

1. Billing
2. Inventory
3. Parties (Customers/Vendors)
4. Payments & Ledger
5. Tax Engine
6. Reports
7. Users & Roles
8. SaaS / Plans
9. Branding & Files (limited)
10. System & Settings

Modules are **independent but event-connected**.

---

## 6️⃣ FEATURE LEVEL STRATEGY

### Level 0 – Core Foundation (Internal)

* Ledger engine
* Tax calculations
* Event system
* Feature flag engine

> Exists even if UI hidden.

---

### Level 1 – MVP (Launch)

#### Billing

* GST / Non-GST invoices
* Draft → Final workflow
* PDF / Print
* Invoice numbering

#### Inventory

* Products & services
* Stock in / stock out
* Auto stock deduction

#### Parties

* Customers & Vendors
* Outstanding balance
* Simple ledger view

#### Payments

* Cash / Bank / UPI
* Partial payments
* Due calculation

#### Reports

* Sales report
* Profit & Loss (basic)
* Stock summary
* Outstanding report

#### System

* Single business
* Single user
* Backup & export

---

### Level 2 – Paid Core

* Branding removal
* 2 logos per business
* Multi-business
* Multi-device sync
* Advanced reports
* Bulk import/export
* Credit limits
* Advance payments

---

### Level 3 – Advanced / Industry

* Barcode
* Batch & expiry
* POS
* E-Invoice / E-Way Bill
* TDS / TCS
* CRM features

---

### Level 4 – Enterprise

* Multi-branch
* Roles & approvals
* Integrations
* White-label
* API access
* SLA

---

## 7️⃣ SAAS & FEATURE MANAGEMENT (CRITICAL)

### Feature Control Model

Features are **data-driven**, not code-driven.

#### Core Tables

```
plans
features
plan_features
subscriptions
business_feature_overrides
```

### Feature Evaluation Flow

```
User → Business → Subscription → Feature Flags → UI + API
```

No `if(plan == premium)` logic allowed.

---

## 8️⃣ BRANDING & FILE MANAGEMENT (FINAL)

### Branding Rules

* Max **2 logo files per business**
* Types:

  * `logo_primary`
  * `logo_secondary`
* No overwrite allowed
* Delete → then upload

### Branding Table

```
business_branding_assets
- id
- business_id
- type
- file_path
- mime_type
- file_size
- created_at
- deleted_at
```

### Storage Path

```
/branding/{business_id}/logo_primary.png
/branding/{business_id}/logo_secondary.png
```

---

## 9️⃣ TEMP FILE MANAGEMENT

### Purpose

* Invoice PDFs
* Report exports
* Generated documents

### Rules

* Auto-delete after **5 days**
* No permanent linking
* Regenerate if needed

### Table

```
temp_files
- id
- business_id
- purpose
- file_path
- expires_at
- created_at
```

### Cleanup

* Daily scheduled job
* Deletes DB record + file

---

## 🔟 DATABASE DESIGN PRINCIPLES (LOCKED)

### Mandatory

* UUID primary keys
* Soft deletes everywhere
* `meta JSONB` column
* Foreign keys
* Ledger-based accounting

### Forbidden Forever

❌ Totals stored in DB
❌ Payments inside invoice table
❌ Hardcoded tax logic
❌ Plan logic inside UI

---

## 1️⃣1️⃣ ACCOUNTING & LEDGER MODEL

### Core Tables

```
ledgers
ledger_entries
payments
payment_allocations
```

### Events

* InvoiceFinalized
* PaymentReceived
* CreditNoteIssued
* StockAdjusted

All reports derived from ledger.

---

## 1️⃣2️⃣ PWA & OFFLINE REQUIREMENTS

### Offline Rules

* Read from IndexedDB
* Write to IndexedDB
* Sync when online
* Conflict resolution by timestamp

### Must Work Offline

* Invoice creation
* Customer management
* Product selection
* Payment recording

---

## 1️⃣3️⃣ SECURITY REQUIREMENTS

* Role-based access
* Tenant isolation
* Encrypted sensitive fields
* Rate limiting
* Audit logs (future)

---

## 1️⃣4️⃣ PERFORMANCE REQUIREMENTS

* Invoice save < 3 seconds
* PDF generation async
* No blocking UI
* Zero data corruption

---

## 1️⃣5️⃣ BACKUP & DATA SAFETY

* Daily DB backup
* Business-level export
* Versioned backups
* Manual restore option (admin)

---

## 1️⃣6️⃣ DEPLOYMENT & DEVOPS

* Ubuntu LTS
* Nginx + PHP-FPM
* Supervisor
* Redis
* PostgreSQL
* CI/CD via GitHub Actions
* Zero-downtime deploy

---

## 1️⃣7️⃣ WHAT IS EXPLICITLY OUT OF SCOPE (NOW)

* Generic file uploads
* Document attachments
* Mobile native apps
* Marketplace
* AI features

---

## 1️⃣8️⃣ SUCCESS METRICS

### Product

* Zero data loss
* <1% sync conflicts
* Stable ledger integrity

### Business

* Free → Paid conversion > 5%
* Churn < 3%
* Feature adoption tracking

---

## 1️⃣9️⃣ FINAL LOCK STATEMENT

This document:

* Freezes **architecture**
* Freezes **core DB assumptions**
* Allows **feature expansion without refactor**
* Enables **clean SaaS monetization**

Any change to:

* Ledger
* Feature system
* Multi-tenancy
  must go through **architectural review**.

---

## 🚀 NEXT (OPTIONAL BUT LOGICAL)

I can now:

1. Design **FULL ERD (tables + relations)**
2. Create **API contracts**
3. Build **Vue 3 PWA skeleton**
4. Write **sync conflict strategy**
5. Prepare **developer onboarding doc**

Just tell me **what’s next**.
