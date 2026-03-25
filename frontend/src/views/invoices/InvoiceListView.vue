<template>
  <AppLayout>
    <div class="p-fluid">
      <!-- Header Section -->
      <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 m-0">Invoices</h1>
          <p class="text-gray-500 mt-1">Manage your billings, payments, and customer accounts.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <Button icon="pi pi-refresh" severity="secondary" outlined @click="refresh" :loading="loading" />
          <Button label="Create Invoice" icon="pi pi-plus" @click="router.push('/invoices/create')" />
        </div>
      </div>

      <!-- Main Data Table -->
      <Card>
        <template #content>
          <DataTable :value="invoices" :loading="loading" dataKey="id" 
            :paginator="true" :rows="10" 
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[10, 25, 50]"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} invoices"
            responsiveLayout="stack" breakpoint="960px"
            v-model:filters="filters" filterDisplay="menu"
            :globalFilterFields="['invoice_number', 'party.name']">
            
            <template #header>
              <div class="flex flex-wrap justify-between items-center gap-3">
                <IconField class="w-full md:w-80">
                  <InputIcon class="pi pi-search" />
                  <InputText v-model="filters['global'].value" placeholder="Search invoices or customers..." class="w-full" />
                </IconField>
                <div class="flex gap-2">
                    <Select v-model="statusFilter" :options="statusOptions" optionLabel="label" optionValue="value" 
                        placeholder="All Statuses" class="w-full md:w-44" showClear />
                </div>
              </div>
            </template>

            <template #empty>No invoices found.</template>

            <Column field="invoice_number" header="Number" sortable>
              <template #body="{ data }">
                <router-link :to="`/invoices/${data.id}`" class="font-bold text-primary hover:underline">
                  {{ data.invoice_number }}
                </router-link>
              </template>
            </Column>

            <Column field="party.name" header="Customer" sortable>
              <template #body="{ data }">
                <div class="flex flex-col">
                  <span class="font-semibold text-gray-900">{{ data.party?.name || 'Unknown' }}</span>
                  <span class="text-xs text-gray-500" v-if="data.party?.phone">{{ data.party.phone }}</span>
                </div>
              </template>
            </Column>

            <Column field="date" header="Date" sortable>
              <template #body="{ data }">
                {{ formatDate(data.date) }}
              </template>
            </Column>

            <Column field="status" header="Status" sortable>
              <template #body="{ data }">
                <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
              </template>
            </Column>

            <Column field="grand_total" header="Amount" sortable>
              <template #body="{ data }">
                <span class="font-bold text-gray-900">₹{{ Number(data.grand_total).toFixed(2) }}</span>
              </template>
            </Column>

            <Column header="Actions" headerStyle="width: 8rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
              <template #body="{ data }">
                <div class="flex justify-center gap-1">
                  <Button icon="pi pi-eye" severity="secondary" rounded text v-tooltip.top="'View'" 
                    @click="router.push(`/invoices/${data.id}`)" />
                  
                  <template v-if="data.status === 'draft'">
                    <Button icon="pi pi-check-circle" severity="success" rounded text v-tooltip.top="'Finalize'" 
                      @click="finalizeInv(data.id)" />
                    <Button icon="pi pi-pencil" severity="info" rounded text v-tooltip.top="'Edit'" 
                      @click="router.push(`/invoices/${data.id}/edit`)" />
                    <Button icon="pi pi-trash" severity="danger" rounded text v-tooltip.top="'Delete'" 
                      @click="deleteInv(data.id)" />
                  </template>

                  <Button icon="pi pi-print" severity="help" rounded text v-tooltip.top="'Print'" 
                    @click="router.push(`/invoices/${data.id}`)" />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- Confirmation & Finalize Dialogs -->
    <Dialog v-model:visible="confirmModal.isOpen" :header="confirmModal.title" :modal="true" :style="{ width: '400px' }">
        <div class="flex flex-col gap-4">
            <p>{{ confirmModal.message }}</p>
            
            <div v-if="confirmModal.showInventoryToggle" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                <div class="flex flex-col gap-1">
                    <span class="font-semibold text-sm">Revert Inventory</span>
                    <span class="text-xs text-gray-500">Restore stock levels</span>
                </div>
                <Checkbox v-model="confirmModal.revertInventory" :binary="true" />
            </div>
        </div>
        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text @click="closeConfirmModal" />
            <Button :label="confirmModal.confirmText" :icon="confirmModal.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'" 
                :severity="confirmModal.confirmText === 'Delete' ? 'danger' : 'primary'"
                @click="handleConfirm" :disabled="confirmModal.processing" />
        </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import AppLayout from '../../layouts/AppLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useInvoiceStore } from '../../stores/invoice'

// PrimeVue Components
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import Checkbox from 'primevue/checkbox'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import { FilterMatchMode } from '@primevue/core/api'

const router = useRouter()
const authStore = useAuthStore()
const invoiceStore = useInvoiceStore()
const { invoices, loading, pagination } = storeToRefs(invoiceStore)

const statusFilter = ref('')
const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const statusOptions = [
    { label: 'Draft', value: 'draft' },
    { label: 'Sent', value: 'sent' },
    { label: 'Paid', value: 'paid' },
    { label: 'Overdue', value: 'overdue' }
]

const refresh = async () => {
  if (!authStore.currentBusinessId) return
  loading.value = true
  try {
    const params: any = {
      business_id: authStore.currentBusinessId,
      type: 'invoice',
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

watch([statusFilter, () => authStore.currentBusinessId], () => {
  invoiceStore.pagination.current_page = 1
  refresh()
})

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString()
}

const getStatusSeverity = (status: string) => {
  switch (status) {
    case 'draft': return 'secondary'
    case 'sent': return 'info'
    case 'paid': return 'success'
    case 'overdue': return 'danger'
    default: return 'secondary'
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
    true
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
