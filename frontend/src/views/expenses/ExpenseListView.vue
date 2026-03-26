<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Expenses</h1>
                    <p class="text-gray-500 mt-1">Track your business expenses and spending.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button label="Scan Receipt" icon="pi pi-camera" severity="secondary" outlined @click="showScanModal = true" />
                    <Button label="Add Expense" icon="pi pi-plus" @click="router.push('/expenses/create')" />
                </div>
            </div>

            <!-- Main Data Table -->
            <Card>
                <template #content>
                    <DataTable :value="expenses" :loading="loading" dataKey="id" 
                        :paginator="true" :rows="10" 
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        :rowsPerPageOptions="[10, 25, 50]"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} expenses"
                        responsiveLayout="stack" breakpoint="960px"
                        v-model:filters="filters" filterDisplay="menu"
                        :globalFilterFields="['description', 'category', 'reference_no']">
                        
                        <template #header>
                            <div class="flex justify-between items-center">
                                <IconField>
                                    <InputIcon class="pi pi-search" />
                                    <InputText v-model="filters['global'].value" placeholder="Search expenses..." class="w-full md:w-80" />
                                </IconField>
                            </div>
                        </template>

                        <template #empty>No expenses recorded.</template>

                        <Column field="date" header="Date" sortable>
                            <template #body="{ data }">
                                <span class="text-gray-700">{{ formatDate(data.date) }}</span>
                            </template>
                        </Column>

                        <Column field="category" header="Category" sortable>
                            <template #body="{ data }">
                                <Tag :value="data.category" severity="secondary" />
                            </template>
                        </Column>

                        <Column field="description" header="Description" sortable>
                            <template #body="{ data }">
                                <span class="text-sm text-gray-900">{{ data.description || '-' }}</span>
                            </template>
                        </Column>

                        <Column field="reference_no" header="Reference" sortable></Column>

                        <Column field="amount" header="Amount" sortable>
                            <template #body="{ data }">
                                <span class="font-bold text-gray-900">₹{{ Number(data.amount).toFixed(2) }}</span>
                            </template>
                        </Column>

                        <Column header="Actions" headerStyle="width: 8rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
                            <template #body="{ data }">
                                <div class="flex justify-center gap-1">
                                    <Button icon="pi pi-pencil" severity="info" rounded text v-tooltip.top="'Edit'" 
                                        @click="router.push(`/expenses/${data.id}/edit`)" />
                                    <Button icon="pi pi-trash" severity="danger" rounded text v-tooltip.top="'Delete'" 
                                        @click="confirmDelete(data.id)" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Scan Receipt Modal -->
        <Dialog v-model:visible="showScanModal" header="Scan Expense Receipt" :style="{ width: '450px' }" :modal="true" :breakpoints="{'960px': '75vw', '641px': '100vw'}">
            <div class="flex flex-col gap-4 py-2">
                <p class="text-sm text-gray-500">Upload a receipt to automatically extract details using AI.</p>
                
                <div v-if="!scanning && !scanResult" 
                    class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl py-12 px-4 hover:border-primary transition-colors cursor-pointer bg-gray-50/50" 
                    @click="fileInput?.click()">
                    <i class="pi pi-upload text-4xl text-gray-400 mb-4"></i>
                    <span class="font-bold text-gray-700">Click to upload receipt</span>
                    <span class="text-xs text-gray-400 mt-1 uppercase">PNG, JPG up to 5MB</span>
                    <input type="file" ref="fileInput" accept="image/*" @change="handleFileSelect" class="hidden">
                </div>

                <!-- Scanning State -->
                <div v-if="scanning" class="text-center py-10">
                    <ProgressSpinner style="width: 40px; height: 40px" strokeWidth="4" />
                    <p class="mt-4 font-bold text-gray-900">Processing Receipt...</p>
                </div>

                <!-- Review Scan Result -->
                <div v-if="scanResult" class="space-y-4 pt-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-xs uppercase text-gray-500">Amount</label>
                            <InputNumber v-model="scanResult.amount" mode="currency" currency="INR" locale="en-IN" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-xs uppercase text-gray-500">Date</label>
                            <DatePicker v-model="scanResult.date" dateFormat="yy-mm-dd" showIcon />
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold text-xs uppercase text-gray-500">Category</label>
                        <InputText v-model="scanResult.category" placeholder="e.g. Food, Travel" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold text-xs uppercase text-gray-500">Description</label>
                        <Textarea v-model="scanResult.description" rows="2" autoResize />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button v-if="!scanResult" label="Cancel" text severity="secondary" @click="closeScanModal" />
                <template v-else>
                    <Button label="Discard" text severity="danger" @click="scanResult = null" />
                    <Button label="Save Expense" icon="pi pi-check" @click="saveScannedExpense" :loading="savingScan" />
                </template>
            </template>
        </Dialog>

        <!-- Delete Confirmation -->
        <Dialog v-model:visible="deleteModal.isOpen" header="Delete Expense" :modal="true" :style="{ width: '400px' }">
            <div class="flex flex-col gap-4 text-center py-4">
                <i class="pi pi-exclamation-triangle text-5xl text-red-500 mb-2"></i>
                <p class="text-gray-700 font-medium leading-relaxed">Are you sure you want to delete this expense? This action cannot be undone.</p>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="deleteModal.isOpen = false" />
                <Button label="Delete" icon="pi pi-trash" severity="danger" @click="handleDelete" :loading="deleteModal.processing" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import AppLayout from '../../layouts/AppLayout.vue'
import { useExpenseStore } from '../../stores/expense'
import client from '../../api/client'
import { FilterMatchMode } from '@primevue/core/api'

// PrimeVue Components
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import Textarea from 'primevue/textarea'
import ProgressSpinner from 'primevue/progressspinner'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const router = useRouter()
const expenseStore = useExpenseStore()
const { expenses, loading, pagination } = storeToRefs(expenseStore)

const showScanModal = ref(false)
const scanning = ref(false)
const savingScan = ref(false)
const scanResult = ref<any>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) scanReceipt(file)
}

const scanReceipt = async (file: File) => {
    scanning.value = true
    const formData = new FormData()
    formData.append('receipt', file)

    try {
        const response = await client.post('/expenses/scan', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        scanResult.value = response.data.data
        if (scanResult.value.date) {
            scanResult.value.date = new Date(scanResult.value.date)
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Scanning failed')
    } finally {
        scanning.value = false
    }
}

const saveScannedExpense = async () => {
    savingScan.value = true
    try {
        const payload = { 
            ...scanResult.value,
            date: scanResult.value.date?.toISOString().split('T')[0]
        }
        await client.post('/expenses', payload)
        showScanModal.value = false
        scanResult.value = null
        refresh()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Failed to save expense')
    } finally {
        savingScan.value = false
    }
}

const closeScanModal = () => {
    showScanModal.value = false
    scanResult.value = null
    scanning.value = false
}

const refresh = () => {
    expenseStore.fetchExpenses({ page: pagination.value.current_page })
}

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
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
        await expenseStore.deleteExpense(deleteModal.value.id)
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
