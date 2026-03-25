<template>
    <div class="space-y-6 p-fluid">
        <!-- Filters & Summary Card -->
        <Card class="border-none shadow-sm overflow-hidden">
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-3 flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">Min Balance</label>
                        <InputNumber v-model="filters.min_balance" mode="currency" currency="INR" locale="en-IN" placeholder="0.00" />
                    </div>
                    <div class="md:col-span-2 pt-6">
                        <Button label="Update List" icon="pi pi-refresh" @click="loadReport" :loading="loading" />
                    </div>
                    <div class="md:col-span-7 flex justify-end">
                        <div class="bg-red-50 p-4 rounded-xl border border-red-100 flex flex-col items-end">
                            <span class="text-xs font-bold text-red-600 uppercase">Total Outstanding</span>
                            <span class="text-3xl font-black text-red-700">{{ formatCurrency(totalOutstanding) }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Debtors Data Table -->
        <Card class="border-none shadow-sm">
            <template #content>
                <DataTable :value="debtors" :loading="loading" dataKey="customer_id" 
                    :paginator="true" :rows="10" 
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[10, 25, 50]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} debtors"
                    responsiveLayout="stack" breakpoint="960px">
                    
                    <template #empty>Great job! No outstanding payments found.</template>

                    <Column field="customer_name" header="Customer" sortable>
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900">{{ data.customer_name }}</span>
                                <span class="text-xs text-gray-500" v-if="data.customer_contact">{{ data.customer_contact }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="invoice_count" header="Pending Invoices" sortable style="text-align: center">
                        <template #body="{ data }">
                            <Tag :value="data.invoice_count + ' Invoices'" severity="warn" />
                        </template>
                    </Column>

                    <Column field="total_due" header="Total Due" sortable style="text-align: right; width: 180px">
                        <template #body="{ data }">
                            <span class="text-xl font-black text-red-600">₹{{ Number(data.total_due).toFixed(2) }}</span>
                        </template>
                    </Column>

                    <Column header="Actions" headerStyle="width: 10rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
                        <template #body="{ data }">
                            <div class="flex justify-center gap-2">
                                <Button icon="pi pi-whatsapp" severity="success" rounded text v-tooltip.top="'WhatsApp Reminder'" 
                                    @click="remindWhatsApp(data)" />
                                <Button icon="pi pi-comment" severity="info" rounded text v-tooltip.top="'SMS Reminder'" 
                                    @click="remindSMS(data)" />
                                <Button icon="pi pi-book" severity="secondary" rounded text v-tooltip.top="'View Ledger'" 
                                    @click="router.push(`/customers/${data.customer_id}/ledger`)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useReportStore } from '../../stores/report'
import { useAuthStore } from '../../stores/auth'
import { storeToRefs } from 'pinia'

// PrimeVue
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import Tag from 'primevue/tag'

const router = useRouter()
const reportStore = useReportStore()
const authStore = useAuthStore()
const { loading } = storeToRefs(reportStore)

const debtors = ref<any[]>([])
const totalOutstanding = ref(0)
const filters = reactive({
    min_balance: 1
})

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val)
}

const loadReport = async () => {
    try {
        const res = await reportStore.fetchOutstandingReport(filters)
        debtors.value = res.data
        totalOutstanding.value = res.total_outstanding
    } catch (e) {
        console.error(e)
    }
}

const getRemindText = (debtor: any) => {
    const businessName = authStore.activeBusiness?.name || 'Us'
    const amount = formatCurrency(debtor.total_due)
    const count = debtor.invoice_count
    return `Hello ${debtor.customer_name},\n\nThis is a gentle reminder from ${businessName}. You have ${count} pending invoice(s) with a total outstanding balance of ${amount}.\n\nPlease ensure payment is made at the earliest.\n\nThank you.`
}

const remindWhatsApp = (debtor: any) => {
    const phone = debtor.customer_contact
    if (!phone || !phone.match(/[0-9]{10}/)) {
        alert('Valid customer phone number is missing.')
        return
    }
    const text = encodeURIComponent(getRemindText(debtor))
    window.open(`https://wa.me/91${phone.replace(/[^0-9]/g, '')}?text=${text}`, '_blank')
}

const remindSMS = (debtor: any) => {
    const phone = debtor.customer_contact
    if (!phone || !phone.match(/[0-9]{10}/)) {
        alert('Valid customer phone number is missing.')
        return
    }
    const text = encodeURIComponent(getRemindText(debtor))
    window.location.href = `sms:${phone.replace(/[^0-9]/g, '')}?body=${text}`
}

onMounted(() => {
    loadReport()
})
</script>
