<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
        <p class="text-sm text-gray-500 mt-1">
          Manage your invoices and payments.
        </p>
      </div>
      <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-4">
        <div class="flex gap-2">
          <select v-model="statusFilter"
            class="block w-40 rounded-lg border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
          </select>
          <button @click="refresh" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>

        <router-link to="/invoices/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Invoice
        </router-link>
      </div>
    </div>

    <!-- Invoice Stats (Optional, keeping simple for now) -->

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-300">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Number</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Due Date</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
              <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-if="loading && invoices.length === 0">
              <td colspan="7" class="text-center py-4 text-gray-500">Loading...</td>
            </tr>
            <tr v-else-if="invoices.length === 0">
              <td colspan="7" class="text-center py-4 text-gray-500">No invoices found.</td>
            </tr>
            <tr v-for="invoice in invoices" :key="invoice.id">
              <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                <router-link :to="`/invoices/${invoice.id}`"
                  class="text-indigo-600 hover:text-indigo-900 hover:underline">
                  {{ invoice.invoice_number }}
                </router-link>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ invoice.party?.name || 'Unknown' }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ formatDate(invoice.date) }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ formatDate(invoice.due_date) }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                <span :class="getStatusClass(invoice.status)"
                  class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">
                  {{ capitalize(invoice.status) }}
                </span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">₹{{ invoice.grand_total }}</td>
              <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                <router-link :to="`/invoices/${invoice.id}`" class="text-gray-600 hover:text-gray-900 mr-4"
                  title="View">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </router-link>
                <a :href="`/invoices/${invoice.id}/print`" target="_blank"
                  class="text-gray-600 hover:text-gray-900 mr-4" title="Print / PDF">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                  </svg>
                </a>
                <router-link :to="`/invoices/${invoice.id}/edit`" v-if="invoice.status === 'draft'"
                  class="text-indigo-600 hover:text-indigo-900 mr-4" title="Edit">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </router-link>
                <button @click="deleteInv(invoice.id)" v-if="invoice.status === 'draft'"
                  class="text-red-600 hover:text-red-900" title="Delete">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="pagination.total > pagination.per_page"
      class="mt-4 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
      <div class="flex flex-1 justify-between sm:justify-end">
        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
          class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0 disabled:opacity-50">Previous</button>
        <button @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="relative ml-3 inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0 disabled:opacity-50">Next</button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useInvoiceStore } from '../../stores/invoice'
import { storeToRefs } from 'pinia'

// No longer using useRoute since this is dedicated to Invoices
const authStore = useAuthStore()
const invoiceStore = useInvoiceStore()
const { invoices, loading, pagination } = storeToRefs(invoiceStore)

const statusFilter = ref('')
const typeFilter = ref('invoice') // Hardcoded, this view is only for Invoices

const refresh = async () => {
  if (!authStore.currentBusinessId) return
  loading.value = true
  try {
    const params: any = {
      business_id: authStore.currentBusinessId,
      type: typeFilter.value,
      page: pagination.value.current_page
    }
    if (statusFilter.value) {
      params.status = statusFilter.value
    }
    await invoiceStore.fetchInvoices(params)
  } catch (e) {
    console.error('Error fetching invoices:', e)
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  if (page < 1 || page > pagination.value.last_page) return
  invoiceStore.pagination.current_page = page
  refresh()
}

watch([statusFilter, () => authStore.currentBusinessId], () => {
  invoiceStore.pagination.current_page = 1
  refresh()
})

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString()
}

const capitalize = (s: string) => s.charAt(0).toUpperCase() + s.slice(1)

const getStatusClass = (status: string) => {
  switch (status) {
    case 'draft': return 'bg-gray-50 text-gray-600 ring-gray-500/10'
    case 'sent': return 'bg-blue-50 text-blue-700 ring-blue-700/10'
    case 'paid': return 'bg-green-50 text-green-700 ring-green-600/20'
    case 'overdue': return 'bg-red-50 text-red-700 ring-red-600/10'
    default: return 'bg-gray-50 text-gray-600 ring-gray-500/10'
  }
}

const deleteInv = async (id: string) => {
  if (confirm(`Are you sure you want to delete this invoice?`)) {
    await invoiceStore.deleteInvoice(id)
    refresh()
  }
}

onMounted(() => {
  refresh()
})
</script>
