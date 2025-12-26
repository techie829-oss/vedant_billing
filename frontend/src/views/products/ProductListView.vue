<template>
    <AppLayout>
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Products & Services</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your products, services, prices and stock.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <router-link to="/products/create" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                     <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Product
                </router-link>
            </div>
        </div>
        
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">SKU</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Price</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Stock</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody v-if="loading" class="divide-y divide-gray-200 bg-white">
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-gray-500">Loading products...</td>
                                </tr>
                            </tbody>
                             <tbody v-else-if="products.length === 0" class="divide-y divide-gray-200 bg-white">
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-gray-500">No products found. Add one to get started.</td>
                                </tr>
                            </tbody>
                            <tbody v-else class="divide-y divide-gray-200 bg-white">
                                <tr v-for="product in products" :key="product.id">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ product.name }}
                                        <div class="text-xs text-gray-500 font-normal mt-0.5" v-if="product.hsn_code">HSN: {{ product.hsn_code }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ product.sku || '-' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 capitalize">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset" :class="product.type === 'goods' ? 'bg-blue-50 text-blue-700 ring-blue-700/10' : 'bg-purple-50 text-purple-700 ring-purple-700/10'">
                                            {{ product.type }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 text-right">₹{{ Number(product.sale_price).toFixed(2) }}</td>
                                     <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                                        <span v-if="product.type === 'goods'">{{ Number(product.current_stock || 0) }} {{ product.unit || '' }}</span>
                                        <span v-else>-</span>
                                     </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset" :class="product.status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/10'">
                                            {{ product.status }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <router-link :to="`/products/${product.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Edit<span class="sr-only">, {{ product.name }}</span></router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useProductStore } from '../../stores/product'
import AppLayout from '../../layouts/AppLayout.vue'

const productStore = useProductStore()
const { products, loading } = storeToRefs(productStore)

onMounted(() => {
    productStore.fetchProducts()
})
</script>
