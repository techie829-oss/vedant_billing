<template>
  <AppLayout>
    <div class="p-fluid">
      <!-- Header Section -->
      <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 m-0">Purchase Invoices</h1>
          <p class="text-gray-500 mt-1">Audit trail of all vendor bills and inventory stock-in records.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <Button icon="pi pi-refresh" severity="secondary" outlined @click="refresh" :loading="loading" />
          <Button label="New Purchase Bill" icon="pi pi-plus" @click="router.push('/purchases/create')" />
        </div>
      </div>

      <!-- Main Data Table -->
      <Card class="border-none shadow-sm overflow-hidden">
        <template #content>
          <DataTable :value="purchases" :loading="loading" dataKey="id" 
            :paginator="true" :rows="10" 
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[10, 25, 50]"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} bills"
            responsiveLayout="stack" breakpoint="960px"
            v-model:filters="filters" filterDisplay="menu"
            :globalFilterFields="['invoice_number', 'party.name']">
            
            <template #header>
                <div class="flex flex-wrap justify-between items-center gap-3">
                    <IconField class="w-full md:w-80">
                        <InputIcon class="pi pi-search" />
                        <InputText v-model="filters['global'].value" placeholder="Search bills or vendors..." class="w-full" />
                    </IconField>
                    <div class="flex gap-2">
                        <Select v-model="statusFilter" :options="statusOptions" optionLabel="label" optionValue="value" 
                            placeholder="All Statuses" class="w-full md:w-44" showClear />
                    </div>
                </div>
            </template>
            <template #empty>No purchase invoices found.</template>

            <Column field="invoice_number" header="Bill Number" sortable>
              <template #body="{ data }">
                <router-link :to="`/purchases/${data.id}/edit`" class="font-bold text-primary hover:underline">
                  {{ data.invoice_number }}
                </router-link>
              </template>
            </Column>

            <Column field="party.name" header="Vendor" sortable>
              <template #body="{ data }">
                <div class="flex flex-col">
                  <span class="font-semibold text-gray-900">{{ data.party?.name || 'Unknown' }}</span>
                  <span class="text-xs text-gray-500" v-if="data.party?.phone">{{ data.party.phone }}</span>
                </div>
              </template>
            </Column>

            <Column field="date" header="Bill Date" sortable>
              <template #body="{ data }">{{ formatDate(data.date) }}</template>
            </Column>

            <Column field="status" header="Status" sortable>
              <template #body="{ data }">
                <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
              </template>
            </Column>

            <Column field="grand_total" header="Total Amount" sortable style="text-align: right">
              <template #body="{ data }">
                <span class="font-bold text-gray-900">₹{{ Number(data.grand_total).toFixed(2) }}</span>
              </template>
            </Column>

            <Column header="Actions" headerStyle="width: 8rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
              <template #body="{ data }">
                <div class="flex justify-center gap-1">
                  <Button icon="pi pi-pencil" severity="info" rounded text v-tooltip.top="'Edit'" 
                    @click="router.push(`/purchases/${data.id}/edit`)" />
                  <Button v-if="data.status === 'draft'" icon="pi pi-trash" severity="danger" rounded text v-tooltip.top="'Delete'" 
                    @click="confirmDelete(data.id)" />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- Delete Confirmation -->
    <Dialog v-model:visible="deleteModal.isOpen" header="Delete Bill" :modal="true" :style="{ width: '400px' }">
        <p>Are you sure you want to delete this purchase bill? This will also revert any inventory stock added by it.</p>
        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text @click="deleteModal.isOpen = false" />
            <Button label="Delete" icon="pi pi-trash" severity="danger" @click="handleDelete" :loading="deleteModal.processing" />
        </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useInvoiceStore } from '../../stores/invoice'
import { storeToRefs } from 'pinia'
import client from '../../api/client'
import { FilterMatchMode } from '@primevue/core/api'

// PrimeVue
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'
import Select from 'primevue/select'
import Card from 'primevue/card'
import Dialog from 'primevue/dialog'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const router = useRouter()
const authStore = useAuthStore()
const invoiceStore = useInvoiceStore()
const { loading } = storeToRefs(invoiceStore)

const statusFilter = ref('')
const purchases = ref<any[]>([])
const filters = ref({ global: { value: null, matchMode: FilterMatchMode.CONTAINS } })

const statusOptions = [
    { label: 'Draft', value: 'draft' },
    { label: 'Received', value: 'sent' },
    { label: 'Paid', value: 'paid' }
]

const refresh = async () => {
  if (!authStore.currentBusinessId) return
  loading.value = true
  try {
    const params: any = { type: 'purchase_invoice', per_page: 100 }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await client.get('/invoices', { params })
    purchases.value = res.data.data ?? res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-IN') : ''

const getStatusSeverity = (status: string) => {
  switch (status) {
    case 'draft': return 'secondary'
    case 'sent': return 'info'
    case 'paid': return 'success'
    default: return 'secondary'
  }
}

// Delete Logic
const deleteModal = reactive({ isOpen: false, id: '', processing: false })
const confirmDelete = (id: string) => {
    deleteModal.id = id
    deleteModal.isOpen = true
}
const handleDelete = async () => {
    deleteModal.processing = true
    try {
        await client.delete(`/invoices/${deleteModal.id}`)
        deleteModal.isOpen = false
        refresh()
    } finally { deleteModal.processing = false }
}

watch([statusFilter, () => authStore.currentBusinessId], () => {
  refresh()
})

onMounted(refresh)
</script>
