import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import ReportsLayout from '../views/reports/ReportsLayout.vue'
import SalesReportView from '../views/reports/SalesReportView.vue'
import OutstandingReportView from '../views/reports/OutstandingReportView.vue'
import StockReportView from '../views/reports/StockReportView.vue'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('../views/auth/LoginView.vue'),
            meta: { guest: true }
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('../views/auth/RegisterView.vue'),
            meta: { guest: true }
        },
        {
            path: '/google-callback',
            name: 'google-callback',
            component: () => import('../views/auth/GoogleCallbackView.vue'),
            meta: { guest: true }
        },
        {
            path: '/verify-email',
            name: 'verify-email',
            component: () => import('../views/auth/VerifyEmailView.vue'),
            meta: { auth: true } // User is logged in but unverified
        },
        {
            path: '/email-verified',
            name: 'email-verified',
            component: () => import('../views/auth/EmailVerifiedView.vue'),
            meta: { auth: true }
        },
        {
            path: '/forgot-password',
            name: 'dashboard',
            component: () => import('../views/DashboardView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/businesses',
            name: 'business-selection',
            component: () => import('../views/auth/BusinessSelectionView.vue'),
            meta: { requiresAuth: true }
        },
        {
            path: '/businesses/create',
            name: 'business-create',
            component: () => import('../views/auth/BusinessCreateView.vue'),
            meta: { requiresAuth: true } // Intentionally NOT requiring business
        },
        {
            path: '/',
            name: 'dashboard',
            component: () => import('../views/DashboardView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/customers',
            name: 'customers-list',
            component: () => import('../views/customers/CustomerListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/customers/create',
            name: 'customers.create',
            component: () => import('../views/customers/CustomerFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/customers/:id/edit',
            name: 'customers.edit',
            component: () => import('../views/customers/CustomerFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/reports',
            name: 'reports',
            component: ReportsLayout,
            meta: { requiresAuth: true, requiresBusiness: true },
            redirect: { name: 'reports.sales' },
            children: [
                {
                    path: 'sales',
                    name: 'reports.sales',
                    component: SalesReportView,
                    meta: { requiresAuth: true, requiresBusiness: true }
                },
                {
                    path: 'outstanding',
                    name: 'reports.outstanding',
                    component: OutstandingReportView,
                    meta: { requiresAuth: true, requiresBusiness: true }
                },
                {
                    path: 'stock',
                    name: 'reports.stock',
                    component: StockReportView,
                    meta: { requiresAuth: true, requiresBusiness: true }
                },
                {
                    path: 'profit-loss',
                    name: 'profit-loss-report',
                    component: () => import('../views/reports/ProfitLossView.vue'),
                    meta: { requiresAuth: true, requiresBusiness: true }
                },
                {
                    path: 'tax',
                    name: 'tax-report',
                    component: () => import('../views/reports/TaxReportView.vue'),
                    meta: { requiresAuth: true, requiresBusiness: true }
                }
            ]
        },
        {
            path: '/products',
            name: 'products-list',
            component: () => import('../views/products/ProductListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/products/create',
            name: 'products.create',
            component: () => import('../views/products/ProductFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/products/:id/edit',
            name: 'products.edit',
            component: () => import('../views/products/ProductFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/inventory/history',
            name: 'inventory.history',
            component: () => import('../views/products/InventoryHistoryView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/invoice-scans',
            name: 'invoice-scans',
            component: () => import('../views/invoices/InvoiceScansView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/invoice-scans/:id',
            name: 'invoice-scan-details',
            component: () => import('../views/invoices/InvoiceScanDetailView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/quick-note',
            name: 'quick-note',
            component: () => import('../views/quick-note/QuickNoteView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        // Invoices
        {
            path: '/invoices',
            name: 'invoices-list',
            component: () => import('../views/invoices/InvoiceListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/invoices/create',
            name: 'invoice-create',
            component: () => import('../views/invoices/InvoiceFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/invoices/:id',
            name: 'invoice-detail',
            component: () => import('../views/invoices/InvoiceDetailView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/invoices/:id/edit',
            name: 'invoice-edit',
            component: () => import('../views/invoices/InvoiceFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/invoices/:id/print',
            name: 'invoice-print',
            component: () => import('../views/invoices/InvoicePrintView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        // Quotations
        {
            path: '/quotations',
            name: 'quotations',
            component: () => import('../views/invoices/DocumentListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/quotations/create',
            name: 'quotation-create',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/quotations/:id',
            name: 'quotation-detail',
            component: () => import('../views/invoices/InvoiceDetailView.vue'), // Reusing Detail View for now
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/quotations/:id/edit',
            name: 'quotation-edit',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        // Credit Notes
        {
            path: '/credit-notes',
            name: 'credit-notes',
            component: () => import('../views/invoices/DocumentListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/credit-notes/create',
            name: 'credit-note-create',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/credit-notes/:id',
            name: 'credit-note-detail',
            component: () => import('../views/invoices/InvoiceDetailView.vue'), // Reusing Detail View for now
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/credit-notes/:id/edit',
            name: 'credit-note-edit',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        // Debit Notes
        {
            path: '/debit-notes',
            name: 'debit-notes',
            component: () => import('../views/invoices/DocumentListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/debit-notes/create',
            name: 'debit-note-create',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/debit-notes/:id',
            name: 'debit-note-detail',
            component: () => import('../views/invoices/InvoiceDetailView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/debit-notes/:id/edit',
            name: 'debit-note-edit',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        // Delivery Challans
        {
            path: '/delivery-challans',
            name: 'delivery-challans',
            component: () => import('../views/invoices/DocumentListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/delivery-challans/create',
            name: 'delivery-challan-create',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/delivery-challans/:id',
            name: 'delivery-challan-detail',
            component: () => import('../views/invoices/InvoiceDetailView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/delivery-challans/:id/edit',
            name: 'delivery-challan-edit',
            component: () => import('../views/invoices/InvoiceFormView.vue'), // Unified form
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        // Vendors
        {
            path: '/vendors',
            name: 'vendors-list',
            component: () => import('../views/vendors/VendorListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/vendors/create',
            name: 'vendors.create',
            component: () => import('../views/vendors/VendorFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/vendors/:id/edit',
            name: 'vendors.edit',
            component: () => import('../views/vendors/VendorFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        // Purchase Invoices
        {
            path: '/purchases',
            name: 'purchases-list',
            component: () => import('../views/purchases/PurchaseListView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/purchases/create',
            name: 'purchases.create',
            component: () => import('../views/purchases/PurchaseFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/purchases/:id/edit',
            name: 'purchases.edit',
            component: () => import('../views/purchases/PurchaseFormView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/settings/business',
            name: 'settings.business',
            component: () => import('../views/settings/BusinessProfileView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },

        {
            path: '/settings/team',
            name: 'settings.team',
            component: () => import('../views/settings/TeamSettingsView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/billing',
            name: 'billing',
            component: () => import('../views/BillingView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },
        {
            path: '/cashbook',
            name: 'cashbook',
            component: () => import('../views/cashbook/CashbookView.vue'),
            meta: { requiresAuth: true, requiresBusiness: true }
        },

    ]
})

// Navigation Guard
router.beforeEach((to, _from, next) => {
    const auth = useAuthStore()
    const isAuthenticated = auth.isAuthenticated
    const hasBusiness = auth.hasSelectedBusiness

    if (to.meta.requiresAuth && !isAuthenticated) {
        // Not logged in, go to login
        next({ name: 'login' })
    } else if (isAuthenticated && to.name === 'login') {
        // Already logged in, go to dashboard (or business selection)
        if (hasBusiness) {
            next({ name: 'dashboard' })
        } else {
            next({ name: 'business-selection' })
        }
    } else if (isAuthenticated && to.meta.requiresBusiness && !hasBusiness) {
        // Logged in but active business needed
        next({ name: 'business-selection' })
    } else if (isAuthenticated && to.name === 'business-selection' && hasBusiness) {
        // Trying to switch business? For now, if they have one selected, redirect to dashboard.
        // Or allow it if we want to support switching. Let's allow switching explicitly,
        next()
    } else if (isAuthenticated && to.name === 'settings.team' && !auth.hasFeature('multi_user')) {
        // Feature restricted (e.g. Starter Plan trying to access Team)
        alert("Upgrade to Pro to access Team Management.") // Optional user feedback
        next({ name: 'dashboard' })
    } else {
        // Strict Subscription Enforcement
        // If authenticated and has business selected, checking plan status
        if (isAuthenticated && hasBusiness) {
            const currentPlan = auth.currentSubscription?.plan;
            const isRestrictedRoute = !['billing', 'settings.business', 'settings.team', 'business-create', 'business-selection', 'login'].includes(to.name as string);

            // If no plan is active (or deleted), force redirect to billing page (plan selection)
            if (!currentPlan && isRestrictedRoute) {
                next({ name: 'billing' });
                return;
            }
        }
        next()
    }
})

export default router
