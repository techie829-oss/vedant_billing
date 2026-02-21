<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Offline Banner -->
    <div v-if="!online" class="bg-red-600 text-white text-center py-2 text-sm font-medium sticky top-0 z-50">
      You are currently offline. Some features may not work.
    </div>
    <div v-if="online && syncing"
      class="bg-yellow-500 text-white text-center py-2 text-sm font-medium sticky top-0 z-50">
      Syncing offline changes...
    </div>

    <div class="flex flex-1">
      <!-- Mobile sidebar -->
      <div v-show="mobileMenuOpen" class="relative z-40 lg:hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" @click="mobileMenuOpen = false"></div>

        <div
          class="fixed inset-y-0 left-0 z-40 flex w-full max-w-xs flex-col bg-white pb-4 shadow-xl transition transform duration-300 ease-in-out"
          :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
          <div class="flex items-center justify-between px-4 pt-5 pb-2">
            <router-link to="/" class="text-2xl font-bold text-gray-900 tracking-tight">
              <span class="text-indigo-600">Vedant</span>Billing
            </router-link>
            <button type="button"
              class="-mr-2 inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
              @click="mobileMenuOpen = false">
              <span class="sr-only">Close menu</span>
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="mt-5 h-0 flex-1 overflow-y-auto">
            <nav class="space-y-1 px-2">
              <!-- Mobile Nav Links (Duplicates of Desktop for now to ensure working state) -->
              <router-link to="/" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path === '/' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Dashboard
              </router-link>

              <router-link to="/invoices/create" @click="mobileMenuOpen = false"
                class="flex items-center px-4 py-3 text-base font-medium rounded-xl transition-colors text-indigo-600 bg-indigo-50 mt-2 mb-2"
                :class="[!hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg class="h-6 w-6 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Invoice
              </router-link>

              <router-link to="/quick-note" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/quick-note') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                Fast Note
              </router-link>

              <router-link to="/invoices" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/invoices') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Invoices
              </router-link>

              <router-link to="/customers" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/customers') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Customers
              </router-link>

              <router-link to="/products" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/products') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Products
              </router-link>

              <router-link to="/reports" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/reports') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Reports
              </router-link>

              <router-link to="/quotations" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/quotations') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Estimates
              </router-link>

              <router-link to="/purchases" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/purchases') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Purchase Invoices
              </router-link>

              <router-link to="/vendors" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/vendors') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Vendors
              </router-link>

              <router-link to="/invoice-scans" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/invoice-scans') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Invoice Scans
              </router-link>

              <router-link to="/credit-notes" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/credit-notes') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l2.333-1.75L10 21l2.333-1.75L15 21l1.667-1.75L19 21z" />
                </svg>
                Credit Notes
              </router-link>

              <router-link to="/debit-notes" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/debit-notes') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Debit Notes
              </router-link>

              <router-link to="/delivery-challans" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/delivery-challans') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Delivery Challans
              </router-link>

              <router-link to="/cashbook" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/cashbook') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Cashbook
              </router-link>

              <router-link v-if="canManageBusiness" to="/billing" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path === '/billing' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Billing & Subscription
              </router-link>

              <router-link v-if="canManageBusiness" to="/settings/business" @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/settings') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
              </router-link>

              <router-link v-if="authStore.hasFeature('multi_user') && canManageBusiness" to="/settings/team"
                @click="mobileMenuOpen = false"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="[$route.path.startsWith('/settings/team') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Team
              </router-link>

              <div class="border-t border-gray-200 mt-4 pt-4">
                <button @click="handleLogout"
                  class="flex w-full items-center px-4 py-3 text-base font-medium text-red-600 rounded-xl hover:bg-red-50">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Logout
                </button>
              </div>
            </nav>
          </div>
        </div>
      </div>

      <!-- Static sidebar for desktop -->
      <aside
        class="fixed inset-y-0 left-0 bg-white w-64 border-r border-gray-200 z-30 transition-transform duration-300 hidden lg:flex lg:flex-col">
        <div class="flex items-center justify-center h-12 border-b border-gray-100">
          <router-link to="/" class="text-xl font-bold text-gray-900 tracking-tight">
            <span class="text-indigo-600">Vedant</span>Billing
          </router-link>
        </div>
        <nav class="p-2 space-y-0.5 flex-1 overflow-y-auto">
          <router-link to="/" class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
            :class="[$route.path === '/' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Dashboard
          </router-link>

          <router-link to="/quick-note"
            class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
            :class="[$route.path.startsWith('/quick-note') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            Fast Note
          </router-link>

          <router-link to="/customers"
            class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
            :class="[$route.path.startsWith('/customers') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Customers
          </router-link>

          <router-link to="/products"
            class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
            :class="[$route.path.startsWith('/products') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Products
          </router-link>

          <div>
            <button @click="toggleReportsMenu" :disabled="!hasActivePlan"
              class="w-full flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors focus:outline-none"
              :class="[$route.path.startsWith('/reports') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span class="flex-1 text-left">Reports</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform duration-200"
                :class="{ 'rotate-180': showReportsMenu }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="showReportsMenu" class="pl-8 pr-2 space-y-0.5 mt-0.5">
              <router-link to="/reports/sales" class="block px-2 py-1 rounded-lg text-xs transition-colors"
                :class="[$route.path.includes('/reports/sales') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                Sales
              </router-link>
              <router-link to="/reports/outstanding" class="block px-2 py-1 rounded-lg text-xs transition-colors"
                :class="[$route.path.includes('/reports/outstanding') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                Outstanding
              </router-link>
              <router-link to="/reports/stock" class="block px-2 py-1 rounded-lg text-xs transition-colors"
                :class="[$route.path.includes('/reports/stock') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                Stock
              </router-link>
              <router-link to="/reports/profit-loss" class="block px-2 py-1 rounded-lg text-xs transition-colors"
                :class="[$route.path.includes('/reports/profit-loss') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                Profit & Loss
              </router-link>
              <router-link to="/reports/tax" class="block px-2 py-1 rounded-lg text-xs transition-colors"
                :class="[$route.path.includes('/reports/tax') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                Tax Reports
              </router-link>
            </div>
          </div>

          <!-- Sales / Billing Section -->
          <div class="pt-1.5 pb-0.5">
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Sales</p>

            <router-link to="/invoices"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/invoices') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Invoices
            </router-link>

            <router-link to="/quotations"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/quotations') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
              Estimates
            </router-link>

            <router-link to="/credit-notes"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/credit-notes') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l2.333-1.75L10 21l2.333-1.75L15 21l1.667-1.75L19 21z" />
              </svg>
              Credit Notes
            </router-link>

            <router-link to="/debit-notes"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/debit-notes') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Debit Notes
            </router-link>

            <router-link to="/delivery-challans"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/delivery-challans') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Delivery Challans
            </router-link>
          </div>

          <!-- Purchase Section -->
          <div class="pt-1.5 pb-0.5">
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Purchases</p>

            <router-link to="/purchases"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/purchases') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Purchase Invoices
            </router-link>

            <router-link to="/vendors"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/vendors') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Vendors
            </router-link>

            <router-link to="/invoice-scans"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/invoice-scans') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Invoice Scans
            </router-link>
          </div>

          <!-- Finance Section -->
          <div class="pt-1.5 pb-0.5">
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Finance</p>
            <router-link to="/cashbook"
              class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="[$route.path.startsWith('/cashbook') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', !hasActivePlan ? 'opacity-50 pointer-events-none' : '']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Cashbook
            </router-link>
          </div>

          <router-link v-if="canManageBusiness" to="/billing"
            class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
            :class="[$route.path === '/billing' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Billing & Subscription
          </router-link>

          <router-link v-if="canManageBusiness" to="/settings/business"
            class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
            :class="[$route.path.startsWith('/settings') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
          </router-link>

          <router-link v-if="authStore.hasFeature('multi_user') && canManageBusiness" to="/settings/team"
            class="flex items-center px-3 py-1 text-xs font-medium rounded-lg transition-colors"
            :class="[$route.path === '/settings/team' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Team
          </router-link>

          <!-- Add more links here -->
        </nav>

        <div class="p-4 border-t border-gray-100 shrink-0">
          <div class="px-2 py-2">
            <div class="flex items-center mb-3">
              <div
                class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                {{ userInitials }}
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-700 truncate w-32">{{ authStore.user?.name || 'User' }}</p>
                <button @click="handleLogout" class="text-xs text-gray-500 hover:text-red-600">Logout</button>
              </div>
            </div>
            <router-link to="/businesses"
              class="block w-full text-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Switch Business
            </router-link>
          </div>
        </div>
      </aside>

      <!-- Mobile Header & Main Content -->
      <div class="flex-1 flex flex-col lg:ml-64 min-w-0 h-[100dvh]">
        <!-- Top Header for Mobile/Tablet -->
        <header
          class="bg-white/80 backdrop-blur-md border-b border-gray-200 lg:hidden h-14 flex items-center px-4 justify-between sticky top-0 z-20">
          <div class="flex items-center gap-2">
            <router-link to="/" class="font-bold text-lg text-indigo-600 tracking-tight">VedantBilling</router-link>
            <span v-if="!online"
              class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">OFFLINE</span>
          </div>
          <button class="text-gray-500 p-2 -mr-2 active:bg-gray-100 rounded-full transition-colors"
            @click="mobileMenuOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-3 sm:p-8 overflow-y-auto pb-24 lg:pb-8 overscroll-none">
          <slot></slot>
        </main>

        <!-- Mobile Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 lg:hidden z-50 pb-safe">
          <div class="flex justify-around items-center h-16">
            <router-link to="/"
              class="flex flex-col items-center justify-center w-full h-full text-xs font-medium transition-colors"
              :class="[$route.path === '/' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-900']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
              Home
            </router-link>

            <router-link to="/invoices"
              class="flex flex-col items-center justify-center w-full h-full text-xs font-medium transition-colors"
              :class="[$route.path.startsWith('/invoices') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-900']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Invoices
            </router-link>

            <!-- FAB for Quick Action (Center) -->
            <div class="relative -top-5">
              <router-link to="/invoices/create"
                class="flex items-center justify-center h-14 w-14 rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </router-link>
            </div>

            <router-link to="/products"
              class="flex flex-col items-center justify-center w-full h-full text-xs font-medium transition-colors"
              :class="[$route.path.startsWith('/products') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-900']">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              Products
            </router-link>

            <button @click="mobileMenuOpen = true"
              class="flex flex-col items-center justify-center w-full h-full text-xs font-medium text-gray-500 hover:text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              More
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../stores/auth'
// import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
// import {
//   Bars3Icon,
//   BellIcon,
//   XMarkIcon,
//   BriefcaseIcon,
//   DocumentDuplicateIcon,
//   UserGroupIcon
// } from '@heroicons/vue/24/outline'


const authStore = useAuthStore()

const mobileMenuOpen = ref(false)
const showReportsMenu = ref(false)
const toggleReportsMenu = () => {
  showReportsMenu.value = !showReportsMenu.value
}

// offline status
const online = ref(navigator.onLine)
const syncing = ref(false)
const updateOnlineStatus = () => { online.value = navigator.onLine }

const updateSyncStatus = (e: Event) => {
  syncing.value = e.type === 'sync-start'
}

onMounted(() => {
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
  window.addEventListener('sync-start', updateSyncStatus)
  window.addEventListener('sync-complete', updateSyncStatus)
})

onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
  window.removeEventListener('sync-start', updateSyncStatus)
  window.removeEventListener('sync-complete', updateSyncStatus)
})

const userInitials = computed(() => {
  const name = authStore.user?.name || ''
  return name.split(' ').map((n: string) => n[0]).join('').substring(0, 2).toUpperCase()
})

const canManageBusiness = computed(() => {
  // Ensure business is loaded and has pivot data before checking role
  if (!authStore.activeBusiness || !authStore.activeBusiness.pivot) {
    return false
  }
  const role = authStore.activeBusiness.pivot.role
  return role === 'owner' || role === 'admin'
})

const hasActivePlan = computed(() => {
  return !!authStore.currentSubscription?.plan;
})

const handleLogout = () => {
  authStore.logout()
}
</script>
