<template>
    <AppLayout>
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Catalog Scan Details</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Ref: {{ scanData?.invoice_no || 'Unknown' }} | Vendor: {{ scanData?.vendor || 'Unknown' }}
                </p>
            </div>
            <div class="flex space-x-3">
                <button @click="$router.push('/invoice-scans')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Scans
                </button>
            </div>
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-sm text-gray-500">Loading scan details...</p>
        </div>

        <div v-else-if="scanData" class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Review Table -->
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Extracted Products</h3>

                <div class="space-y-6">
                    <div v-for="item in scanData.temp_products" :key="item.temp_product.id"
                        class="border rounded-lg p-4 bg-gray-50">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Scanned Data -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Scanned Item</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <dt class="text-xs font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ item.temp_product.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500">Quantity</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ item.temp_product.quantity }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500">Price</dt>
                                        <dd class="mt-1 text-sm text-gray-900">₹{{ item.temp_product.price }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Actions / Matches -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Action needed</h4>

                                <div v-if="item.temp_product.status === 'pending'" class="space-y-3">
                                    <!-- Matches -->
                                    <div v-if="item.suggested_matches && item.suggested_matches.length > 0"
                                        class="mb-3">
                                        <p class="text-xs text-gray-500 mb-2">Suggested Match:</p>
                                        <div v-for="match in item.suggested_matches" :key="match.product_id"
                                            class="flex items-center justify-between bg-white p-2 rounded border mb-2">
                                            <span class="text-sm text-gray-700">{{ match.name }} ({{
                                                Math.round(match.confidence * 100) }}%)</span>
                                            <button @click="matchProduct(item.temp_product.id, match.product_id)"
                                                class="text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded hover:bg-indigo-100">
                                                Match
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Manual Actions -->
                                    <div class="flex space-x-2">
                                        <button @click="addNewProduct(item.temp_product.id)"
                                            class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                                            Add as New
                                        </button>
                                        <button @click="rejectProduct(item.temp_product.id)"
                                            class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                            Reject
                                        </button>
                                    </div>
                                </div>

                                <div v-else class="flex items-center h-full">
                                    <span v-if="item.temp_product.status === 'matched'"
                                        class="text-green-600 font-medium flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Matched
                                    </span>
                                    <span v-else-if="item.temp_product.status === 'added'"
                                        class="text-blue-600 font-medium flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Added as New
                                    </span>
                                    <span v-else-if="item.temp_product.status === 'rejected'"
                                        class="text-red-600 font-medium flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Rejected
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

const route = useRoute()
const scanId = route.params.id as string

const loading = ref(true)
const scanData = ref<any>(null)

onMounted(() => {
    fetchScanDetails()
})

async function fetchScanDetails() {
    loading.value = true
    try {
        const response = await client.get(`/invoice-scans/${scanId}`)
        if (response.data.success && response.data.data) {
            scanData.value = response.data.data
        } else {
            // Handle error or redirect
            alert('Could not load scan details')
        }
    } catch (error) {
        console.error('Error fetching details:', error)
    } finally {
        loading.value = false
    }
}

async function matchProduct(tempId: string, productId: string) {
    try {
        await client.post(`/temp-products/${tempId}/match`, { product_id: productId })
        await fetchScanDetails() // Refresh to update status
    } catch (error: any) {
        alert(error.response?.data?.message || 'Match failed')
    }
}

async function addNewProduct(tempId: string) {
    try {
        await client.post(`/temp-products/${tempId}/add-new`)
        await fetchScanDetails()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Add failed')
    }
}

async function rejectProduct(tempId: string) {
    if (!confirm('Are you sure you want to reject this item?')) return
    try {
        await client.delete(`/temp-products/${tempId}`)
        await fetchScanDetails()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Reject failed')
    }
}
</script>
