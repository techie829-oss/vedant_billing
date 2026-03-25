<template>
    <div class="space-y-6">
        <div class="bg-white shadow sm:rounded-lg">
            <div
                class="px-6 py-5 border-b border-gray-200 sm:px-6 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50 gap-4">
                <div class="flex items-center space-x-4 w-full sm:w-auto">
                    <div class="relative flex items-start">
                        <div class="flex h-6 items-center">
                            <input id="low-stock" type="checkbox" v-model="filters.low_stock_only" @change="loadReport"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="low-stock" class="font-medium text-gray-900">Low Stock Only</label>
                        </div>
                    </div>
                </div>
                <div
                    class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm w-full sm:w-auto text-center sm:text-left">
                    Valuation: <span class="font-bold text-gray-900 text-lg ml-2">{{
                        formatCurrency(summary.total_valuation) }}</span>
                </div>
            </div>

            <!-- Data List -->
            <div>
                <!-- Mobile Card View -->
                <div class="sm:hidden space-y-4 px-4 pb-4">
                    <div v-if="loading && !products.length" class="text-center py-4 text-gray-500">Loading...</div>
                    <div v-for="product in products" :key="product.id"
                        class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="font-bold text-gray-900">{{ product.name }}</div>
                                <div class="text-xs text-gray-500">{{ product.sku || '' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold"
                                    :class="Number(product.current_stock) <= 5 ? 'text-red-600' : 'text-green-600'">
                                    {{ product.current_stock }} {{ product.unit }}
                                </div>
                                <div class="text-xs text-gray-500">Stock</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-sm border-t border-gray-100 pt-2 mt-2">
                            <div>
                                <span class="text-gray-500 text-xs">Buy:</span> {{
                                    formatCurrency(product.purchase_price) }}
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Sell:</span> {{ formatCurrency(product.sale_price)
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Purchase Price</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sales Price</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Current Stock</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="loading && !products.length">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                            </tr>
                            <tr v-for="product in products" :key="product.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                    <div class="text-xs text-gray-500">{{ product.hsn_code ? `HSN: ${product.hsn_code}`
                                        : '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ product.sku || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    {{ formatCurrency(product.purchase_price) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    {{ formatCurrency(product.sale_price) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right"
                                    :class="Number(product.current_stock) <= 5 ? 'text-red-600' : 'text-green-600'">
                                    {{ product.current_stock }} {{ product.unit }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useReportStore } from '../../stores/report'
import { storeToRefs } from 'pinia'

const reportStore = useReportStore()
const { loading } = storeToRefs(reportStore)

const products = ref<any[]>([])
const summary = ref({
    total_items: 0,
    total_stock_qty: 0,
    total_valuation: 0
})

const filters = ref({
    low_stock_only: false
})

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val)
}

const loadReport = async () => {
    try {
        const res = await reportStore.fetchStockReport(filters.value)
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
