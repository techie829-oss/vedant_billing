<template>
    <div class="space-y-6">
        <!-- Filters & Summary Card -->
        <Card class="border-none shadow-sm overflow-hidden">
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-4 flex items-center gap-2">
                        <Checkbox v-model="filters.low_stock_only" :binary="true" id="low_stock" @change="loadReport" />
                        <label for="low_stock" class="font-semibold text-sm cursor-pointer">Show Low Stock Items Only</label>
                    </div>
                    <div class="md:col-span-8 flex justify-end">
                        <div class="bg-primary-50 p-4 rounded-xl border border-primary-100 flex flex-col items-end">
                            <span class="text-xs font-bold text-primary uppercase">Inventory Valuation</span>
                            <span class="text-3xl font-black text-primary-900">{{ formatCurrency(summary.total_valuation) }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Stock Data Table -->
        <Card class="border-none shadow-sm">
            <template #content>
                <DataTable :value="products" :loading="loading" dataKey="id" 
                    :paginator="true" :rows="10" 
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[10, 25, 50, 100]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} items"
                    responsiveLayout="stack" breakpoint="960px">
                    
                    <template #empty>No stock data available.</template>

                    <Column field="name" header="Product" sortable>
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900">{{ data.name }}</span>
                                <span class="text-xs text-gray-500" v-if="data.hsn_code">HSN: {{ data.hsn_code }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="sku" header="SKU" sortable style="width: 150px"></Column>

                    <Column field="purchase_price" header="Purchase Price" sortable style="text-align: right; width: 150px">
                        <template #body="{ data }">₹{{ Number(data.purchase_price).toFixed(2) }}</template>
                    </Column>

                    <Column field="sale_price" header="Sale Price" sortable style="text-align: right; width: 150px">
                        <template #body="{ data }">₹{{ Number(data.sale_price).toFixed(2) }}</template>
                    </Column>

                    <Column field="current_stock" header="Current Stock" sortable style="text-align: right; width: 180px">
                        <template #body="{ data }">
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-lg font-black" :class="Number(data.current_stock) <= 5 ? 'text-red-600' : 'text-green-600'">
                                    {{ data.current_stock }}
                                </span>
                                <Tag :value="data.unit" severity="secondary" size="small" class="text-[10px]" />
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
import { useReportStore } from '../../stores/report'
import { storeToRefs } from 'pinia'

// PrimeVue
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'

const reportStore = useReportStore()
const { loading } = storeToRefs(reportStore)

const products = ref<any[]>([])
const summary = ref({
    total_items: 0,
    total_stock_qty: 0,
    total_valuation: 0
})

const filters = reactive({
    low_stock_only: false
})

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val)
}

const loadReport = async () => {
    try {
        const res = await reportStore.fetchStockReport(filters)
        products.value = res.data
        summary.value = {
            total_items: res.total_items,
            total_stock_qty: res.total_stock_qty,
            total_valuation: res.total_valuation
        }
    } catch (e) {
        console.error(e)
    }
}

onMounted(async () => {
    await loadReport()
})
</script>
