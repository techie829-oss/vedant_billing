<template>
  <AppLayout>
    <div class="p-fluid">
      <!-- Header Section -->
      <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 m-0">Customers</h1>
          <p class="text-gray-500 mt-1">Manage your customer relationships and track balances.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <Button icon="pi pi-refresh" severity="secondary" outlined @click="refresh" :loading="loading" />
          <Button label="Add Customer" icon="pi pi-user-plus" @click="router.push('/customers/create')" />
        </div>
      </div>

      <!-- Main Data Table -->
      <Card>
        <template #content>
          <DataTable :value="partyStore.customers" :loading="loading" dataKey="id" 
            :paginator="true" :rows="10" 
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[10, 25, 50]"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} customers"
            responsiveLayout="stack" breakpoint="960px"
            v-model:filters="filters" filterDisplay="menu"
            :globalFilterFields="['name', 'phone', 'email', 'gstin']">
            
            <template #header>
              <div class="flex flex-wrap justify-between items-center gap-3">
                <IconField class="w-full md:w-80">
                  <InputIcon class="pi pi-search" />
                  <InputText v-model="filters['global'].value" placeholder="Search customers..." class="w-full" />
                </IconField>
              </div>
            </template>

            <template #empty>
              <div class="text-center py-12">
                <i class="pi pi-users text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No customers found.</p>
                <Button label="Add Your First Customer" icon="pi pi-plus" text @click="router.push('/customers/create')" class="mt-2" />
              </div>
            </template>

            <Column field="name" header="Name" sortable>
              <template #body="{ data }">
                <div class="flex items-center gap-3">
                  <Avatar :label="data.name.substring(0, 1).toUpperCase()" shape="circle" class="bg-primary-100 text-primary font-bold" />
                  <div class="flex flex-col">
                    <span class="font-bold text-gray-900">{{ data.name }}</span>
                    <span class="text-xs text-gray-500" v-if="data.gstin">GSTIN: {{ data.gstin }}</span>
                  </div>
                </div>
              </template>
            </Column>

            <Column field="contact" header="Contact">
              <template #body="{ data }">
                <div class="flex flex-col gap-1">
                  <div v-if="data.phone" class="flex items-center gap-2 text-sm">
                    <i class="pi pi-phone text-xs text-gray-400"></i>
                    <span>{{ data.phone }}</span>
                  </div>
                  <div v-if="data.email" class="flex items-center gap-2 text-sm">
                    <i class="pi pi-envelope text-xs text-gray-400"></i>
                    <span>{{ data.email }}</span>
                  </div>
                </div>
              </template>
            </Column>

            <Column field="current_balance" header="Balance" sortable>
              <template #body="{ data }">
                <div class="font-bold" :class="getBalanceClass(data.current_balance)">
                  ₹{{ Math.abs(data.current_balance).toFixed(2) }}
                  <span class="text-[10px] uppercase ml-1">{{ data.current_balance > 0 ? 'Dr' : (data.current_balance < 0 ? 'Cr' : '') }}</span>
                </div>
              </template>
            </Column>

            <Column field="status" header="Status" sortable>
              <template #body="{ data }">
                <Tag :value="data.status.toUpperCase()" :severity="data.status === 'active' ? 'success' : 'secondary'" />
              </template>
            </Column>

            <Column header="Actions" headerStyle="width: 10rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
              <template #body="{ data }">
                <div class="flex justify-center gap-1">
                  <Button icon="pi pi-book" severity="warn" rounded text v-tooltip.top="'Ledger'" 
                    @click="router.push(`/customers/${data.id}/ledger`)" />
                  <Button icon="pi pi-pencil" severity="info" rounded text v-tooltip.top="'Edit'" 
                    @click="router.push(`/customers/${data.id}/edit`)" />
                  <Button icon="pi pi-trash" severity="danger" rounded text v-tooltip.top="'Delete'" 
                    @click="confirmDelete(data.id)" />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- Delete Confirmation -->
    <Dialog v-model:visible="deleteModal.isOpen" header="Delete Customer" :modal="true" :style="{ width: '400px' }">
        <div class="flex flex-col gap-4">
            <p>Are you sure you want to delete this customer? This action cannot be undone.</p>
        </div>
        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text @click="deleteModal.isOpen = false" />
            <Button label="Delete" icon="pi pi-trash" severity="danger" @click="handleDelete" :loading="deleteModal.processing" />
        </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { usePartyStore } from '../../stores/party'
import AppLayout from '../../layouts/AppLayout.vue'
import { FilterMatchMode } from '@primevue/core/api'

// PrimeVue Components
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Dialog from 'primevue/dialog'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const router = useRouter()
const partyStore = usePartyStore()
const loading = computed(() => partyStore.loading)

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const refresh = () => {
  partyStore.fetchParties({ type: 'customer' })
}

const getBalanceClass = (balance: number) => {
  if (balance > 0) return 'text-green-600'
  if (balance < 0) return 'text-red-600'
  return 'text-gray-900'
}

// Delete Modal State
const deleteModal = ref({
  isOpen: false,
  id: '',
  processing: false
})

const confirmDelete = (id: string) => {
  deleteModal.value = {
    isOpen: true,
    id,
    processing: false
  }
}

const handleDelete = async () => {
  deleteModal.value.processing = true
  try {
    await partyStore.deleteParty(deleteModal.value.id)
    deleteModal.value.isOpen = false
  } catch (e) {
    console.error(e)
  } finally {
    deleteModal.value.processing = false
  }
}

onMounted(() => {
  refresh()
})
</script>
