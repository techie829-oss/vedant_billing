<template>
    <div class="space-y-6 p-fluid">
        <!-- Filters Card -->
        <Card class="border-none shadow-sm overflow-hidden">
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">Start Date</label>
                        <DatePicker v-model="startDate" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-xs uppercase text-gray-500">End Date</label>
                        <DatePicker v-model="endDate" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="md:col-span-2">
                        <Button label="Update Statement" icon="pi pi-refresh" @click="loadData" :loading="loading" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
            <ProgressSpinner style="width: 50px; height: 50px" />
            <p class="mt-4 text-gray-500">Calculating your financial performance...</p>
        </div>

        <template v-else-if="reportData">
            <!-- Summary KPI Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="border-none shadow-sm bg-green-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-green-600 uppercase">Total Income</span>
                            <span class="text-3xl font-black text-green-900">{{ formatCurrency(reportData.income.total) }}</span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm bg-red-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-red-600 uppercase">Total Expenses</span>
                            <span class="text-3xl font-black text-red-900">{{ formatCurrency(reportData.expenses.total) }}</span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm" :class="reportData.net_profit >= 0 ? 'bg-indigo-50' : 'bg-red-100'">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold uppercase" :class="reportData.net_profit >= 0 ? 'text-indigo-600' : 'text-red-700'">Net Profit</span>
                            <span class="text-3xl font-black" :class="reportData.net_profit >= 0 ? 'text-indigo-900' : 'text-red-900'">
                                {{ formatCurrency(reportData.net_profit) }}
                            </span>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Detailed Breakdowns -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Income Breakdown -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <template #title>Income Breakdown</template>
                    <template #content>
                        <DataTable :value="reportData.income.accounts" class="p-datatable-sm">
                            <Column field="name" header="Account / Source"></Column>
                            <Column field="amount" header="Amount" style="text-align: right">
                                <template #body="{ data }">
                                    <span class="font-bold text-green-600">{{ formatCurrency(data.amount) }}</span>
                                </template>
                            </Column>
                            <template #footer>
                                <div class="flex justify-between items-center px-2">
                                    <span class="font-bold text-lg">Total Income</span>
                                    <span class="font-black text-lg text-green-700">{{ formatCurrency(reportData.income.total) }}</span>
                                </div>
                            </template>
                        </DataTable>
                    </template>
                </Card>

                <!-- Expense Breakdown -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <template #title>Expense Breakdown</template>
                    <template #content>
                        <DataTable :value="reportData.expenses.accounts" class="p-datatable-sm">
                            <Column field="name" header="Expense Category"></Column>
                            <Column field="amount" header="Amount" style="text-align: right">
                                <template #body="{ data }">
                                    <span class="font-bold text-red-600">{{ formatCurrency(data.amount) }}</span>
                                </template>
                            </Column>
                            <template #footer>
                                <div class="flex justify-between items-center px-2">
                                    <span class="font-bold text-lg">Total Expenses</span>
                                    <span class="font-black text-lg text-red-700">{{ formatCurrency(reportData.expenses.total) }}</span>
                                </div>
                            </template>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useReportStore } from '../../stores/report'
import { useAuthStore } from '../../stores/auth'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import DatePicker from 'primevue/datepicker'
import ProgressSpinner from 'primevue/progressspinner'

const reportStore = useReportStore()
const authStore = useAuthStore()

const loading = ref(false)
const reportData = ref<any>(null)

// Default to current month
const now = new Date()
const startDate = ref(new Date(now.getFullYear(), now.getMonth(), 1))
const endDate = ref(new Date(now.getFullYear(), now.getMonth() + 1, 0))

const loadData = async () => {
    loading.value = true
    try {
        const res = await reportStore.fetchProfitLoss({
            start_date: startDate.value.toISOString().split('T')[0],
            end_date: endDate.value.toISOString().split('T')[0]
        })
        reportData.value = res
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: authStore.activeBusiness?.currency || 'INR'
    }).format(value)
}

onMounted(() => {
    loadData()
})
</script>
