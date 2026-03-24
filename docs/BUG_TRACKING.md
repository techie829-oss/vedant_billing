# 🐛 Bug & Task Tracking: VedantBilling

This document tracks identified bugs, architectural flaws, and pending technical tasks discovered during the project audit.

## 🔴 CRITICAL: Security & Multi-Tenancy Leaks
These issues pose a risk of data exposure between different businesses (tenants).

- [x] **Missing Global Scopes:** Fixed on all models.
- [x] **Scoped Invoice Numbering:** Fixed in `InvoiceController`.
- [x] **Spoofable Tenancy Authorization:** Applied `tenant.context` middleware to ALL routes in `api.php`.
- [x] **Missing Policy Enforcement:** All controllers (`PartyController`, `InventoryController`, `PaymentController`, `ProductController`) now use `Gate::authorize()`.

## 🟠 HIGH: Financial & Core Logic
These issues break the fundamental accounting and inventory features of the application.

- [x] **Incomplete Ledger Implementation:** Fixed.
- [x] **Broken Stock Updates:** Fixed.
- [x] **Stagnant Party Balances:** Fixed.
- [x] **Broken Invoice Finalization:** Fixed backend to respect `status` in `store/update` and added UI buttons in List View.
- [x] **Inventory Race Conditions:** `ProductController@adjustStock` and `InventoryController@store` now use `lockForUpdate()`.
- [x] **Role-Based Access Violations:** Policies updated to restrict Create/Update/Delete to Owners/Admins.
- [x] **Purchase Status Bug:** Fixed.
- [x] **Missing Financial Casts:** Fixed.
- [x] **Dashboard Chart Crash:** Fixed with robust null-checks and cross-driver compatibility.
- [x] **Payment Allocation (Bill-by-Bill):** Refactored backend to support multi-invoice allocation and FIFO auto-allocation. Added UI to Ledger view.
- [x] **Missing Model Casts:** Fixed.

## 🟡 MEDIUM: OCR & AI Pipeline
Issues related to the automated data extraction and matching workflow.

- [x] **Scanning Visibility:** Added "Scan Catalog" button to `ProductListView.vue`.
- [ ] **Scan Error Handling:** If an LLM fails to parse an invoice, `temp_products` can get stuck in a 'pending' state without a clear UI recovery path for the user.
- [x] **Expense Scan UI:** Implemented OCR receipt scanning directly in `ExpenseListView.vue`.

## 🟢 LOW: Technical Debt & DX
Improvements for maintainability and developer experience.

- [x] **Test Coverage:** Added `TenancyIsolationTest.php` covering all core models.
- [x] **Party Ledger (Khata):** Implemented backend endpoint and frontend view with running balance and WhatsApp sharing.
- [ ] **Laravel 13 Readiness:** Prepare for the PHP 8.3 bump and framework upgrade.

---
**Last Updated:** March 24, 2026
**Status:** Audit Complete | Implementation Pending
