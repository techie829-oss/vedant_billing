<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <!-- Total In -->
                <Card class="border-none shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <template #content>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="block text-gray-500 font-semibold mb-2 text-xs uppercase tracking-wider">Total In (Credit)</span>
                                <div class="text-3xl font-bold text-green-600">₹{{ abbreviateNumber(summary.total_in) }}</div>
                                <div class="mt-2 flex items-center gap-1 text-xs text-green-600/80 font-medium">
                                    <i class="pi pi-arrow-up-right"></i>
                                    <span>Income from Sales</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-green-50 text-green-600">
                                <i class="pi pi-plus-circle text-2xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Total Out -->
                <Card class="border-none shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <template #content>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="block text-gray-500 font-semibold mb-2 text-xs uppercase tracking-wider">Total Out (Debit)</span>
                                <div class="text-3xl font-bold text-red-600">₹{{ abbreviateNumber(summary.total_out) }}</div>
                                <div class="mt-2 flex items-center gap-1 text-xs text-red-600/80 font-medium">
                                    <i class="pi pi-arrow-down-right"></i>
                                    <span>Expenses</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-red-50 text-red-600">
                                <i class="pi pi-minus-circle text-2xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Balance -->
                <Card class="border-none shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <template #content>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="block text-gray-500 font-semibold mb-2 text-xs uppercase tracking-wider">Net Balance</span>
                                <div class="text-3xl font-bold" :class="summary.balance >= 0 ? 'text-primary' : 'text-red-500'">
                                    ₹{{ abbreviateNumber(summary.balance) }}
                                </div>
                                <div class="mt-2 text-xs font-medium text-gray-400 uppercase tracking-tighter">
                                    Current Cash on Hand
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-primary-50 text-primary">
                                <i class="pi pi-wallet text-2xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Cashbook</h1>
                    <p class="text-gray-500 mt-1">Track your daily business income and expenses.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button label="Add Income" icon="pi pi-plus" severity="success" outlined @click="router.push('/invoices/create')" />
                    <Button label="Add Expense" icon="pi pi-minus" severity="danger" @click="openExpenseModal" />
                </div>
            </div>

            <!-- Filters & Ledger Table -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Sidebar Filters -->
                <div class="col-span-12 lg:col-span-3 space-y-6">
                    <Card class="border-none shadow-sm">
                        <template #title>Filter Records</template>
                        <template #content>
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-xs uppercase text-gray-500">Start Date</label>
                                    <DatePicker v-model="filters.start_date" dateFormat="yy-mm-dd" showIcon />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-xs uppercase text-gray-500">End Date</label>
                                    <DatePicker v-model="filters.end_date" dateFormat="yy-mm-dd" showIcon />
                                </div>
                                <div class="flex flex-col gap-2 pt-2">
                                    <Button label="Apply Filters" icon="pi pi-filter" @click="refresh" />
                                    <Button v-if="filters.start_date || filters.end_date" label="Clear" severity="secondary" text @click="clearFilters" />
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Main Ledger Table -->
                <div class="col-span-12 lg:col-span-9">
                    <Card class="border-none shadow-sm">
                        <template #content>
                            <DataTable :value="entries" :loading="loading" dataKey="id" 
                                :paginator="true" :rows="10" 
                                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                :rowsPerPageOptions="[10, 25, 50]"
                                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                                responsiveLayout="stack" breakpoint="960px">
                                
                                <template #empty>No transactions found for the selected period.</template>

                                <Column field="date" header="Date" sortable style="width: 120px">
                                    <template #body="{ data }">{{ formatDate(data.date) }}</template>
                                </Column>

                                <Column header="Transaction Details">
                                    <template #body="{ data }">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">{{ data.title || (data.type === 'IN' ? 'Customer Payment' : 'Business Expense') }}</span>
                                            <span class="text-xs text-gray-500" v-if="data.description">{{ data.description }}</span>
                                            <div class="flex items-center gap-2 mt-1" v-if="data.payment_method">
                                                <Tag :value="data.payment_method" severity="secondary" size="small" class="text-[10px]" />
                                            </div>
                                        </div>
                                    </template>
                                </Column>

                                <Column header="Out (Debit)" style="text-align: right; width: 150px">
                                    <template #body="{ data }">
                                        <span v-if="data.type === 'OUT'" class="font-bold text-red-600">₹{{ Number(data.amount).toFixed(2) }}</span>
                                        <span v-else class="text-gray-200">-</span>
                                    </template>
                                </Column>

                                <Column header="In (Credit)" style="text-align: right; width: 150px">
                                    <template #body="{ data }">
                                        <span v-if="data.type === 'IN'" class="font-bold text-green-600">₹{{ Number(data.amount).toFixed(2) }}</span>
                                        <span v-else class="text-gray-200">-</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Add Expense Dialog -->
        <Dialog v-model:visible="showExpenseModal" header="Record Business Expense" :modal="true" :style="{ width: '450px' }" :breakpoints="{'960px': '75vw', '641px': '100vw'}">
            <div class="flex flex-col gap-4 pt-2">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Amount *</label>
                    <InputNumber v-model="form.amount" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" autofocus />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Category</label>
                    <Select v-model="form.category" :options="categories" placeholder="Select Category" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Date</label>
                    <DatePicker v-model="form.date" dateFormat="yy-mm-dd" showIcon />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Details / Notes</label>
                    <Textarea v-model="form.description" rows="3" autoResize placeholder="What was this expense for?" />
                </div>

                <!-- OCR Scan Receipt Section -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="font-bold text-sm">Scan Receipt</span>
                            <span class="text-xs text-gray-500">Upload image to auto-fill details</span>
                        </div>
                        <Button :label="scanning ? 'Scanning...' : 'Scan Receipt'" :icon="scanning ? 'pi pi-spin pi-spinner' : 'pi pi-camera'" 
                            size="small" severity="secondary" @click="triggerFileUpload" :disabled="scanning" />
                    </div>

                    <input ref="receiptFileInput" type="file" accept="image/*" @change="handleReceiptScan" class="hidden" />

                    <Message v-if="scanResult" severity="success" class="mb-2">
                        Scanned: ₹{{ scanResult.amount }} from {{ scanResult.merchant || 'Merchant' }}
                    </Message>
                    
                    <Message v-if="scanError" severity="error" class="mb-2">
                        {{ scanError }}
                    </Message>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text @click="closeExpenseModal" />
                <Button label="Save Expense" icon="pi pi-check" severity="danger" :loading="savingExpense" @click="saveExpense" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import { useCashbookStore } from '../../stores/cashbook'
import { useExpenseStore } from '../../stores/expense'
import { storeToRefs } from 'pinia'
import client from '../../api/client'

// PrimeVue Components
import Card from 'primevue/card'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import DatePicker from 'primevue/datepicker'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import Message from 'primevue/message'

const router = useRouter()
const cashbookStore = useCashbookStore()
const expenseStore = useExpenseStore()
const { entries, summary, loading } = storeToRefs(cashbookStore)

const showExpenseModal = ref(false)
const savingExpense = ref(false)
const categories = ['Rent', 'Food', 'Travel', 'Utilities', 'Salary', 'Office Supplies', 'Inventory', 'Other']

const form = reactive({
    amount: 0,
    category: 'Other',
    date: new Date(),
    description: '',
    payment_method: 'Cash'
})

// OCR scan state
const scanning = ref(false)
const scanResult = ref<any>(null)
const scanError = ref<string | null>(null)
const receiptFileInput = ref<HTMLInputElement | null>(null)

const openExpenseModal = () => {
    form.amount = 0
    form.category = 'Other'
    form.date = new Date()
    form.description = ''
    scanResult.value = null
    scanError.value = null
    showExpenseModal.value = true
}

const closeExpenseModal = () => {
    showExpenseModal.value = false
}

const saveExpense = async () => {
    if (!form.amount || !form.date) return
    savingExpense.value = true
    try {
        const payload = {
            ...form,
            date: form.date.toISOString().split('T')[0]
        }
        await expenseStore.createExpense(payload)
        showExpenseModal.value = false
        refresh()
    } catch (e) {
        alert('Failed to save expense')
    } finally {
        savingExpense.value = false
    }
}

const filters = reactive({
    start_date: null as Date | null,
    end_date: null as Date | null
})

const refresh = () => {
    const params: any = { page: 1 }
    if (filters.start_date) params.start_date = filters.start_date.toISOString().split('T')[0]
    if (filters.end_date) params.end_date = filters.end_date.toISOString().split('T')[0]
    
    cashbookStore.fetchCashbook(params)
}

const clearFilters = () => {
    filters.start_date = null
    filters.end_date = null
    refresh()
}

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
}

const abbreviateNumber = (value: number) => {
    return new Intl.NumberFormat('en-IN', {
        maximumFractionDigits: 2,
        minimumFractionDigits: 2
    }).format(value);
}

// OCR functions
const triggerFileUpload = () => {
    receiptFileInput.value?.click()
}

const handleReceiptScan = async (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file) return

    scanError.value = null
    scanResult.value = null
    scanning.value = true

    try {
        const formData = new FormData()
        formData.append('receipt', file)
        const response = await client.post('/expenses/scan', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        scanResult.value = response.data.data
        if (response.data.data.amount) form.amount = parseFloat(response.data.data.amount)
        if (response.data.data.date) form.date = new Date(response.data.data.date)
        if (response.data.data.notes) form.description = response.data.data.notes
        if (response.data.data.category) {
            const matched = categories.find(c => c.toLowerCase() === response.data.data.category.toLowerCase())
            if (matched) form.category = matched
        }
    } catch (error: any) {
        scanError.value = error.response?.data?.message || 'Failed to scan receipt'
    } finally {
        scanning.value = false
        if (receiptFileInput.value) receiptFileInput.value.value = ''
    }
}

onMounted(() => {
    refresh()
})
</script>
