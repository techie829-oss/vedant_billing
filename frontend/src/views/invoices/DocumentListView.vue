<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">{{ pageTitle }}</h1>
                    <p class="text-gray-500 mt-1">{{ pageDescription }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button icon="pi pi-refresh" severity="secondary" outlined @click="refresh" :loading="loading" />
                    <Button :label="createButtonText" icon="pi pi-plus" @click="router.push(createRoute)" />
                </div>
            </div>

            <!-- Main Data Table -->
            <Card>
                <template #content>
                    <DataTable :value="invoices" :loading="loading" dataKey="id" 
                        :paginator="true" :rows="10" 
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        :rowsPerPageOptions="[10, 25, 50]"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} documents"
                        responsiveLayout="stack" breakpoint="960px"
                        v-model:filters="filters" filterDisplay="menu"
                        :globalFilterFields="['invoice_number', 'party.name']">
                        
                        <template #header>
                            <div class="flex flex-wrap justify-between items-center gap-3">
                                <IconField class="w-full md:w-80">
                                    <InputIcon class="pi pi-search" />
                                    <InputText v-model="filters['global'].value" placeholder="Search number or customer..." class="w-full" />
                                </IconField>
                                <div class="flex gap-2">
                                    <Select v-model="statusFilter" :options="statusOptions" optionLabel="label" optionValue="value" 
                                        placeholder="All Statuses" class="w-full md:w-44" showClear />
                                </div>
                            </div>
                        </template>

                        <template #empty>No {{ pageTitle.toLowerCase() }} found.</template>

                        <Column field="invoice_number" header="Number" sortable>
                            <template #body="{ data }">
                                <router-link :to="`${baseRoute}/${data.id}`" class="font-bold text-primary hover:underline">
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
                                        @click="router.push(`${baseRoute}/${data.id}`)" />
                                    
                                    <template v-if="data.status === 'draft'">
                                        <Button icon="pi pi-pencil" severity="info" rounded text v-tooltip.top="'Edit'" 
                                            @click="router.push(`${baseRoute}/${data.id}/edit`)" />
                                        <Button icon="pi pi-trash" severity="danger" rounded text v-tooltip.top="'Delete'" 
                                            @click="confirmDelete(data.id)" />
                                    </template>

                                    <Button icon="pi pi-print" severity="help" rounded text v-tooltip.top="'Print'" 
                                        @click="router.push(`${baseRoute}/${data.id}`)" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Delete Confirmation -->
        <Dialog v-model:visible="deleteModal.isOpen" header="Delete Document" :modal="true" :style="{ width: '400px' }">
            <div class="flex flex-col gap-4 text-center py-4">
                <i class="pi pi-exclamation-triangle text-5xl text-red-500 mb-2"></i>
                <p class="text-gray-700 font-medium leading-relaxed">
                    Are you sure you want to delete this {{ pageTitle.toLowerCase().slice(0, -1) }}? This action cannot be undone.
                </p>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="deleteModal.isOpen = false" />
                <Button label="Delete" icon="pi pi-trash" severity="danger" @click="handleDelete" :loading="deleteModal.processing" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import AppLayout from '../../layouts/AppLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useInvoiceStore } from '../../stores/invoice'
import { FilterMatchMode } from '@primevue/core/api'

// PrimeVue Components
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const route = useRoute()
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
    { label: 'Overdue', value: 'overdue' },
    { label: 'Accepted', value: 'accepted' },
    { label: 'Declined', value: 'declined' }
]

// Detect document type from route
const documentType = computed(() => {
    const path = route.path
    if (path.includes('/quotations')) return 'proforma_invoice'
    if (path.includes('/credit-notes')) return 'credit_note'
    if (path.includes('/debit-notes')) return 'debit_note'
    if (path.includes('/delivery-challans')) return 'delivery_challan'
    return 'tax_invoice,bill_of_supply'
})

const baseRoute = computed(() => {
    const path = route.path
    if (path.includes('/quotations')) return '/quotations'
    if (path.includes('/credit-notes')) return '/credit-notes'
    if (path.includes('/debit-notes')) return '/debit-notes'
    if (path.includes('/delivery-challans')) return '/delivery-challans'
    return '/invoices'
})

const pageTitle = computed(() => {
    if (documentType.value === 'proforma_invoice') return 'Quotations'
    if (documentType.value === 'credit_note') return 'Credit Notes'
    if (documentType.value === 'debit_note') return 'Debit Notes'
    if (documentType.value === 'delivery_challan') return 'Delivery Challans'
    return 'Invoices'
})

const pageDescription = computed(() => {
    if (documentType.value === 'proforma_invoice') return 'Manage your quotations and estimates.'
    if (documentType.value === 'credit_note') return 'Manage your customer credit notes and refunds.'
    if (documentType.value === 'debit_note') return 'Manage debit notes for additional charges.'
    if (documentType.value === 'delivery_challan') return 'Manage delivery challans for goods movement.'
    return 'Manage your invoices and bills of supply.'
})

const createRoute = computed(() => `${baseRoute.value}/create`)

const createButtonText = computed(() => {
    if (documentType.value === 'proforma_invoice') return 'Create Quotation'
    if (documentType.value === 'credit_note') return 'Create Credit Note'
    if (documentType.value === 'debit_note') return 'Create Debit Note'
    if (documentType.value === 'delivery_challan') return 'Create Delivery Challan'
    return 'Create Invoice'
})

const refresh = async () => {
    if (!authStore.currentBusinessId) return
    loading.value = true
    try {
        const params: any = {
            business_id: authStore.currentBusinessId,
            type: documentType.value,
            page: pagination.value.current_page
        }
        if (statusFilter.value) {
            params.status = statusFilter.value
        }
        await invoiceStore.fetchInvoices(params)
    } catch (e) {
        console.error('Error fetching documents:', e)
    } finally {
        loading.value = false
    }
}

watch([statusFilter, () => authStore.currentBusinessId, documentType], () => {
    invoiceStore.pagination.current_page = 1
    refresh()
})

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
}

const getStatusSeverity = (status: string) => {
    switch (status) {
        case 'paid': return 'success'
        case 'sent': return 'info'
        case 'overdue': return 'danger'
        case 'accepted': return 'success'
        case 'declined': return 'danger'
        case 'converted': return 'secondary'
        default: return 'secondary'
    }
}

// Delete Modal Logic
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
        await invoiceStore.deleteInvoice(deleteModal.value.id)
        deleteModal.value.isOpen = false
        refresh()
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
