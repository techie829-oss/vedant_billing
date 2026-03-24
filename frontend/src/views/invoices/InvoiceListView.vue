<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
        <p class="text-sm text-gray-500 mt-1">
          Manage your invoices and payments.
        </p>
      </div>
      <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
        <div class="flex gap-2 w-full sm:w-auto">
          <select v-model="statusFilter"
            class="block w-full sm:w-40 rounded-lg border-gray-300 py-2.5 px-3 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
          </select>
          <button @click="refresh"
            class="p-2.5 text-gray-400 bg-white border border-gray-300 rounded-lg hover:text-indigo-600 hover:border-indigo-600 transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>

        <router-link to="/invoices/create"
          class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-3 sm:py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
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
      <!-- Mobile Card View -->
      <div class="sm:hidden divide-y divide-gray-100">
        <div v-if="loading && invoices.length === 0" class="p-4 text-center text-gray-500 text-sm">Loading...</div>
        <div v-else-if="invoices.length === 0" class="p-4 text-center text-gray-500 text-sm">No invoices found.</div>
        <div v-for="invoice in invoices" :key="invoice.id"
          class="p-3 hover:bg-gray-50 flex flex-col gap-2 transition-colors">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <router-link :to="`/invoices/${invoice.id}`" class="text-indigo-600 font-bold text-sm">
                {{ invoice.invoice_number }}
              </router-link>
              <span class="text-xs text-gray-400">• {{ formatDate(invoice.date) }}</span>
            </div>
            <span class="font-bold text-gray-900 text-sm">₹{{ invoice.grand_total }}</span>
          </div>

          <div class="flex justify-between items-center">
            <div class="text-sm font-medium text-gray-700 truncate max-w-[60%]">{{ invoice.party?.name || 'Unknown' }}
            </div>
            <span :class="getStatusClass(invoice.status)"
              class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset capitalize">
              {{ invoice.status }}
            </span>
          </div>

          <div class="flex justify-end gap-4 mt-1 pt-1.5 border-t border-gray-50">
            <router-link :to="`/invoices/${invoice.id}`"
              class="text-xs font-medium text-gray-500 hover:text-gray-900 flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              View
            </router-link>
            <router-link :to="`/invoices/${invoice.id}`"
              class="text-xs font-medium text-indigo-600 hover:text-indigo-900 flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print
            </router-link>
            <router-link v-if="invoice.status === 'draft'" :to="`/invoices/${invoice.id}/edit`"
              class="text-xs font-medium text-indigo-600 hover:text-indigo-900 flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </router-link>
          </div>
        </div>
      </div>

      <!-- Desktop Table View -->
      <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50/80">
            <tr>
              <th scope="col"
                class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider sm:pl-6">
                Number</th>
              <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Customer</th>
              <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Date</th>
              <th scope="col"
                class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                Due Date</th>
              <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Status</th>
              <th scope="col" class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Amount</th>
              <th scope="col" class="relative py-2 pl-3 pr-4 sm:pr-6">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-if="loading && invoices.length === 0">
              <td colspan="7" class="text-center py-4 text-xs text-gray-500">Loading invoices...</td>
            </tr>
            <tr v-else-if="invoices.length === 0">
              <td colspan="7" class="text-center py-4 text-xs text-gray-500">No invoices found.</td>
            </tr>
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50/50 transition-colors">
              <td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                <router-link :to="`/invoices/${invoice.id}`"
                  class="text-indigo-600 hover:text-indigo-900 hover:underline">
                  {{ invoice.invoice_number }}
                </router-link>
              </td>
              <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">
                {{ invoice.party?.name || 'Unknown' }}
                <div v-if="invoice.party?.phone" class="text-xs text-gray-500 font-normal mt-0.5">
                  {{ invoice.party.phone }}
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ formatDate(invoice.date) }}</td>
              <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden lg:table-cell">{{
                formatDate(invoice.due_date) }}</td>
              <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                <span :class="getStatusClass(invoice.status)"
                  class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-medium ring-1 ring-inset">
                  {{ capitalize(invoice.status) }}
                </span>
              </td>
              <td class="whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-900 text-right">₹{{
                Number(invoice.grand_total).toFixed(2) }}</td>
              <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                <div class="flex items-center justify-end gap-3">
                  <router-link :to="`/invoices/${invoice.id}`" class="text-indigo-600 hover:text-indigo-900 group"
                    title="View">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:scale-110"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </router-link>

                  <button @click="finalizeInv(invoice.id)" v-if="invoice.status === 'draft'"
                    class="text-green-600 hover:text-green-900 group" title="Finalize">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:scale-110"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>

                  <router-link :to="`/invoices/${invoice.id}/edit`" v-if="invoice.status === 'draft'"
                    class="text-indigo-600 hover:text-indigo-900 group" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:scale-110"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </router-link>
                  <button @click="deleteInv(invoice.id)" v-if="invoice.status === 'draft'"
                    class="text-red-500 hover:text-red-700 group" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:scale-110"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
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

    <!-- Confirmation Modal -->
    <ConfirmationModal :is-open="confirmModal.isOpen" :title="confirmModal.title" :message="confirmModal.message"
      :confirm-text="confirmModal.confirmText" :processing="confirmModal.processing" @close="closeConfirmModal"
      @confirm="handleConfirm">
      <div v-if="confirmModal.showInventoryToggle"
        class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between">
        <div class="flex flex-col">
          <span class="text-sm font-medium text-gray-900">Revert Inventory Stock</span>
          <span class="text-xs text-gray-500">Remove stock quantities added by this invoice</span>
        </div>
        <!-- Toggle Switch -->
        <button type="button"
          class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
          :class="[confirmModal.revertInventory ? 'bg-indigo-600' : 'bg-gray-200']"
          @click="confirmModal.revertInventory = !confirmModal.revertInventory">
          <span class="sr-only">Toggle revert inventory</span>
          <span aria-hidden="true"
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            :class="[confirmModal.revertInventory ? 'translate-x-5' : 'translate-x-0']" />
        </button>
      </div>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useInvoiceStore } from '../../stores/invoice'
import { storeToRefs } from 'pinia'
import ConfirmationModal from '../../components/ConfirmationModal.vue'

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

// Modal States
const confirmModal = ref({
  isOpen: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  processing: false,
  showInventoryToggle: false,
  revertInventory: false,
  onConfirm: async () => { }
})

const showConfirm = (title: string, message: string, onConfirm: () => Promise<void>, confirmText = 'Confirm', showInventoryToggle = false) => {
  confirmModal.value = {
    isOpen: true,
    title,
    message,
    confirmText,
    processing: false,
    showInventoryToggle,
    revertInventory: false,
    onConfirm
  }
}

const closeConfirmModal = () => {
  confirmModal.value.isOpen = false
}

const handleConfirm = async () => {
  confirmModal.value.processing = true
  try {
    await confirmModal.value.onConfirm()
    closeConfirmModal()
  } catch (e) {
    console.error(e)
  } finally {
    confirmModal.value.processing = false
  }
}

const deleteInv = (id: string) => {
  showConfirm(
    'Delete Invoice',
    'Are you sure you want to delete this invoice? This action cannot be undone.',
    async () => {
      await invoiceStore.deleteInvoice(id, confirmModal.value.revertInventory)
      refresh()
    },
    'Delete',
    true // showInventoryToggle
  )
}

const finalizeInv = (id: string) => {
  showConfirm(
    'Finalize Invoice',
    'Are you sure? Once finalized, you cannot edit this invoice.',
    async () => {
      try {
        await invoiceStore.finalizeInvoice(id)
        refresh()
      } catch (e: any) {
        alert(e.response?.data?.message || 'Failed to finalize invoice')
      }
    },
    'Finalize'
  )
}

onMounted(() => {
  refresh()
})
</script>
