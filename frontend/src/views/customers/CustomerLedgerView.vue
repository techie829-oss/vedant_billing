<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <Button icon="pi pi-arrow-left" severity="secondary" rounded text @click="router.back()" />
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 m-0">{{ party?.name || 'Loading Ledger...' }}</h1>
                        <p class="text-gray-500 mt-1 uppercase tracking-wider text-xs font-semibold">Account Statement & Transaction History</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button label="Record Payment" icon="pi pi-plus-circle" severity="success" @click="showPaymentModal = true" />
                    <Button label="Print" icon="pi pi-print" severity="secondary" outlined @click="printLedger" />
                    <Button label="WhatsApp" icon="pi pi-whatsapp" severity="success" text @click="shareWhatsApp" />
                </div>
            </div>

            <!-- Summary KPI Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <Card class="border-none shadow-sm overflow-hidden bg-gray-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-500 uppercase">Opening Balance</span>
                            <span class="text-2xl font-black text-gray-900">₹{{ Number(openingBalance).toFixed(2) }}</span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm overflow-hidden" :class="closingBalance >= 0 ? 'bg-red-50' : 'bg-green-50'">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold uppercase" :class="closingBalance >= 0 ? 'text-red-600' : 'text-green-600'">
                                {{ closingBalance >= 0 ? 'Receivable (Dr)' : 'Payable (Cr)' }}
                            </span>
                            <span class="text-2xl font-black" :class="closingBalance >= 0 ? 'text-red-700' : 'text-green-700'">
                                ₹{{ Math.abs(Number(closingBalance)).toFixed(2) }}
                            </span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm overflow-hidden bg-primary-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-primary uppercase">Total Transactions</span>
                            <span class="text-2xl font-black text-primary-900">{{ ledger.length }} Records</span>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Ledger Data Table -->
            <Card class="border-none shadow-sm overflow-hidden">
                <template #content>
                    <DataTable :value="ledger" :loading="loading" dataKey="id" class="p-datatable-sm"
                        responsiveLayout="stack" breakpoint="960px">
                        
                        <template #empty>No transactions found for this account.</template>

                        <Column field="date" header="Date" sortable style="width: 120px">
                            <template #body="{ data }">{{ data.date }}</template>
                        </Column>

                        <Column header="Description">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ data.description }}</span>
                                    <span class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ data.type.replace('_', ' ') }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Debit (+)" style="text-align: right; width: 150px">
                            <template #body="{ data }">
                                <span v-if="data.debit > 0" class="font-bold text-red-600">₹{{ Number(data.debit).toFixed(2) }}</span>
                                <span v-else class="text-gray-200">-</span>
                            </template>
                        </Column>

                        <Column header="Credit (-)" style="text-align: right; width: 150px">
                            <template #body="{ data }">
                                <span v-if="data.credit > 0" class="font-bold text-green-600">₹{{ Number(data.credit).toFixed(2) }}</span>
                                <span v-else class="text-gray-200">-</span>
                            </template>
                        </Column>

                        <Column header="Running Balance" style="text-align: right; width: 180px">
                            <template #body="{ data }">
                                <span class="font-black text-gray-900">₹{{ Number(data.balance).toFixed(2) }}</span>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center px-4 py-2 bg-gray-50 border-t border-gray-200">
                                <span class="font-bold text-lg uppercase">Net Closing Balance</span>
                                <span class="font-black text-xl" :class="closingBalance >= 0 ? 'text-red-600' : 'text-green-600'">
                                    ₹{{ Math.abs(Number(closingBalance)).toFixed(2) }}
                                    <small class="text-xs font-normal ml-1">{{ closingBalance >= 0 ? 'Dr' : 'Cr' }}</small>
                                </span>
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Record Payment Dialog -->
        <Dialog v-model:visible="showPaymentModal" header="Record Customer Payment" :modal="true" :style="{ width: '450px' }" :breakpoints="{'960px': '75vw', '641px': '100vw'}">
            <div class="flex flex-col gap-4 pt-2">
                <div class="bg-primary-50 p-3 rounded-lg border border-primary-100 flex items-center gap-3">
                    <Avatar icon="pi pi-user" shape="circle" class="bg-primary text-white" />
                    <div class="flex flex-col">
                        <span class="text-xs text-primary-700 font-bold uppercase">Customer</span>
                        <span class="font-black text-primary-900">{{ party?.name }}</span>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Amount Received *</label>
                    <InputNumber v-model="paymentForm.amount" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" autofocus />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Payment Date</label>
                    <DatePicker v-model="paymentForm.date" dateFormat="yy-mm-dd" showIcon />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Payment Method</label>
                    <Select v-model="paymentForm.method" :options="paymentMethods" optionLabel="label" optionValue="value" />
                </div>

                <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <Checkbox v-model="paymentForm.auto_allocate" :binary="true" id="auto_alloc" />
                    <label for="auto_alloc" class="text-sm cursor-pointer">Auto-allocate to oldest pending bills</label>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text @click="showPaymentModal = false" />
                <Button label="Record Payment" icon="pi pi-check" :loading="submittingPayment" @click="submitPayment" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'
import { useNotificationStore } from '../../stores/notification'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Avatar from 'primevue/avatar'

const router = useRouter()
const route = useRoute()
const notificationStore = useNotificationStore()

const loading = ref(true)
const party = ref<any>(null)
const ledger = ref<any[]>([])
const openingBalance = ref(0)
const closingBalance = ref(0)

const showPaymentModal = ref(false)
const submittingPayment = ref(false)

const paymentForm = reactive({
    amount: 0,
    date: new Date(),
    method: 'bank_transfer',
    auto_allocate: true
})

const paymentMethods = [
    { label: 'Cash', value: 'cash' },
    { label: 'Bank Transfer', value: 'bank_transfer' },
    { label: 'UPI / PhonePe / GPay', value: 'upi' },
    { label: 'Cheque', value: 'check' }
]

const submitPayment = async () => {
    if (!paymentForm.amount) return
    submittingPayment.value = true
    try {
        await client.post('/payments', {
            party_id: route.params.id,
            ...paymentForm,
            date: paymentForm.date.toISOString().split('T')[0]
        })
        showPaymentModal.value = false
        // Reset
        paymentForm.amount = 0
        paymentForm.date = new Date()
        await fetchLedger()
        notificationStore.notify('Success', 'Payment recorded successfully.', 'success')
    } catch (e: any) {
        notificationStore.notify('Error', e.response?.data?.message || 'Failed to record payment', 'error')
    } finally {
        submittingPayment.value = false
    }
}

const fetchLedger = async () => {
    loading.value = true
    try {
        const response = await client.get(`/parties/${route.params.id}/ledger`)
        party.value = response.data.party
        ledger.value = response.data.ledger
        openingBalance.value = response.data.opening_balance
        closingBalance.value = response.data.closing_balance
    } catch (e) {
        console.error('Failed to fetch ledger:', e)
    } finally {
        loading.value = false
    }
}

const printLedger = () => { window.print() }

const shareWhatsApp = () => {
    const phone = party.value?.phone
    if (!phone) {
        notificationStore.notify('Missing Info', 'Customer phone number is missing.', 'warning')
        return
    }
    const balanceMsg = closingBalance.value >= 0 
        ? `₹${Math.abs(closingBalance.value).toFixed(2)} (Receivable)` 
        : `₹${Math.abs(closingBalance.value).toFixed(2)} (Payable)`;

    const text = encodeURIComponent(`Hello ${party.value.name},\n\nYour current ledger balance with us is ${balanceMsg}.\n\nThank you!`)
    window.open(`https://wa.me/91${phone.replace(/[^0-9]/g, '')}?text=${text}`, '_blank')
}

onMounted(fetchLedger)
</script>

<style scoped>
@media print {
    :deep(aside), :deep(header), button {
        display: none !important;
    }
    :deep(main) {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
