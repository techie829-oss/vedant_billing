<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Purchase Invoices</h1>
        <p class="text-sm text-gray-500 mt-1">Manage bills and purchase invoices from your vendors.</p>
      </div>
      <div class="mt-4 sm:mt-0 flex gap-3">
        <select v-model="statusFilter"
          class="block w-36 rounded-lg border-gray-300 py-2.5 px-3 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="sent">Received</option>
          <option value="paid">Paid</option>
        </select>
        <router-link to="/purchases/create"
          class="inline-flex justify-center items-center px-4 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Purchase Invoice
        </router-link>
      </div>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <!-- Mobile -->
      <div class="sm:hidden divide-y divide-gray-100">
        <div v-if="loading && purchases.length === 0" class="p-4 text-center text-gray-500 text-sm">Loading...</div>
        <div v-else-if="purchases.length === 0" class="p-6 text-center text-gray-500 text-sm">
          No purchase invoices found. <router-link to="/purchases/create" class="text-indigo-600">Create one</router-link>.
        </div>
        <div v-for="inv in purchases" :key="inv.id" class="p-3 hover:bg-gray-50 flex flex-col gap-2 transition-colors">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <router-link :to="`/purchases/${inv.id}/edit`" class="text-indigo-600 font-bold text-sm">
                {{ inv.invoice_number }}
              </router-link>
              <span class="text-xs text-gray-400">• {{ formatDate(inv.date) }}</span>
            </div>
            <span class="font-bold text-gray-900 text-sm">₹{{ Number(inv.grand_total).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700 truncate max-w-[60%]">{{ inv.party?.name || 'Unknown Vendor' }}</div>
            <span :class="getStatusClass(inv.status)"
              class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset capitalize">
              {{ inv.status }}
            </span>
          </div>
        </div>
      </div>

      <!-- Desktop Table -->
      <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-300">
          <thead class="bg-gray-50">
            <tr>
              <th class="py-2.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Number</th>
              <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-900">Vendor</th>
              <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-900">Date</th>
              <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-900">Due Date</th>
              <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-900">Status</th>
              <th class="px-3 py-2.5 text-right text-sm font-semibold text-gray-900">Amount</th>
              <th class="relative py-2.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-if="loading && purchases.length === 0">
              <td colspan="7" class="text-center py-6 text-gray-500">Loading...</td>
            </tr>
            <tr v-else-if="purchases.length === 0">
              <td colspan="7" class="text-center py-6 text-gray-500">No purchase invoices found.</td>
            </tr>
            <tr v-for="inv in purchases" :key="inv.id" class="hover:bg-gray-50 transition-colors">
              <td class="whitespace-nowrap py-2.5 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                <router-link :to="`/purchases/${inv.id}/edit`" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                  {{ inv.invoice_number }}
                </router-link>
              </td>
              <td class="whitespace-nowrap px-3 py-2.5 text-sm text-gray-500">{{ inv.party?.name || '—' }}</td>
              <td class="whitespace-nowrap px-3 py-2.5 text-sm text-gray-500">{{ formatDate(inv.date) }}</td>
              <td class="whitespace-nowrap px-3 py-2.5 text-sm text-gray-500">{{ formatDate(inv.due_date) }}</td>
              <td class="whitespace-nowrap px-3 py-2.5 text-sm">
                <span :class="getStatusClass(inv.status)"
                  class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize">
                  {{ inv.status }}
                </span>
              </td>
              <td class="whitespace-nowrap px-3 py-2.5 text-sm text-gray-900 text-right font-medium">
                ₹{{ Number(inv.grand_total).toFixed(2) }}
              </td>
              <td class="relative whitespace-nowrap py-2.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-3">
                <router-link v-if="inv.status === 'draft'" :to="`/purchases/${inv.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Edit</router-link>
                <button v-if="inv.status === 'draft'" @click="deletePurchase(inv.id)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page"
      class="mt-4 flex items-center justify-end border-t border-gray-200 bg-white px-4 py-3">
      <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
        class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">Previous</button>
      <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
        class="ml-3 relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">Next</button>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useInvoiceStore } from '../../stores/invoice'
import { storeToRefs } from 'pinia'
import client from '../../api/client'

const authStore = useAuthStore()
const invoiceStore = useInvoiceStore()
const { loading, pagination } = storeToRefs(invoiceStore)

const statusFilter = ref('')
const purchases = ref<any[]>([])

const refresh = async () => {
  if (!authStore.currentBusinessId) return
  loading.value = true
  try {
    const params: any = { type: 'purchase_invoice', page: pagination.value.current_page }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await client.get('/invoices', { params })
    purchases.value = res.data.data ?? res.data
    Object.assign(pagination.value, {
      current_page: res.data.current_page ?? 1,
      last_page: res.data.last_page ?? 1,
      per_page: res.data.per_page ?? 20,
      total: res.data.total ?? purchases.value.length
    })
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  if (page < 1 || page > pagination.value.last_page) return
  pagination.value.current_page = page
  refresh()
}

const deletePurchase = async (id: string) => {
  if (!confirm('Delete this purchase invoice?')) return
  await client.delete(`/invoices/${id}`)
  refresh()
}

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString() : ''

const getStatusClass = (status: string) => {
  switch (status) {
    case 'draft': return 'bg-gray-50 text-gray-600 ring-gray-500/10'
    case 'sent': return 'bg-blue-50 text-blue-700 ring-blue-700/10'
    case 'paid': return 'bg-green-50 text-green-700 ring-green-600/20'
    default: return 'bg-gray-50 text-gray-600 ring-gray-500/10'
  }
}

watch([statusFilter, () => authStore.currentBusinessId], () => {
  pagination.value.current_page = 1
  refresh()
})

onMounted(refresh)
</script>
