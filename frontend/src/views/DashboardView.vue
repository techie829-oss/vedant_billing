<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">Overview of your business performance.</p>
      </div>
      <div class="mt-4 sm:mt-0 flex gap-2 flex-wrap">
        <router-link to="/purchases/create"
          class="inline-flex items-center px-3 py-2 border border-orange-200 shadow-sm text-sm font-medium rounded-lg text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors">
          <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Purchase Bill
        </router-link>
        <router-link to="/invoices/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
          <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Invoice
        </router-link>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8 grid grid-cols-2 md:grid-cols-4 gap-4">
      <!-- Fast Note -->
      <router-link to="/quick-note"
        class="flex flex-col items-center justify-center p-4 bg-yellow-50 rounded-xl shadow-sm border border-yellow-200 hover:shadow-md hover:bg-yellow-100 transition-all group cursor-pointer relative overflow-hidden">
        <div class="absolute top-0 right-0 w-8 h-8 bg-yellow-200 rotate-45 transform translate-x-4 -translate-y-4">
        </div>
        <div
          class="h-10 w-10 rounded-full bg-yellow-200 text-yellow-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
          </svg>
        </div>
        <span class="text-sm font-bold text-yellow-900">Fast Note</span>
      </router-link>

      <router-link to="/cashbook"
        class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-100 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-900">Cashbook</span>
      </router-link>

      <router-link to="/invoices"
        class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-900">Invoice</span>
      </router-link>

      <!-- Purchase Invoice -->
      <router-link to="/purchases/create"
        class="flex flex-col items-center justify-center p-4 bg-orange-50 rounded-xl shadow-sm border border-orange-100 hover:shadow-md hover:border-orange-200 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <span class="text-sm font-medium text-orange-900">Purchase Bill</span>
      </router-link>

      <!-- Scan Invoice -->
      <router-link to="/invoice-scans"
        class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-orange-100 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-900">Scan Invoice</span>
      </router-link>

      <!-- Add Vendor -->
      <router-link to="/vendors/create"
        class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-orange-100 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-900">Add Vendor</span>
      </router-link>

      <router-link to="/quotations"
        class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-teal-100 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-900">Estimate</span>
      </router-link>

      <router-link to="/customers"
        class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-100 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-900">Add Customer</span>
      </router-link>

      <router-link to="/products"
        class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-orange-100 transition-all group cursor-pointer">
        <div
          class="h-10 w-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-900">Add Product</span>
      </router-link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Main Stats & Chart -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Stats Grid (2x2) -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
          <!-- Sales Revenue -->
          <div
            class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5 flex items-start justify-between hover:shadow-md transition-shadow">
            <div>
              <dt class="text-sm font-medium text-gray-500">Sales Revenue</dt>
              <dd class="mt-1 text-3xl font-bold text-gray-900">₹{{ abbreviateNumber(metrics.revenue) }}</dd>
              <dd class="mt-1 text-xs text-green-600 font-medium bg-green-50 px-2 py-0.5 rounded-full inline-block">
                Received &amp; Pending</dd>
            </div>
            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <!-- Outstanding -->
          <div
            class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5 flex items-start justify-between hover:shadow-md transition-shadow">
            <div>
              <dt class="text-sm font-medium text-gray-500">Outstanding</dt>
              <dd class="mt-1 text-3xl font-bold text-gray-900">₹{{ abbreviateNumber(metrics.outstanding) }}</dd>
              <dd class="mt-1 text-xs text-orange-600 font-medium bg-orange-50 px-2 py-0.5 rounded-full inline-block">To
                be collected</dd>
            </div>
            <div class="p-3 bg-orange-50 rounded-lg text-orange-600">
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <!-- Total Purchases -->
          <div
            class="bg-white overflow-hidden shadow-sm rounded-xl border border-orange-50 p-5 flex items-start justify-between hover:shadow-md transition-shadow">
            <div>
              <dt class="text-sm font-medium text-gray-500">Total Purchases</dt>
              <dd class="mt-1 text-3xl font-bold text-gray-900">₹{{ abbreviateNumber(metrics.total_purchases) }}</dd>
              <dd class="mt-1 text-xs text-orange-600 font-medium bg-orange-50 px-2 py-0.5 rounded-full inline-block">
                Bills from vendors</dd>
            </div>
            <div class="p-3 bg-orange-50 rounded-lg text-orange-500">
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
          <!-- Payable to Vendors -->
          <div
            class="bg-white overflow-hidden shadow-sm rounded-xl border border-red-50 p-5 flex items-start justify-between hover:shadow-md transition-shadow">
            <div>
              <dt class="text-sm font-medium text-gray-500">Payable to Vendors</dt>
              <dd class="mt-1 text-3xl font-bold text-gray-900">₹{{ abbreviateNumber(metrics.payable_to_vendors) }}</dd>
              <dd class="mt-1 text-xs text-red-600 font-medium bg-red-50 px-2 py-0.5 rounded-full inline-block">Pending
                payment</dd>
            </div>
            <div class="p-3 bg-red-50 rounded-lg text-red-500">
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>


        <!-- Chart -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Cashflow (Income vs Expense)</h3>
            <select
              class="text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-600">
              <option>Last 6 Months</option>
            </select>
          </div>
          <div class="h-72 w-full relative">
            <Line v-if="chartData" :data="chartData" :options="chartOptions" />
            <div v-else class="h-full flex items-center justify-center text-gray-400 text-sm">Loading chart...</div>
          </div>
        </div>
      </div>

      <!-- Right Column: Recent Activity & Secondary Stats -->
      <div class="space-y-8">
        <!-- Low Stock Alert Widget -->
        <div v-if="lowStockProducts.length > 0"
          class="bg-white shadow-sm rounded-xl border border-red-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-red-50 bg-red-50/50 flex justify-between items-center">
            <div class="flex items-center gap-2">
              <div class="p-1.5 bg-red-100 rounded-lg text-red-600">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <h3 class="text-sm font-bold text-red-900">Low Stock Alert</h3>
            </div>
            <router-link to="/products" class="text-xs font-medium text-red-600 hover:text-red-500">View
              Inventory</router-link>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-for="product in lowStockProducts" :key="product.id"
              class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
              <div>
                <p class="text-sm font-medium text-gray-900 truncate w-40">{{ product.name }}</p>
                <p class="text-[10px] text-gray-500">Unit: {{ product.unit }}</p>
              </div>
              <div class="text-right">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">
                  {{ Number(product.current_stock) }} Left
                </span>
              </div>
            </div>
          </div>
        </div>
        <!-- Customers & Vendors counts -->
        <div class="grid grid-cols-2 gap-4">
          <div
            class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-4 flex flex-col items-center justify-center text-center">
            <div class="p-2 bg-blue-50 rounded-full text-blue-600 mb-2">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <dd class="text-2xl font-bold text-gray-900">{{ metrics.customers }}</dd>
            <dt class="text-xs font-medium text-gray-500">Customers</dt>
          </div>

          <div
            class="bg-white overflow-hidden shadow-sm rounded-xl border border-orange-50 p-4 flex flex-col items-center justify-center text-center">
            <div class="p-2 bg-orange-50 rounded-full text-orange-600 mb-2">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <dd class="text-2xl font-bold text-gray-900">{{ metrics.vendors ?? 0 }}</dd>
            <dt class="text-xs font-medium text-gray-500">Vendors</dt>
          </div>
        </div>

        <!-- Recent Purchase Invoices -->
        <div class="bg-white shadow-sm rounded-xl border border-orange-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-orange-50 bg-orange-50/50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-orange-900">Recent Purchase Bills</h3>
            <router-link to="/purchases" class="text-xs font-medium text-orange-600 hover:text-orange-500">View
              all</router-link>
          </div>
          <div class="max-h-[220px] overflow-y-auto">
            <div v-if="recentPurchases.length > 0" class="divide-y divide-gray-100">
              <div v-for="inv in recentPurchases" :key="inv.id"
                class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors cursor-pointer"
                @click="$router.push(`/purchases/${inv.id}/edit`)">
                <div class="flex items-start gap-2">
                  <div
                    class="mt-0.5 h-7 w-7 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-bold text-[9px]">
                    {{ inv.vendor_name.substring(0, 2).toUpperCase() }}</div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 truncate w-28">{{ inv.vendor_name }}</p>
                    <p class="text-xs text-gray-400">{{ inv.invoice_number }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm font-bold text-gray-900">₹{{ abbreviateNumber(Number(inv.amount)) }}</p>
                  <span
                    class="inline-flex items-center rounded-sm px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset capitalize"
                    :class="{ 'bg-gray-50 text-gray-600 ring-gray-500/20': inv.status === 'draft', 'bg-green-50 text-green-700 ring-green-600/20': inv.status === 'paid', 'bg-blue-50 text-blue-700 ring-blue-700/20': inv.status === 'sent' }">
                    {{ inv.status }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="p-4 text-center text-xs text-gray-400">
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
