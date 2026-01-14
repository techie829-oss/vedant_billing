<template>
    <AppLayout>
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Products & Services</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your products, services, prices and stock.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                <router-link to="/invoice-scans"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-3 sm:py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors relative">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Catalog Scans
                    <span v-if="pendingCount > 0"
                        class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">{{
                        pendingCount }}</span>
                </router-link>
                <router-link to="/products/create"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-3 sm:py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Product
                </router-link>
            </div>
        </div>

        <div class="mt-8 flow-root">
            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-gray-100 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div v-if="loading" class="p-4 text-center text-sm text-gray-500">Loading products...</div>
                <div v-else-if="products.length === 0" class="p-4 text-center text-sm text-gray-500">No products found.
                    Add one to get started.</div>
                <div v-for="product in products" :key="product.id"
                    class="p-3 hover:bg-gray-50 flex flex-col gap-2 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="pr-2">
                            <div class="font-bold text-gray-900 text-sm truncate max-w-[200px]">{{ product.name }}</div>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span
                                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset"
                                    :class="product.type === 'goods' ? 'bg-blue-50 text-blue-700 ring-blue-700/10' : 'bg-purple-50 text-purple-700 ring-purple-700/10'">
                                    {{ product.type }}
                                </span>
                                <span class="text-[10px] text-gray-400" v-if="product.hsn_code">HSN: {{ product.hsn_code
                                }}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-bold text-gray-900 text-sm">₹{{ Number(product.sale_price).toFixed(2) }}
                            </div>
                            <div class="text-[10px] text-gray-500 mt-0.5">
                                <span v-if="product.type === 'goods' && product.current_stock !== undefined">
                                    <span
                                        :class="Number(product.current_stock) > 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                                        {{ Number(product.current_stock) }}
                                    </span>
                                    {{ product.unit }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-1.5 border-t border-gray-100 mt-1">
                        <span
                            class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset capitalize"
                            :class="product.status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/10'">
                            {{ product.status }}
                        </span>
                        <router-link :to="`/products/${product.id}/edit`"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-900 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </router-link>
                    </div>
                </div>
            </div>

            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8 hidden sm:block">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                        Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        SKU</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Type</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                        Price</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                        Stock</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody v-if="loading" class="divide-y divide-gray-200 bg-white">
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-gray-500">Loading products...
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else-if="products.length === 0" class="divide-y divide-gray-200 bg-white">
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-gray-500">No products found.
                                        Add one to get started.</td>
                                </tr>
                            </tbody>
                            <tbody v-else class="divide-y divide-gray-200 bg-white">
                                <tr v-for="product in products" :key="product.id">
                                    <td
                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ product.name }}
                                        <div class="text-xs text-gray-500 font-normal mt-0.5" v-if="product.hsn_code">
                                            HSN: {{ product.hsn_code }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ product.sku || '-'
                                    }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 capitalize">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                            :class="product.type === 'goods' ? 'bg-blue-50 text-blue-700 ring-blue-700/10' : 'bg-purple-50 text-purple-700 ring-purple-700/10'">
                                            {{ product.type }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 text-right">₹{{
                                        Number(product.sale_price).toFixed(2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                                        <span v-if="product.type === 'goods'">{{ Number(product.current_stock || 0) }}
                                            {{ product.unit || '' }}</span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                            :class="product.status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/10'">
                                            {{ product.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <router-link :to="`/products/${product.id}/edit`"
                                            class="text-indigo-600 hover:text-indigo-900">Edit<span class="sr-only">, {{
                                                product.name }}</span></router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan Invoice Modal -->
        <div v-if="showScanModal" class="relative z-50" @click.self="showScanModal = false">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
                        <div>
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Scan Purchase Invoice</h3>
                            <p class="mt-1 text-sm text-gray-500">Upload an invoice image to automatically extract
                                products</p>
                        </div>

                        <!-- File Upload -->
                        <div v-if="!scanning && !scanResult" class="mt-6">
                            <input type="file" ref="fileInput" accept="image/*,application/pdf"
                                @change="handleFileSelect" class="hidden">
                            <div @click="fileInput?.click()"
                                class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 cursor-pointer hover:border-indigo-500 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                        <span class="font-semibold text-indigo-600">Click to upload invoice</span>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-600">PNG, JPG up to 10MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Scanning State -->
                        <div v-if="scanning" class="mt-6 text-center py-10">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600">
                            </div>
                            <p class="mt-4 text-sm font-medium text-gray-900">Processing Invoice...</p>
                            <p class="mt-2 text-xs text-gray-500">
                                This usually takes 10-20 seconds<br>
                                <span class="text-gray-400">Using Tesseract OCR + Llama 3 AI</span>
                            </p>
                            <div class="mt-6 max-w-md mx-auto">
                                <div class="flex items-start space-x-3 text-left">
                                    <svg class="h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-xs text-gray-600">
                                        <p class="font-medium">Image uploaded successfully</p>
                                        <p class="text-gray-400">Background job is processing...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error State -->
                        <div v-if="scanError" class="mt-6 rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Scan failed</h3>
                                    <p class="mt-1 text-sm text-red-700">{{ scanError }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Scanned Products Review -->
                        <div v-if="scanResult && scanResult.temp_products && scanResult.temp_products.length > 0"
                            class="mt-6">
                            <div class="mb-4 rounded-md bg-green-50 p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">{{
                                            scanResult.temp_products.length }} products extracted</h3>
                                        <p class="mt-1 text-sm text-green-700">Vendor: {{ scanResult.vendor || 'Unknown'
                                            }} | Invoice: {{ scanResult.invoice_no || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Product</th>
                                            <th
                                                class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Qty</th>
                                            <th
                                                class="px-3 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Price</th>
                                            <th
                                                class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Match</th>
                                            <th
                                                class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="item in scanResult.temp_products" :key="item.temp_product.id">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                                <div class="font-medium text-gray-900">{{ item.temp_product.name }}
                                                </div>
                                                <div class="text-xs text-gray-500">SKU: {{ item.temp_product.sku || '-'
                                                    }}</div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                item.temp_product.quantity }} {{ item.temp_product.unit }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 text-right">₹{{
                                                Number(item.temp_product.price).toFixed(2) }}</td>
                                            <td class="px-3 py-4 text-sm">
                                                <select v-model="item.selectedMatch"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                    <option :value="null">Add as New Product</option>
                                                    <option v-for="match in item.suggested_matches"
                                                        :key="match.product_id" :value="match.product_id">
                                                        {{ match.name }} ({{ (match.confidence * 100).toFixed(0) }}%
                                                        match)
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-4 text-sm text-center space-x-2">
                                                <button @click="approveProduct(item)"
                                                    :disabled="processingIds.includes(item.temp_product.id)"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 disabled:opacity-50">
                                                    <svg v-if="processingIds.includes(item.temp_product.id)"
                                                        class="animate-spin -ml-0.5 mr-1 h-3 w-3 text-white"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    {{ processingIds.includes(item.temp_product.id) ? 'Adding...' :
                                                        'Approve' }}
                                                </button>
                                                <button @click="rejectProduct(item.temp_product.id)"
                                                    :disabled="processingIds.includes(item.temp_product.id)"
                                                    class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button @click="closeScanModal" type="button"
                                class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useProductStore } from '../../stores/product'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

const productStore = useProductStore()
const { products, loading } = storeToRefs(productStore)

const showScanModal = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const scanning = ref(false)
const scanError = ref('')
const scanResult = ref<any>(null)
const processingIds = ref<string[]>([])
const pendingCount = ref(0)

onMounted(async () => {
    await productStore.fetchProducts()
    await fetchPendingCount()
})

async function fetchPendingCount() {
    try {
        const response = await client.get('/temp-products')
        pendingCount.value = response.data.data?.length || 0
    } catch (error) {
        console.error('Failed to fetch pending count:', error)
    }
}

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    // Validate file
    if (!file.type.startsWith('image/')) {
        scanError.value = 'Please select an image file'
        return
    }

    if (file.size > 10 * 1024 * 1024) {
        scanError.value = 'File size must be less than 10MB'
        return
    }

    scanInvoice(file)
}

async function scanInvoice(file: File) {
    scanning.value = true
    scanError.value = ''
    scanResult.value = null

    const formData = new FormData()
    formData.append('invoice', file)

    try {
        // Step 1: Upload file (fast)
        const uploadResponse = await client.post('/products/scan-invoice', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (!uploadResponse.data.success) {
            scanError.value = uploadResponse.data.message || 'Upload failed'
            return
        }

        const scanId = uploadResponse.data.scan_id

        // Step 2: Poll for status (with timeout after 3 minutes)
        const maxAttempts = 36 // 36 * 5s = 3 minutes
        let attempts = 0

        const pollInterval = setInterval(async () => {
            attempts++

            try {
                const statusResponse = await client.get(`/invoice-scans/${scanId}`)

                if (statusResponse.data.status === 'success') {
                    // Processing complete!
                    clearInterval(pollInterval)
                    scanning.value = false
                    scanResult.value = statusResponse.data.data

                    // Initialize selectedMatch for each item
                    scanResult.value.temp_products.forEach((item: any) => {
                        item.selectedMatch = item.temp_product.matched_product_id || null
                    })
                } else if (statusResponse.data.status === 'failed') {
                    // Processing failed
                    clearInterval(pollInterval)
                    scanning.value = false
                    scanError.value = statusResponse.data.error || 'Failed to process invoice'
                } else if (statusResponse.data.status === 'pending') {
                    // Still processing...
                    if (attempts >= maxAttempts) {
                        clearInterval(pollInterval)
                        scanning.value = false
                        scanError.value = 'Processing timeout. The scan is still running in background. Please refresh later.'
                    }
                }
            } catch (error: any) {
                console.error('Polling error:', error)
                if (attempts >= maxAttempts) {
                    clearInterval(pollInterval)
                    scanning.value = false
                    scanError.value = 'Failed to check scan status'
                }
            }
        }, 5000) // Poll every 5 seconds

    } catch (error: any) {
        scanning.value = false
        scanError.value = error.response?.data?.message || 'Failed to upload invoice'
    }
}

async function approveProduct(item: any) {
    const tempProductId = item.temp_product.id
    processingIds.value.push(tempProductId)

    try {
        if (item.selectedMatch) {
            // Match to existing product
            await client.post(`/temp-products/${tempProductId}/match`, {
                product_id: item.selectedMatch,
                update_inventory: true
            })
        } else {
            // Add as new product
            await client.post(`/temp-products/${tempProductId}/add-new`, {
                update_inventory: true
            })
        }

        // Remove from list
        const index = scanResult.value.temp_products.findIndex((p: any) => p.temp_product.id === tempProductId)
        if (index > -1) {
            scanResult.value.temp_products.splice(index, 1)
        }

        // Refresh products list
        await productStore.fetchProducts()
        await fetchPendingCount()

    } catch (error: any) {
        alert(error.response?.data?.message || 'Failed to process product')
    } finally {
        processingIds.value = processingIds.value.filter(id => id !== tempProductId)
    }
}

async function rejectProduct(tempProductId: string) {
    processingIds.value.push(tempProductId)

    try {
        await client.delete(`/temp-products/${tempProductId}`)

        // Remove from list
        const index = scanResult.value.temp_products.findIndex((p: any) => p.temp_product.id === tempProductId)
        if (index > -1) {
            scanResult.value.temp_products.splice(index, 1)
        }

        await fetchPendingCount()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Failed to reject product')
    } finally {
        processingIds.value = processingIds.value.filter(id => id !== tempProductId)
    }
}

function closeScanModal() {
    showScanModal.value = false
    scanResult.value = null
    scanError.value = ''
    scanning.value = false
}
</script>
