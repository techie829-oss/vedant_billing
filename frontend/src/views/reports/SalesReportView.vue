<template>
    <div class="space-y-6">
        <div class="bg-white shadow sm:rounded-lg">
            <!-- Filters -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" v-model="filters.start_date" class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                <!-- ... content ... -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" v-model="filters.end_date" class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                        <div class="relative">
                             <select v-model="filters.customer_id" class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                               <option value="">All Customers</option>
                               <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                             </select>
                             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                 <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                     <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                 </svg>
                             </div>
                        </div>
                    </div>
                    <div>
                        <button @click="loadReport" :disabled="loading" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            {{ loading ? 'Loading...' : 'Generate Report' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div v-if="totals" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-6 border-b border-gray-200">
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-indigo-600">Total Invoices</div>
                    <div class="mt-1 text-2xl font-semibold text-indigo-900">{{ totals.count }}</div>
                </div>
                 <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-green-600">Total Sales</div>
                    <div class="mt-1 text-2xl font-semibold text-green-900">{{ formatCurrency(totals.total_amount) }}</div>
                </div>
                 <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-yellow-600">Balance Due</div>
                    <div class="mt-1 text-2xl font-semibold text-yellow-900">{{ formatCurrency(totals.balance_due) }}</div>
                </div>
                 <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-gray-600">Tax Collected</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ formatCurrency(totals.tax_collected) }}</div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="loading && !invoices.length">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                        <tr v-else-if="!invoices.length">
                             <td colspan="5" class="px-6 py-4 text-center text-gray-500">No invoices found for this period.</td>
                        </tr>
                        <tr v-for="inv in invoices" :key="inv.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ inv.date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                 <router-link :to="`/invoices/${inv.id}`">{{ inv.invoice_number }}</router-link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ inv.party?.name || 'Unknown' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize" 
                                      :class="{
                                          'bg-green-100 text-green-800': inv.status === 'paid',
                                          'bg-yellow-100 text-yellow-800': inv.status === 'sent' || inv.status === 'partial',
                                          'bg-red-100 text-red-800': inv.status === 'overdue'
                                      }">
                                    {{ inv.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ formatCurrency(inv.grand_total) }}</td>
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
import { usePartyStore } from '../../stores/party'
import { storeToRefs } from 'pinia'

const reportStore = useReportStore()
const partyStore = usePartyStore()
const { loading } = storeToRefs(reportStore)

const invoices = ref<any[]>([])
const totals = ref<any>(null)
const customers = ref<any[]>([])

const filters = ref({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0], // Start of month
    end_date: new Date().toISOString().split('T')[0],
    customer_id: ''
})

const loadReport = async () => {
    try {
        const res = await reportStore.fetchSalesReport(filters.value)
        invoices.value = res.data
        totals.value = res.totals
    } catch (e) {
        console.error(e)
    }
}

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val)
}

onMounted(async () => {
    // Load customers for filter
    try {
        await partyStore.fetchParties({ type: 'customer', per_page: 100 })
        customers.value = partyStore.parties
    } catch (e) { console.error(e) }

    await loadReport()
})
</script>
