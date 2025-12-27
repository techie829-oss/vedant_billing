<template>
    <div class="space-y-6">
        <!-- Header / Filters -->
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Detailed Tax Report</label>
                        <p class="text-xs text-gray-500">GST Summary for ITR Filing</p>
                    </div>
                </div>
                <!-- Date Filters -->
                <div class="flex items-center gap-2">
                    <input type="date" v-model="filters.start_date"
                        class="block rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <span class="text-gray-500">-</span>
                    <input type="date" v-model="filters.end_date"
                        class="block rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <button @click="fetchReport"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Apply
                    </button>
                    <!-- Download/Print -->
                    <button @click="downloadCSV"
                        class="ml-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Export CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Table -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <div v-if="loading" class="p-12 flex justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <div v-else-if="reportData" class="flow-root">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Tax
                                    Rate</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                    Taxable Value</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                    Values (CGST + SGST)</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Total
                                    Tax</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="row in reportData.summary" :key="row.rate">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    GST @ {{ Number(row.rate) }}%
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                                    {{ formatCurrency(row.taxable_value) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                                    <div class="flex flex-col text-[10px]">
                                        <span>CGST: {{ formatCurrency(row.cgst_share) }}</span>
                                        <span>SGST: {{ formatCurrency(row.sgst_share) }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-gray-900 text-right">
                                    {{ formatCurrency(row.total_tax) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    Totals</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(reportData.totals.taxable) }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                    -
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-bold text-gray-900">
                                    {{ formatCurrency(reportData.totals.tax) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div v-else class="p-12 text-center text-gray-500">
                No data available based on filters.
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import client from '../../api/client'

const loading = ref(false)
const reportData = ref<any>(null)
const filters = ref({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().substring(0, 10),
    end_date: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().substring(0, 10)
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount)
}

const fetchReport = async () => {
    loading.value = true
    try {
        const response = await client.get('/reports/tax-summary', { params: filters.value })
        reportData.value = response.data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

const downloadCSV = () => {
    if (!reportData.value) return;

    // Simple client-side csv generation
    let csv = 'Tax Rate,Taxable Value,Total Tax\n';
    reportData.value.summary.forEach((row: any) => {
        csv += `GST ${row.rate}%,${row.taxable_value},${row.total_tax}\n`
    })
    csv += `Total,${reportData.value.totals.taxable},${reportData.value.totals.tax}\n`

    const blob = new Blob([csv], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `tax_report_${filters.value.start_date}.csv`
    a.click()
}

onMounted(() => {
    fetchReport()
})
</script>
