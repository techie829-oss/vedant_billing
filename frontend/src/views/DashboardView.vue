<template>
  <AppLayout>
    <!-- Header -->
    <div class="mb-5 flex items-center justify-between">
      <div>
        <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Dashboard</h2>
        <p class="text-xs text-gray-500 mt-0.5">Welcome back, here is your business overview</p>
      </div>
      <div class="relative">
        <button @click="showCreateDropdown = !showCreateDropdown"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create New
          <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': showCreateDropdown }"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
              clip-rule="evenodd" />
          </svg>
        </button>

        <!-- Invisible overlay to close dropdown -->
        <div v-if="showCreateDropdown" @click="showCreateDropdown = false" class="fixed inset-0 z-40"></div>

        <!-- Dropdown Menu -->
        <div v-if="showCreateDropdown"
          class="absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">

          <div class="px-4 py-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sales & Billing</p>
          </div>
          <div class="py-1">
            <router-link to="/invoices/create" @click="showCreateDropdown = false"
              class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Standard Invoice
            </router-link>
            <router-link to="/quotations/create" @click="showCreateDropdown = false"
              class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
              Quotation / Estimate
            </router-link>
            <router-link to="/quick-note" @click="showCreateDropdown = false"
              class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Rough Bill (Kaccha)
            </router-link>
          </div>

          <div class="px-4 py-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Purchases</p>
          </div>
          <div class="py-1">
            <router-link to="/purchases/create" @click="showCreateDropdown = false"
              class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Record Purchase
            </router-link>
            <router-link to="/cashbook" @click="showCreateDropdown = false"
              class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Payment / Cash Entry
            </router-link>
          </div>

          <div class="px-4 py-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Records</p>
          </div>
          <div class="py-1">
            <router-link to="/customers/create" @click="showCreateDropdown = false"
              class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
              Add Customer
            </router-link>
            <router-link to="/products/create" @click="showCreateDropdown = false"
              class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              Add Product
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="mb-6">
      <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3 px-1">Manage Business</h3>
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">

        <router-link to="/invoices"
          class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-3.5 bg-white hover:bg-slate-50 border border-gray-100 hover:border-indigo-200 rounded-2xl transition-all group shadow-sm hover:shadow-md cursor-pointer">
          <div
            class="h-10 w-10 sm:h-9 sm:w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">Invoices</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Manage sales</p>
          </div>
        </router-link>

        <router-link to="/purchases"
          class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-3.5 bg-white hover:bg-slate-50 border border-gray-100 hover:border-orange-200 rounded-2xl transition-all group shadow-sm hover:shadow-md cursor-pointer">
          <div
            class="h-10 w-10 sm:h-9 sm:w-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 group-hover:text-orange-700">Purchases</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Manage bills</p>
          </div>
        </router-link>

        <router-link to="/quick-note"
          class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-3.5 bg-white hover:bg-slate-50 border border-gray-100 hover:border-yellow-300 rounded-2xl transition-all group shadow-sm hover:shadow-md cursor-pointer">
          <div
            class="h-10 w-10 sm:h-9 sm:w-9 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 group-hover:text-yellow-700">Fast Note</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Rough bills</p>
          </div>
        </router-link>

        <router-link to="/quotations"
          class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-3.5 bg-white hover:bg-slate-50 border border-gray-100 hover:border-teal-300 rounded-2xl transition-all group shadow-sm hover:shadow-md cursor-pointer">
          <div
            class="h-10 w-10 sm:h-9 sm:w-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 group-hover:text-teal-700">Estimates</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Manage quotes</p>
          </div>
        </router-link>

        <router-link to="/customers"
          class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-3.5 bg-white hover:bg-slate-50 border border-gray-100 hover:border-blue-300 rounded-2xl transition-all group shadow-sm hover:shadow-md cursor-pointer">
          <div
            class="h-10 w-10 sm:h-9 sm:w-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-700">Customers</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Manage parties</p>
          </div>
        </router-link>

        <router-link to="/cashbook"
          class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-3.5 bg-white hover:bg-slate-50 border border-gray-100 hover:border-emerald-300 rounded-2xl transition-all group shadow-sm hover:shadow-md cursor-pointer">
          <div
            class="h-10 w-10 sm:h-9 sm:w-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 group-hover:text-emerald-700">Cashbook</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Manage flows</p>
          </div>
        </router-link>

      </div>
    </div>

    <!-- KPI Stats Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Sales Revenue -->
      <div
        class="bg-white rounded-2xl border border-gray-100 p-4 xl:p-5 flex items-start justify-between hover:shadow-md transition-shadow">
        <div>
          <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Sales Revenue</dt>
          <dd class="mt-1 text-2xl font-bold text-gray-900 tracking-tight">₹{{ abbreviateNumber(metrics.revenue) }}</dd>
          <dd
            class="mt-1.5 text-[11px] text-green-700 font-medium bg-green-50 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6">
              </path>
            </svg>
            Received &amp; Pending
          </dd>
        </div>
        <div class="p-2.5 bg-indigo-50 rounded-xl text-indigo-600 shrink-0">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <!-- Outstanding -->
      <div
        class="bg-white rounded-2xl border border-gray-100 p-4 xl:p-5 flex items-start justify-between hover:shadow-md transition-shadow">
        <div>
          <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Outstanding</dt>
          <dd class="mt-1 text-2xl font-bold text-gray-900 tracking-tight">₹{{ abbreviateNumber(metrics.outstanding) }}
          </dd>
          <dd
            class="mt-1.5 text-[11px] text-amber-700 font-medium bg-amber-50 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            To be collected
          </dd>
        </div>
        <div class="p-2.5 bg-amber-50 rounded-xl text-amber-600 shrink-0">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <!-- Total Purchases -->
      <div
        class="bg-white rounded-2xl border border-gray-100 p-4 xl:p-5 flex items-start justify-between hover:shadow-md transition-shadow">
        <div>
          <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Purchases</dt>
          <dd class="mt-1 text-2xl font-bold text-gray-900 tracking-tight">₹{{ abbreviateNumber(metrics.total_purchases)
            }}</dd>
          <dd
            class="mt-1.5 text-[11px] text-orange-700 font-medium bg-orange-50 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
              </path>
            </svg>
            Bills from vendors
          </dd>
        </div>
        <div class="p-2.5 bg-orange-50 rounded-xl text-orange-600 shrink-0">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
      </div>
      <!-- Payable -->
      <div
        class="bg-white rounded-2xl border border-gray-100 p-4 xl:p-5 flex items-start justify-between hover:shadow-md transition-shadow">
        <div>
          <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Payable to Vendors</dt>
          <dd class="mt-1 text-2xl font-bold text-gray-900 tracking-tight">₹{{
            abbreviateNumber(metrics.payable_to_vendors) }}</dd>
          <dd
            class="mt-1.5 text-[11px] text-red-700 font-medium bg-red-50 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6 6">
              </path>
            </svg>
            Pending payment
          </dd>
        </div>
        <div class="p-2.5 bg-red-50 rounded-xl text-red-600 shrink-0">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>
    <!-- Chart + Right Column -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
      <div class="lg:col-span-2">
        <!-- Chart -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-gray-700">Cashflow (Income vs Expense)</h3>
            <select class="text-xs border-gray-200 rounded-md text-gray-500 py-0.5 px-1 focus:ring-indigo-300">
              <option>Last 6 Months</option>
            </select>
          </div>
          <div class="h-56 w-full relative">
            <Line v-if="chartData" :data="chartData" :options="chartOptions" />
            <div v-else class="h-full flex items-center justify-center text-gray-400 text-xs">Loading chart...</div>
          </div>
        </div>
      </div>
      <!-- Right Column -->
      <div class="space-y-4">
        <!-- Low Stock Alert Widget -->
        <div v-if="lowStockProducts.length > 0"
          class="bg-white shadow-sm rounded-xl border border-red-100 overflow-hidden">
          <div class="px-3 py-2 border-b border-red-50 bg-red-50/50 flex justify-between items-center">
            <div class="flex items-center gap-1.5">
              <div class="p-1 bg-red-100 rounded-md text-red-600">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <h3 class="text-xs font-bold text-red-900">Low Stock Alert</h3>
            </div>
            <router-link to="/products" class="text-[10px] font-medium text-red-600 hover:text-red-500">View
              →</router-link>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-for="product in lowStockProducts" :key="product.id"
              class="px-3 py-2 flex items-center justify-between hover:bg-gray-50 transition-colors">
              <div>
                <p class="text-xs font-medium text-gray-900 truncate w-32">{{ product.name }}</p>
                <p class="text-[10px] text-gray-400">{{ product.unit }}</p>
              </div>
              <span
                class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">
                {{ Number(product.current_stock) }} Left
              </span>
            </div>
          </div>
        </div>

        <!-- Customers & Vendors counts -->
        <div class="grid grid-cols-2 gap-3">
          <div
            class="bg-white rounded-xl border border-gray-100 p-3 flex flex-col items-center justify-center text-center">
            <div class="p-1.5 bg-blue-50 rounded-lg text-blue-600 mb-1">
              <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <dd class="text-xl font-bold text-gray-900">{{ metrics.customers }}</dd>
            <dt class="text-[10px] font-medium text-gray-400">Customers</dt>
          </div>
          <div
            class="bg-white rounded-xl border border-orange-50 p-3 flex flex-col items-center justify-center text-center">
            <div class="p-1.5 bg-orange-50 rounded-lg text-orange-600 mb-1">
              <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <dd class="text-xl font-bold text-gray-900">{{ metrics.vendors ?? 0 }}</dd>
            <dt class="text-[10px] font-medium text-gray-400">Vendors</dt>
          </div>
        </div>

        <!-- Recent Purchase Invoices -->
        <div class="bg-white shadow-sm rounded-xl border border-orange-100 overflow-hidden">
          <div class="px-3 py-2 border-b border-orange-50 bg-orange-50/50 flex justify-between items-center">
            <h3 class="text-xs font-bold text-orange-900">Recent Purchase Bills</h3>
            <router-link to="/purchases" class="text-[10px] font-medium text-orange-600 hover:text-orange-500">View
              all</router-link>
          </div>
          <div class="max-h-[200px] overflow-y-auto">
            <div v-if="recentPurchases.length > 0" class="divide-y divide-gray-100">
              <div v-for="inv in recentPurchases" :key="inv.id"
                class="px-3 py-2 flex items-center justify-between hover:bg-gray-50 transition-colors cursor-pointer"
                @click="$router.push(`/purchases/${inv.id}/edit`)">
                <div class="flex items-center gap-2">
                  <div
                    class="h-6 w-6 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-bold text-[9px] shrink-0">
                    {{ inv.vendor_name.substring(0, 2).toUpperCase() }}
                  </div>
                  <div>
                    <p class="text-xs font-medium text-gray-900 truncate w-24">{{ inv.vendor_name }}</p>
                    <p class="text-[10px] text-gray-400">{{ inv.invoice_number }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-xs font-bold text-gray-900">₹{{ abbreviateNumber(Number(inv.amount)) }}</p>
                  <span
                    class="inline-flex items-center rounded-sm px-1.5 py-0.5 text-[9px] font-medium ring-1 ring-inset capitalize"
                    :class="{ 'bg-gray-50 text-gray-600 ring-gray-500/20': inv.status === 'draft', 'bg-green-50 text-green-700 ring-green-600/20': inv.status === 'paid', 'bg-blue-50 text-blue-700 ring-blue-700/20': inv.status === 'sent' }">
                    {{ inv.status }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="p-3 text-center text-xs text-gray-400">
              No purchase bills yet.
              <router-link to="/purchases/create" class="text-orange-600 hover:underline block mt-1">Add one
                →</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import client from '../api/client'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

interface DashboardMetrics {
  revenue: number
  outstanding: number
  customers: number
  vendors: number
  products: number
  total_expenses: number
  total_purchases: number
  payable_to_vendors: number
}

interface RecentActivity {
  id: string
  invoice_number: string
  customer_name: string
  date: string
  amount: string | number
  status: string
}

interface LowStockProduct {
  id: string
  name: string
  current_stock: number
  unit: string
}

const metrics = ref<DashboardMetrics>({
  revenue: 0,
  outstanding: 0,
  customers: 0,
  vendors: 0,
  products: 0,
  total_expenses: 0,
  total_purchases: 0,
  payable_to_vendors: 0,
})

const recentActivity = ref<RecentActivity[]>([])
const recentPurchases = ref<any[]>([])
const lowStockProducts = ref<LowStockProduct[]>([])
const cashflowHistory = ref<any[]>([])

const showCreateDropdown = ref(false)

const chartData = computed(() => {
  if (!cashflowHistory.value.length) return null
  return {
    labels: cashflowHistory.value.map((d) => {
      // Convert '2025-01' to 'Jan'
      if (!d.month) return ''
      const parts = d.month.split('-')
      const y = parts[0] || '2000'
      const m = parts[1] || '1'
      const date = new Date(parseInt(y), parseInt(m) - 1, 1)
      return date.toLocaleString('default', { month: 'short' })
    }),
    datasets: [
      {
        label: 'Income',
        backgroundColor: 'rgba(16, 185, 129, 0.2)', // Green
        borderColor: '#10b981',
        borderWidth: 2,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#10b981',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        data: cashflowHistory.value.map(d => d.income),
        tension: 0.4
      },
      {
        label: 'Expense',
        backgroundColor: 'rgba(239, 68, 68, 0.2)', // Red
        borderColor: '#ef4444',
        borderWidth: 2,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#ef4444',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        data: cashflowHistory.value.map(d => d.expense),
        tension: 0.4
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true, position: 'top' as const },
    tooltip: {
      backgroundColor: '#1f2937',
      padding: 12,
      titleFont: { size: 13 },
      bodyFont: { size: 13 },
      displayColors: false,
      callbacks: {
        label: (context: any) => '₹ ' + context.parsed.y.toLocaleString('en-IN')
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { display: true, color: '#f3f4f6' },
      ticks: {
        font: { size: 11 },
        callback: (value: any) => '₹' + abbreviateNumber(value)
      }
    },
    x: {
      grid: { display: false },
      ticks: { font: { size: 11 } }
    }
  }
}

const abbreviateNumber = (value: number) => {
  return new Intl.NumberFormat('en-IN', {
    maximumSignificantDigits: 3,
    notation: "compact",
    compactDisplay: "short"
  }).format(value);
}

onMounted(async () => {
  try {
    const response = await client.get('/dashboard')
    metrics.value = response.data.metrics
    recentActivity.value = response.data.recent_activity
    recentPurchases.value = response.data.recent_purchases || []
    lowStockProducts.value = response.data.low_stock_products || []
    cashflowHistory.value = response.data.cashflow_chart || []
  } catch (e) {
    console.error('Failed to load dashboard data', e)
  }
})
</script>
