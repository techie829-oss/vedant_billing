<template>
    <div class="space-y-6">
        <div class="bg-white shadow sm:rounded-lg">
            <!-- Filters -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Balance</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input type="number" v-model="filters.min_balance"
                                class="block w-full rounded-md border-0 py-2 pl-7 pr-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                placeholder="0.00" />
                        </div>
                    </div>
                    <div>
                        <button @click="loadReport" :disabled="loading"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            {{ loading ? 'Loading...' : 'Update List' }}
                        </button>
                    </div>
                    <div class="lg:col-span-2 flex justify-end items-center">
                        <div
                            class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm">
                            Total Due: <span class="font-bold text-red-600 text-lg ml-2">{{
                                formatCurrency(totalOutstanding) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pending Invoices</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Due</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="loading && !debtors.length">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                        <tr v-else-if="!debtors.length">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Great job! No outstanding
                                payments.</td>
                        </tr>
                        <tr v-for="debtor in debtors" :key="debtor.customer_id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-indigo-600">{{ debtor.customer_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ debtor.customer_contact || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ debtor.invoice_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600 text-right">
                                {{ formatCurrency(debtor.total_due) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
                                    <button @click="remindWhatsApp(debtor)" title="WhatsApp Reminder"
                                        class="text-green-600 hover:text-green-800 transition-transform hover:scale-110">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M.057 24l1.687-6.163c-3.104-5.391-.039-12.01 6.163-15.038 6.136-2.992 12.871-.161 14.773 6.126 1.902 6.287-1.127 12.718-7.396 14.623L.057 24z" />
                                        </svg>
                                    </button>
                                    <button @click="remindSMS(debtor)" title="SMS Reminder"
                                        class="text-blue-600 hover:text-blue-800 transition-transform hover:scale-110">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

import { useReportStore } from '../../stores/report'
import { useAuthStore } from '../../stores/auth'
import { storeToRefs } from 'pinia'

const reportStore = useReportStore()
const authStore = useAuthStore()
const { loading } = storeToRefs(reportStore)

const debtors = ref<any[]>([])
const totalOutstanding = ref(0)
const filters = ref({
    min_balance: 1
})

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val)
}

const loadReport = async () => {
    try {
        const res = await reportStore.fetchOutstandingReport(filters.value)
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
    const phone = debtor.customer_contact // Assuming this might be phone. If it's email, this won't work well. logic in controller prefers phone.
    if (!phone || !phone.match(/[0-9]{10}/)) {
        alert('Valid customer phone number is missing.')
        return
    }
    const text = encodeURIComponent(getRemindText(debtor))
    window.open(`https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${text}`, '_blank')
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
