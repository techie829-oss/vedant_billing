<template>
    <div class="space-y-6">
        <!-- Filters Card -->
        <Card class="border-none shadow-sm overflow-hidden">
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-3 flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">Start Date</label>
                        <DatePicker v-model="filters.start_date" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="md:col-span-3 flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">End Date</label>
                        <DatePicker v-model="filters.end_date" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="md:col-span-6 flex gap-2">
                        <Button label="Apply Filters" icon="pi pi-filter" @click="fetchReport" :loading="loading" class="flex-1" />
                        <Button label="Export CSV" icon="pi pi-download" severity="secondary" outlined @click="downloadCSV" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
            <ProgressSpinner style="width: 50px; height: 50px" />
            <p class="mt-4 text-gray-500">Compiling tax records...</p>
        </div>

        <template v-else-if="reportData">
            <!-- Totals KPI Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Card class="border-none shadow-sm bg-indigo-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-indigo-600 uppercase">Total Taxable Value</span>
                            <span class="text-3xl font-black text-indigo-900">{{ formatCurrency(reportData.totals.taxable) }}</span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm bg-primary-900 text-white">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold uppercase opacity-80">Total Tax Output (GST)</span>
                            <span class="text-3xl font-black">{{ formatCurrency(reportData.totals.tax) }}</span>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Tax Summary Table -->
            <Card class="border-none shadow-sm overflow-hidden">
                <template #title>GST Summary Breakdown</template>
                <template #content>
                    <DataTable :value="reportData.summary" class="p-datatable-sm" responsiveLayout="stack" breakpoint="960px">
                        <template #empty>No tax records found for the selected period.</template>

                        <Column field="rate" header="Tax Rate" sortable>
                            <template #body="{ data }">
                                <Tag :value="'GST @ ' + Number(data.rate) + '%'" severity="info" rounded />
                            </template>
                        </Column>

                        <Column field="taxable_value" header="Taxable Amount" style="text-align: right">
                            <template #body="{ data }">
                                <span class="font-medium text-gray-900">{{ formatCurrency(data.taxable_value) }}</span>
                            </template>
                        </Column>

                        <Column header="Tax Split (CGST + SGST)" style="text-align: right">
                            <template #body="{ data }">
                                <div class="flex flex-col items-end gap-1">
                                    <span class="text-[10px] text-gray-500 uppercase">CGST: {{ formatCurrency(data.cgst_share) }}</span>
                                    <span class="text-[10px] text-gray-500 uppercase">SGST: {{ formatCurrency(data.sgst_share) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="total_tax" header="Total Tax" style="text-align: right">
                            <template #body="{ data }">
                                <span class="font-black text-primary">{{ formatCurrency(data.total_tax) }}</span>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-between items-center px-4 py-2 border-t border-gray-100">
                                <span class="font-bold text-lg">Total Tax Output</span>
                                <span class="font-black text-xl text-primary">{{ formatCurrency(reportData.totals.tax) }}</span>
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import client from '../../api/client'

// PrimeVue
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import DatePicker from 'primevue/datepicker'
import Tag from 'primevue/tag'
import ProgressSpinner from 'primevue/progressspinner'

const loading = ref(false)
const reportData = ref<any>(null)

const filters = reactive({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    end_date: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)
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
        const params = {
            start_date: filters.start_date.toISOString().split('T')[0],
            end_date: filters.end_date.toISOString().split('T')[0]
        }
        const response = await client.get('/reports/tax-summary', { params })
        reportData.value = response.data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

const downloadCSV = () => {
    if (!reportData.value) return;

    let csv = 'Tax Rate,Taxable Value,Total Tax\n';
    reportData.value.summary.forEach((row: any) => {
        csv += `GST ${row.rate}%,${row.taxable_value},${row.total_tax}\n`
    })
    csv += `Total,${reportData.value.totals.taxable},${reportData.value.totals.tax}\n`

    const blob = new Blob([csv], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `tax_report_${filters.start_date.toISOString().split('T')[0]}.csv`
    a.click()
}

onMounted(() => {
    fetchReport()
})
</script>
