<template>
    <AppLayout>
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <router-link to="/customers" class="hover:text-indigo-600">Customers</router-link>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span>Ledger</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">{{ party?.name || 'Loading...' }}</h1>
                <p class="text-sm text-gray-500">Statement of accounts and transaction history</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button @click="showPaymentModal = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    Record Payment
                </button>
                <button @click="printLedger" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
                <button @click="shareWhatsApp" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-[#25D366] hover:bg-[#20bd5a] focus:outline-none">
                    WhatsApp
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Opening Balance</dt>
                <dd class="mt-1 text-2xl font-bold text-gray-900">₹{{ Number(openingBalance).toFixed(2) }}</dd>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Closing Balance</dt>
                <dd class="mt-1 text-2xl font-bold" :class="closingBalance >= 0 ? 'text-red-600' : 'text-green-600'">
                    ₹{{ Math.abs(Number(closingBalance)).toFixed(2) }}
                    <span class="text-xs font-normal ml-1">{{ closingBalance >= 0 ? '(Receivable)' : '(Payable)' }}</span>
                </dd>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Transactions</dt>
                <dd class="mt-1 text-2xl font-bold text-gray-900">{{ ledger.length }}</dd>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="ledger-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider text-red-600">Debit (+)</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider text-green-600">Credit (-)</th>
                            <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="loading">
                            <td colspan="5" class="text-center py-10 text-sm text-gray-500 italic">Loading ledger entries...</td>
                        </tr>
                        <tr v-else-if="ledger.length === 0">
                            <td colspan="5" class="text-center py-10 text-sm text-gray-500 italic">No transactions found for this period.</td>
                        </tr>
                        <!-- Opening Balance Row -->
                        <tr class="bg-gray-50/50">
                            <td class="py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">-</td>
                            <td class="px-3 py-4 text-sm font-semibold text-gray-900 uppercase">Opening Balance</td>
                            <td class="px-3 py-4 text-sm text-right">-</td>
                            <td class="px-3 py-4 text-sm text-right">-</td>
                            <td class="px-3 py-4 text-sm text-right font-bold text-gray-900">₹{{ Number(openingBalance).toFixed(2) }}</td>
                        </tr>
                        <!-- Transaction Rows -->
                        <tr v-for="(tx, index) in ledger" :key="index" class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ tx.date }}</td>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ tx.description }}</div>
                                <div class="text-xs text-gray-400 capitalize">{{ tx.type.replace('_', ' ') }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-red-600 font-medium">
                                {{ tx.debit > 0 ? '₹' + Number(tx.debit).toFixed(2) : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-green-600 font-medium">
                                {{ tx.credit > 0 ? '₹' + Number(tx.credit).toFixed(2) : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold text-gray-900">
                                ₹{{ Number(tx.balance).toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50/50 font-bold border-t-2 border-gray-200">
                        <tr>
                            <td colspan="2" class="py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6 text-right uppercase tracking-wider">Grand Total</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-red-600">
                                ₹{{ totalDebit.toFixed(2) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-green-600">
                                ₹{{ totalCredit.toFixed(2) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-gray-900">
                                ₹{{ Math.abs(Number(closingBalance)).toFixed(2) }}
                                <span class="text-[10px] font-normal ml-1">{{ closingBalance >= 0 ? 'Dr' : 'Cr' }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Record Payment Modal -->
        <div v-if="showPaymentModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <div>
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 4.5h.75zm0 15a.75.75 0 01.75-.75h-.75V15c.996-.264 2.131.46 2.62.872l1.32.768c.379.22.844.186 1.18-.088l1.753-1.344c.435-.333 1.055-.262 1.465.166l1.786 1.84a1.125 1.125 0 001.62-.005l1.09-1.125c.343-.353.948-.28 1.25.127l.654.887c.184.25.467.397.776.402 1.353.023 2.802-.303 3.93-1.077m0-9.67l-.768-1.554a1.737 1.737 0 00-2.316-.76l-8.08 4.296a1.166 1.166 0 00-.51 1.472l1.309 2.923a.857.857 0 001.5.088l2.97-4.137" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Record Payment</h3>
                                <p class="text-sm text-gray-500">Record a payment received from {{ party?.name }}.</p>

                                <div class="mt-4 space-y-4 text-left">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-900">Amount Received (₹)</label>
                                        <input type="number" step="0.01" v-model="paymentForm.amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-900">Payment Date</label>
                                        <input type="date" v-model="paymentForm.date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-900">Method</label>
                                        <select v-model="paymentForm.method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="upi">UPI</option>
                                            <option value="check">Check</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="auto-allocate" type="checkbox" v-model="paymentForm.auto_allocate" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="auto-allocate" class="ml-2 block text-sm text-gray-900">Auto-allocate to oldest pending bills</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="button" @click="submitPayment" :disabled="submittingPayment" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:col-start-2 disabled:opacity-50">
                                {{ submittingPayment ? 'Saving...' : 'Record Payment' }}
                            </button>
                            <button type="button" @click="showPaymentModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'
import { useNotificationStore } from '../../stores/notification'

const notificationStore = useNotificationStore()
const route = useRoute()
const loading = ref(true)
const party = ref<any>(null)
const ledger = ref<any[]>([])
const openingBalance = ref(0)
const closingBalance = ref(0)

const showPaymentModal = ref(false)
const submittingPayment = ref(false)
const paymentForm = ref({
    amount: 0,
    date: new Date().toISOString().split('T')[0],
    method: 'bank_transfer',
    auto_allocate: true
})

const totalDebit = computed(() => {
    return ledger.value.reduce((sum, tx) => sum + Number(tx.debit || 0), 0)
})

const totalCredit = computed(() => {
    return ledger.value.reduce((sum, tx) => sum + Number(tx.credit || 0), 0)
})

const submitPayment = async () => {
    submittingPayment.value = true
    try {
        await client.post('/payments', {
            party_id: route.params.id,
            ...paymentForm.value
        })
        showPaymentModal.value = false
        // Reset form
        paymentForm.value = {
            amount: 0,
            date: new Date().toISOString().split('T')[0],
            method: 'bank_transfer',
            auto_allocate: true
        }
        await fetchLedger()
        notificationStore.notify('Success', 'Payment recorded and allocated successfully.', 'success')
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

const printLedger = () => {
    window.print()
}

const shareWhatsApp = () => {
    const phone = party.value?.phone
    if (!phone) {
        notificationStore.notify('Missing Info', 'Customer phone number is missing.', 'warning')
        return
    }
    
    const balanceMsg = closingBalance.value >= 0 
        ? `₹${Math.abs(closingBalance.value).toFixed(2)} (Receivable)` 
        : `₹${Math.abs(closingBalance.value).toFixed(2)} (Payable)`;

    const text = encodeURIComponent(
        `Hello ${party.value.name},\n\nYour current ledger balance with us is ${balanceMsg}.\n\nThank you!`
    )
    window.open(`https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${text}`, '_blank')
}

onMounted(fetchLedger)
</script>

<style scoped>
@media print {
    .AppLayout :deep(aside), 
    .AppLayout :deep(header),
    button {
        display: none !important;
    }
    .AppLayout :deep(main) {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
