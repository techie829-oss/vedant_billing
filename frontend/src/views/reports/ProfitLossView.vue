<template>
    <div class="space-y-6">
      <!-- Filters / Header Info -->
      <div class="bg-white shadow sm:rounded-lg p-6 border-b border-gray-200 bg-gray-50">
        <div class="sm:flex sm:items-end sm:justify-between gap-4">
            <div>
                 <h3 class="text-lg font-medium text-gray-900 leading-6">Income Statement</h3>
                 <p class="mt-1 text-sm text-gray-500">
                    Period: <span class="font-medium text-gray-900">{{ formatDate(startDate) }}</span> to <span class="font-medium text-gray-900">{{ formatDate(endDate) }}</span>
                 </p>
            </div>
             <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-4 items-end">
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                     <input type="date" v-model="startDate" class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                     <input type="date" v-model="endDate" class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <button @click="loadData" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Report
                </button>
            </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else-if="reportData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 truncate">Total Income</dt>
              <dd class="mt-1 text-3xl font-semibold text-green-600">{{ formatCurrency(reportData.income.total) }}</dd>
            </div>
          </div>
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 truncate">Total Expenses</dt>
              <dd class="mt-1 text-3xl font-semibold text-red-600">{{ formatCurrency(reportData.expenses.total) }}</dd>
            </div>
          </div>
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 truncate">Net Profit</dt>
              <dd class="mt-1 text-3xl font-semibold" :class="reportData.net_profit >= 0 ? 'text-indigo-600' : 'text-red-600'">
                {{ formatCurrency(reportData.net_profit) }}
              </dd>
            </div>
          </div>
        </div>

        <!-- Detailed Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Income Breakdown -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                 <h3 class="text-lg leading-6 font-medium text-gray-900">Income Breakdown</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200">
                <li v-for="account in reportData.income.accounts" :key="account.id" class="px-4 py-4 sm:px-6 flex justify-between">
                    <span class="text-sm font-medium text-gray-900">{{ account.name }}</span>
                    <span class="text-sm text-gray-500">{{ formatCurrency(account.amount) }}</span>
                </li>
                <li v-if="reportData.income.accounts.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500 text-sm">
                    No income records found for this period.
                </li>
            </ul>
             <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200 flex justify-between font-bold">
                <span>Total Income</span>
                <span>{{ formatCurrency(reportData.income.total) }}</span>
            </div>
          </div>

          <!-- Expense Breakdown -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
             <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                 <h3 class="text-lg leading-6 font-medium text-gray-900">Expense Breakdown</h3>
            </div>
             <ul role="list" class="divide-y divide-gray-200">
                <li v-for="account in reportData.expenses.accounts" :key="account.id" class="px-4 py-4 sm:px-6 flex justify-between">
                    <span class="text-sm font-medium text-gray-900">{{ account.name }}</span>
                    <span class="text-sm text-gray-500">{{ formatCurrency(account.amount) }}</span>
                </li>
                <li v-if="reportData.expenses.accounts.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500 text-sm">
                    No expense records found for this period.
                </li>
            </ul>
            <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200 flex justify-between font-bold">
                <span>Total Expenses</span>
                <span>{{ formatCurrency(reportData.expenses.total) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useReportStore } from '../../stores/report'
import { useAuthStore } from '../../stores/auth'

const reportStore = useReportStore()
const authStore = useAuthStore()

const loading = ref(false)
const reportData = ref<any>(null)

// Default to current month
const now = new Date()
const startDate = ref(new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0])
const endDate = ref(new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0])

const loadData = async () => {
    loading.value = true
    try {
        reportData.value = await reportStore.fetchProfitLoss({
            start_date: startDate.value,
            end_date: endDate.value
        })
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: authStore.activeBusiness?.currency || 'INR'
    }).format(value)
}

const formatDate = (dateStr: string = '') => {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

onMounted(() => {
    loadData()
})
</script>
