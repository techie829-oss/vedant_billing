<template>
    <div class="space-y-6">
        <!-- Filters Card -->
        <Card class="border-none shadow-sm overflow-hidden">
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">Start Date</label>
                        <DatePicker v-model="filters.start_date" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">End Date</label>
                        <DatePicker v-model="filters.end_date" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">Customer</label>
                        <Select v-model="filters.customer_id" :options="customers" optionLabel="name" optionValue="id" filter showClear placeholder="All Customers" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <Button label="Generate Report" icon="pi pi-chart-bar" @click="loadReport" :loading="loading" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Summary KPI Row -->
        <div v-if="totals" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card class="border-none shadow-sm bg-indigo-50">
                <template #content>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-indigo-600 uppercase">Total Invoices</span>
                        <span class="text-2xl font-black text-indigo-900">{{ totals.count }}</span>
                    </div>
                </template>
            </Card>
            <Card class="border-none shadow-sm bg-green-50">
                <template #content>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-green-600 uppercase">Total Sales</span>
                        <span class="text-2xl font-black text-green-900">{{ formatCurrency(totals.total_amount) }}</span>
                    </div>
                </template>
            </Card>
            <Card class="border-none shadow-sm bg-amber-50">
                <template #content>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-amber-600 uppercase">Balance Due</span>
                        <span class="text-2xl font-black text-amber-900">{{ formatCurrency(totals.balance_due) }}</span>
                    </div>
                </template>
            </Card>
            <Card class="border-none shadow-sm bg-blue-50">
                <template #content>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-blue-600 uppercase">Tax Collected</span>
                        <span class="text-2xl font-black text-blue-900">{{ formatCurrency(totals.tax_collected) }}</span>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Report Data Table -->
        <Card class="border-none shadow-sm">
            <template #content>
                <DataTable :value="invoices" :loading="loading" dataKey="id" 
                    :paginator="true" :rows="10" 
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[10, 25, 50, 100]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
                    responsiveLayout="stack" breakpoint="960px">
                    
                    <template #empty>No records found for this period.</template>

                    <Column field="date" header="Date" sortable style="width: 120px">
                        <template #body="{ data }">{{ formatDate(data.date) }}</template>
                    </Column>

                    <Column field="invoice_number" header="Invoice #" sortable>
                        <template #body="{ data }">
                            <router-link :to="`/invoices/${data.id}`" class="font-bold text-primary hover:underline">
                                {{ data.invoice_number }}
                            </router-link>
                        </template>
                    </Column>

                    <Column field="party.name" header="Customer" sortable>
                        <template #body="{ data }">
                            <span class="font-medium text-gray-900">{{ data.party?.name || 'Unknown' }}</span>
                        </template>
                    </Column>

                    <Column field="status" header="Status" sortable style="width: 120px">
                        <template #body="{ data }">
                            <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
                        </template>
                    </Column>

                    <Column field="grand_total" header="Amount" sortable style="text-align: right; width: 150px">
                        <template #body="{ data }">
                            <span class="font-bold">₹{{ Number(data.grand_total).toFixed(2) }}</span>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useReportStore } from '../../stores/report'
import { usePartyStore } from '../../stores/party'
import { storeToRefs } from 'pinia'

// PrimeVue
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Tag from 'primevue/tag'

const reportStore = useReportStore()
const partyStore = usePartyStore()
const { loading } = storeToRefs(reportStore)

const invoices = ref<any[]>([])
const totals = ref<any>(null)
const customers = ref<any[]>([])

const filters = reactive({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    end_date: new Date(),
    customer_id: ''
})

const loadReport = async () => {
    try {
        const params = {
            ...filters,
            start_date: filters.start_date.toISOString().split('T')[0],
            end_date: filters.end_date.toISOString().split('T')[0]
        }
        const res = await reportStore.fetchSalesReport(params)
        invoices.value = res.data
        totals.value = res.totals
    } catch (e) {
        console.error(e)
    }
}

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val)
}

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
}

const getStatusSeverity = (status: string) => {
    switch (status) {
        case 'paid': return 'success'
        case 'partial':
        case 'sent': return 'warn'
        case 'overdue': return 'danger'
        default: return 'secondary'
    }
}

onMounted(async () => {
    try {
        await partyStore.fetchParties({ type: 'customer', per_page: 100 })
        customers.value = partyStore.parties
    } catch (e) { console.error(e) }

    await loadReport()
})
</script>
