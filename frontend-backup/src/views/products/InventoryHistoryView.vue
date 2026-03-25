<template>
    <AppLayout>
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Inventory History</h1>
                <p class="text-sm text-gray-500 mt-1">Track stock movements and purchase history.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button @click="$router.push('/products')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                    Back to Products
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Type</label>
                    <select v-model="filters.type"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Types</option>
                        <option value="purchase">Purchase (Stock In)</option>
                        <option value="sale">Sale (Stock Out)</option>
                        <option value="adjustment">Adjustment</option>
                        <option value="return">Return</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit
                            Price</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref /
                            Party</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="loading">
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Loading history...</td>
                    </tr>
                    <tr v-else-if="transactions.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No transactions found.</td>
                    </tr>
                    <tr v-for="t in transactions" :key="t.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ new Date(t.created_at).toLocaleDateString() }}
                            <span class="text-xs text-gray-400 block">{{ new Date(t.created_at).toLocaleTimeString()
                                }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ t.product?.name || 'Unknown Product' }}
                            <span class="text-xs text-gray-400 block">{{ t.product?.sku }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                :class="{
                                    'bg-green-100 text-green-800': t.type === 'purchase',
                                    'bg-red-100 text-red-800': t.type === 'sale',
                                    'bg-yellow-100 text-yellow-800': t.type === 'adjustment',
                                    'bg-gray-100 text-gray-800': t.type === 'return',
                                }">
                                {{ t.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-bold">
                            {{ t.type === 'sale' || (t.type === 'adjustment' && t.quantity < 0) ? '' : '+' }}{{
                                Number(t.quantity) }} </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                            {{ t.unit_price ? '₹' + Number(t.unit_price).toFixed(2) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div v-if="t.party" class="text-indigo-600 font-medium">{{ t.party.name }}</div>
                            <div v-else class="text-gray-400">-</div>

                            <div v-if="t.notes" class="text-xs text-gray-400 italic mt-0.5">{{ t.notes }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination could go here -->
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

const transactions = ref<any[]>([])
const loading = ref(false)
const filters = ref({
    type: ''
})

const fetchHistory = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (filters.value.type) params.type = filters.value.type

        const res = await client.get('/inventory', { params })
        transactions.value = res.data.data
    } catch (e) {
        console.error("Failed to load inventory history", e)
    } finally {
        loading.value = false
    }
}

watch(() => filters.value.type, () => {
    fetchHistory()
})

onMounted(() => {
    fetchHistory()
})
</script>
