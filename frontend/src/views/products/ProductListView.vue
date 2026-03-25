<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Products & Services</h1>
                    <p class="text-gray-500 mt-1">Manage your inventory, pricing, and stock levels.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button label="History" icon="pi pi-history" severity="secondary" outlined 
                        @click="router.push('/inventory/history')" />
                    <Button label="Catalog Scans" icon="pi pi-camera" severity="secondary" outlined 
                        :badge="pendingCount > 0 ? pendingCount.toString() : undefined"
                        badgeSeverity="danger"
                        @click="router.push('/invoice-scans')" />
                    <Button label="Scan Catalog" icon="pi pi-expand" severity="info" 
                        @click="showScanModal = true" />
                    <Button label="Add Product" icon="pi pi-plus" 
                        @click="router.push('/products/create')" />
                </div>
            </div>

            <!-- Main Data Table -->
            <Card>
                <template #content>
                    <DataTable :value="products" :loading="loading" dataKey="id" 
                        :paginator="true" :rows="10" 
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        :rowsPerPageOptions="[10, 25, 50]"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products"
                        responsiveLayout="stack" breakpoint="960px"
                        v-model:filters="filters" filterDisplay="menu"
                        :globalFilterFields="['name', 'sku', 'hsn_code']">
                        
                        <template #header>
                            <div class="flex justify-between items-center">
                                <span class="p-input-icon-left">
                                    <i class="pi pi-search" />
                                    <InputText v-model="filters['global'].value" placeholder="Search products..." class="w-full md:w-80" />
                                </span>
                            </div>
                        </template>

                        <template #empty>No products found.</template>

                        <Column field="name" header="Name" sortable>
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ data.name }}</span>
                                    <span class="text-xs text-gray-500" v-if="data.hsn_code">HSN: {{ data.hsn_code }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="sku" header="SKU" sortable></Column>

                        <Column field="type" header="Type" sortable>
                            <template #body="{ data }">
                                <Tag :value="data.type" :severity="data.type === 'goods' ? 'info' : 'help'" />
                            </template>
                        </Column>

                        <Column field="sale_price" header="Sale Price" sortable>
                            <template #body="{ data }">
                                <span class="font-semibold">₹{{ Number(data.sale_price).toFixed(2) }}</span>
                            </template>
                        </Column>

                        <Column field="current_stock" header="Stock" sortable>
                            <template #body="{ data }">
                                <div v-if="data.type === 'goods'" class="flex items-center justify-between gap-4">
                                    <div>
                                        <span :class="Number(data.current_stock || 0) > 0 ? 'text-gray-900 font-medium' : 'text-red-600 font-bold'">
                                            {{ Number(data.current_stock || 0) }}
                                        </span>
                                        <span class="text-xs ml-1 text-gray-500">{{ data.unit }}</span>
                                    </div>
                                    <div class="flex gap-1">
                                        <Button icon="pi pi-plus" size="small" severity="success" text rounded 
                                            @click="openStockModal(data, 'purchase')" v-tooltip.top="'Add Stock'" />
                                        <Button icon="pi pi-minus" size="small" severity="danger" text rounded 
                                            @click="openStockModal(data, 'adjustment')" v-tooltip.top="'Reduce Stock'" />
                                    </div>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>

                        <Column field="status" header="Status" sortable>
                            <template #body="{ data }">
                                <Tag :value="data.status" :severity="data.status === 'active' ? 'success' : 'danger'" />
                            </template>
                        </Column>

                        <Column header="Actions" headerStyle="width: 5rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
                            <template #body="{ data }">
                                <Button icon="pi pi-pencil" severity="secondary" rounded text @click="router.push(`/products/${data.id}/edit`)" />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Scan Invoice Modal (Refactored with PrimeVue) -->
        <Dialog v-model:visible="showScanModal" header="Scan Purchase Invoice" :style="{ width: '50vw' }" :modal="true" :breakpoints="{'960px': '75vw', '641px': '100vw'}">
            <div class="flex flex-col gap-4">
                <p class="text-gray-500">Upload an invoice image to automatically extract products using AI.</p>
                
                <div v-if="!scanning && !scanResult" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl py-10 px-4 hover:border-primary transition-colors cursor-pointer" @click="fileInput?.click()">
                    <i class="pi pi-cloud-upload text-4xl text-gray-400 mb-4"></i>
                    <span class="font-semibold text-lg">Click to upload or drag & drop</span>
                    <span class="text-sm text-gray-400">PNG, JPG or PDF up to 10MB</span>
                    <input type="file" ref="fileInput" accept="image/*,application/pdf" @change="handleFileSelect" class="hidden">
                </div>

                <div v-if="scanning" class="text-center py-10">
                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" animationDuration=".5s" />
                    <p class="mt-4 font-semibold">Processing Invoice with AI...</p>
                    <p class="text-sm text-gray-500">This usually takes 10-20 seconds.</p>
                </div>

                <Message v-if="scanError" severity="error">{{ scanError }}</Message>

                <div v-if="scanResult && scanResult.temp_products && scanResult.temp_products.length > 0">
                    <div class="bg-blue-50 p-3 rounded-lg mb-4 flex items-center justify-between">
                        <span class="text-blue-800 font-medium">{{ scanResult.temp_products.length }} products found</span>
                        <span class="text-xs text-blue-600">{{ scanResult.vendor || 'Unknown Vendor' }}</span>
                    </div>

                    <DataTable :value="scanResult.temp_products" size="small" scrollable scrollHeight="400px">
                        <Column field="temp_product.name" header="Product"></Column>
                        <Column field="temp_product.quantity" header="Qty">
                            <template #body="{ data }">
                                {{ data.temp_product.quantity }} {{ data.temp_product.unit }}
                            </template>
                        </Column>
                        <Column field="temp_product.price" header="Price">
                            <template #body="{ data }">
                                ₹{{ Number(data.temp_product.price).toFixed(2) }}
                            </template>
                        </Column>
                        <Column header="Match / Action">
                            <template #body="{ data }">
                                <div class="flex flex-col gap-2">
                                    <Select v-model="data.selectedMatch" :options="data.suggested_matches" optionLabel="name" optionValue="product_id" 
                                        placeholder="Add as New" size="small" showClear class="w-full" />
                                    <div class="flex gap-1">
                                        <Button icon="pi pi-check" size="small" severity="success" @click="approveProduct(data)" :loading="processingIds.includes(data.temp_product.id)" />
                                        <Button icon="pi pi-trash" size="small" severity="danger" text @click="rejectProduct(data.temp_product.id)" :loading="processingIds.includes(data.temp_product.id)" />
                                    </div>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times" text @click="closeScanModal" />
            </template>
        </Dialog>

        <StockAdjustmentModal :isOpen="showStockModal" :product="selectedProduct" :type="stockActionType"
            @close="showStockModal = false" @saved="onStockUpdated" />
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useProductStore } from '../../stores/product'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'
import StockAdjustmentModal from './StockAdjustmentModal.vue'

// PrimeVue Components
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import ProgressSpinner from 'primevue/progressspinner'
import Message from 'primevue/message'
import Select from 'primevue/select'
import { FilterMatchMode } from '@primevue/core/api'

const router = useRouter()
const productStore = useProductStore()
const { products, loading } = storeToRefs(productStore)

const showScanModal = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const scanning = ref(false)
const scanError = ref('')
const scanResult = ref<any>(null)
const processingIds = ref<string[]>([])
const pendingCount = ref(0)

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

// Stock Adjustment
const showStockModal = ref(false)
const selectedProduct = ref<any>(null)
const stockActionType = ref<'purchase' | 'adjustment'>('purchase')

const openStockModal = (product: any, type: 'purchase' | 'adjustment') => {
    selectedProduct.value = product
    stockActionType.value = type
    showStockModal.value = true
}

const onStockUpdated = async () => {
    await productStore.fetchProducts()
}

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
    scanInvoice(file)
}

async function scanInvoice(file: File) {
    scanning.value = true
    scanError.value = ''
    scanResult.value = null

    const formData = new FormData()
    formData.append('invoice', file)

    try {
        const uploadResponse = await client.post('/products/scan-invoice', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (!uploadResponse.data.success) {
            scanError.value = uploadResponse.data.message || 'Upload failed'
            scanning.value = false
            return
        }

        const scanId = uploadResponse.data.scan_id

        // Polling
        const pollInterval = setInterval(async () => {
            try {
                const statusResponse = await client.get(`/invoice-scans/${scanId}`)
                if (statusResponse.data.status === 'success') {
                    clearInterval(pollInterval)
                    scanning.value = false
                    scanResult.value = statusResponse.data.data
                    scanResult.value.temp_products.forEach((item: any) => {
                        item.selectedMatch = item.temp_product.matched_product_id || null
                    })
                } else if (statusResponse.data.status === 'failed') {
                    clearInterval(pollInterval)
                    scanning.value = false
                    scanError.value = statusResponse.data.error || 'Failed to process'
                }
            } catch (error) {
                clearInterval(pollInterval)
                scanning.value = false
                scanError.value = 'Polling error'
            }
        }, 3000)

    } catch (error: any) {
        scanning.value = false
        scanError.value = error.response?.data?.message || 'Failed to upload'
    }
}

async function approveProduct(item: any) {
    const tempProductId = item.temp_product.id
    processingIds.value.push(tempProductId)
    try {
        if (item.selectedMatch) {
            await client.post(`/temp-products/${tempProductId}/match`, {
                product_id: item.selectedMatch,
                update_inventory: true
            })
        } else {
            await client.post(`/temp-products/${tempProductId}/add-new`, {
                update_inventory: true
            })
        }
        const index = scanResult.value.temp_products.findIndex((p: any) => p.temp_product.id === tempProductId)
        if (index > -1) scanResult.value.temp_products.splice(index, 1)
        await productStore.fetchProducts()
        await fetchPendingCount()
    } catch (error) {
        alert('Action failed')
    } finally {
        processingIds.value = processingIds.value.filter(id => id !== tempProductId)
    }
}

async function rejectProduct(tempProductId: string) {
    processingIds.value.push(tempProductId)
    try {
        await client.delete(`/temp-products/${tempProductId}`)
        const index = scanResult.value.temp_products.findIndex((p: any) => p.temp_product.id === tempProductId)
        if (index > -1) scanResult.value.temp_products.splice(index, 1)
        await fetchPendingCount()
    } catch (error) {
        alert('Delete failed')
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
