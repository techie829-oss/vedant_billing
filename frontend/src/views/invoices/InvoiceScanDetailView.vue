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
                <button v-if="canCreateInvoice" @click="showInvoiceModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Create Purchase Invoice
                </button>
                <router-link v-else-if="scanData?.invoice_id" :to="`/purchases/${scanData.invoice_id}/edit`"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    View Purchase Invoice
                </router-link>
                <button @click="$router.push('/invoice-scans')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Scans
                </button>
            </div>
        </div>

        <!-- Purchase Invoice Created Banner -->
        <div v-if="invoiceCreated"
            class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center justify-between">
            <div class="flex items-center text-green-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">Purchase invoice created successfully!</span>
            </div>
            <router-link to="/purchases" class="text-sm font-medium text-green-700 underline hover:text-green-900">
                View Purchase Invoices →
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-sm text-gray-500">Loading scan details...</p>
        </div>

        <div v-else-if="scanData" class="space-y-6">
            <!-- Vendor Information Card -->
            <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                <div class="px-4 py-4 sm:p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Vendor Information</h3>
                    <div
                        class="border rounded-lg p-4 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Scanned Vendor
                                Name</h4>
                            <p class="text-lg font-bold text-gray-900">{{ scanData.vendor || 'Unknown Vendor' }}</p>
                            <p class="text-sm text-gray-500 mt-1">Found on the invoice scan</p>
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Map to existing vendor or
                                auto-create</label>
                            <select v-model="invoiceForm.party_id"
                                class="block w-full rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm font-medium">
                                <option value="">+ Auto-create new vendor: {{ scanData.vendor || 'Unknown' }}</option>
                                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extracted Products Card -->
            <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                <div class="px-4 py-4 sm:p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Extracted Products</h3>

                    <div class="space-y-6">
                        <div v-for="item in scanData.temp_products" :key="item.temp_product.id"
                            class="border rounded-lg p-3 sm:p-4 bg-gray-50">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Scanned Data -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Scanned Item</h4>
                                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-medium text-gray-500">Name</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ item.temp_product.name }}</dd>
                                        </div>
                                        <div class="sm:col-span-2" v-if="item.temp_product.description">
                                            <dt class="text-xs font-medium text-gray-500">Details (Extracted)</dt>
                                            <dd class="mt-1 text-xs text-gray-700">{{ item.temp_product.description }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500">Quantity</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ item.temp_product.quantity }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500">Price</dt>
                                            <dd class="mt-1 text-sm text-gray-900">₹{{ item.temp_product.price }}</dd>
                                        </div>
                                        <div v-if="item.temp_product.mrp">
                                            <dt class="text-xs font-medium text-gray-500">MRP</dt>
                                            <dd class="mt-1 text-sm text-gray-900">₹{{ item.temp_product.mrp }}</dd>
                                        </div>
                                        <div v-if="item.temp_product.batch_number">
                                            <dt class="text-xs font-medium text-gray-500">Batch No.</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ item.temp_product.batch_number }}
                                            </dd>
                                        </div>
                                        <div v-if="item.temp_product.expiry_date">
                                            <dt class="text-xs font-medium text-gray-500">Mfg/Exp Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ item.temp_product.expiry_date }}
                                            </dd>
                                        </div>
                                        <div v-if="item.temp_product.discount > 0">
                                            <dt class="text-xs font-medium text-gray-500">Discount</dt>
                                            <dd class="mt-1 text-sm text-green-600">-₹{{ item.temp_product.discount }}
                                            </dd>
                                        </div>
                                        <div v-if="item.temp_product.tax_rate">
                                            <dt class="text-xs font-medium text-gray-500">Tax Rate</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ item.temp_product.tax_rate }}%
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Catalog Actions / Matches -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Add to Catalog</h4>

                                    <div v-if="item.temp_product.status === 'pending'" class="space-y-3">
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
                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Matched
                                        </span>
                                        <span v-else-if="item.temp_product.status === 'added'"
                                            class="text-blue-600 font-medium flex items-center">
                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Added as New
                                        </span>
                                        <span v-else-if="item.temp_product.status === 'rejected'"
                                            class="text-red-600 font-medium flex items-center">
                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
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
        </div>

        <!-- Create Purchase Invoice Modal -->
        <div v-if="showInvoiceModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showInvoiceModal = false"></div>

                <div class="relative bg-white rounded-xl shadow-xl px-6 pt-6 pb-6 sm:max-w-lg sm:w-full z-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Purchase Invoice</h3>

                    <div class="space-y-4">
                        <!-- Invoice Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vendor's Invoice No.</label>
                            <input type="text" v-model="invoiceForm.invoice_number"
                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
                                <input type="date" v-model="invoiceForm.date"
                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                <input type="date" v-model="invoiceForm.due_date"
                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                            </div>
                        </div>

                        <!-- Logistics Details -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-Way Bill No.</label>
                                <input type="text" v-model="invoiceForm.eway_bill_no" placeholder="Optional"
                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle No.</label>
                                <input type="text" v-model="invoiceForm.vehicle_no" placeholder="Optional"
                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
                                <input type="text" v-model="invoiceForm.po_number" placeholder="Optional"
                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Challan No.</label>
                                <input type="text" v-model="invoiceForm.challan_no" placeholder="Optional"
                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                            </div>
                        </div>

                        <!-- Items summary -->
                        <div class="bg-gray-50 rounded-lg p-3 text-sm">
                            <p class="font-medium text-gray-700 mb-2">Items from scan ({{ invoiceItems.length }})</p>
                            <div class="space-y-1 max-h-36 overflow-y-auto">
                                <div v-for="(item, i) in invoiceItems" :key="i"
                                    class="flex justify-between text-gray-600">
                                    <span class="truncate max-w-[60%]">{{ item.name }}</span>
                                    <span>
                                        {{ item.quantity }} × ₹{{ item.unit_price }}
                                        <span v-if="item.discount > 0" class="text-xs text-green-600 ml-1">(-₹{{
                                            item.discount }})</span>
                                    </span>
                                </div>
                            </div>
                            <div
                                class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-semibold text-gray-900">
                                <span>Total</span>
                                <span>₹{{ invoiceTotal.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div v-if="invoiceError" class="text-sm text-red-600 bg-red-50 rounded p-2">{{ invoiceError }}
                        </div>
                    </div>

                    <div class="mt-5 flex gap-3 justify-end">
                        <button @click="showInvoiceModal = false" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="createPurchaseInvoice" :disabled="creatingInvoice" type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 disabled:opacity-60">
                            {{ creatingInvoice ? 'Creating...' : 'Create Invoice' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

const route = useRoute()
const scanId = route.params.id as string

const loading = ref(true)
const scanData = ref<any>(null)
const showInvoiceModal = ref(false)
const invoiceCreated = ref(false)
const creatingInvoice = ref(false)
const invoiceError = ref<string | null>(null)
const vendors = ref<any[]>([])

const today = new Date().toISOString().split('T')[0]
const in30 = new Date(Date.now() + 30 * 864e5).toISOString().split('T')[0]

const invoiceForm = ref({
    party_id: '',
    invoice_number: '',
    date: today,
    due_date: in30,
    po_number: '',
    eway_bill_no: '',
    vehicle_no: '',
    challan_no: '',
})

// Can create invoice only when status=success and has items
const canCreateInvoice = computed(() => {
    return !invoiceCreated.value &&
        scanData.value?.temp_products?.length > 0
})

// Items derived from temp_products
const invoiceItems = computed(() => {
    return (scanData.value?.temp_products ?? []).map((tp: any) => ({
        name: tp.temp_product.name,
        product_id: tp.temp_product.matched_product_id ?? null,
        quantity: Number(tp.temp_product.quantity) || 1,
        unit_price: Number(tp.temp_product.price) || 0,
        mrp: tp.temp_product.mrp ? Number(tp.temp_product.mrp) : null,
        discount: Number(tp.temp_product.discount) || 0,
        tax_rate: Number(tp.temp_product.tax_rate) || 0,
        hsn_code: tp.temp_product.hsn_code ?? null,
        batch_number: tp.temp_product.batch_number ?? null,
        expiry_date: tp.temp_product.expiry_date ?? null,
        description: tp.temp_product.description ?? null,
    }))
})

const invoiceTotal = computed(() =>
    invoiceItems.value.reduce((sum: number, i: any) => {
        const base = (i.quantity * i.unit_price) - i.discount
        return sum + base + base * (i.tax_rate / 100)
    }, 0)
)

onMounted(async () => {
    await fetchScanDetails()
    // Load vendors for modal dropdown
    try {
        const res = await client.get('/parties', { params: { type: 'vendor', per_page: 200 } })
        vendors.value = res.data.data ?? res.data
    } catch (e) { }
})

async function fetchScanDetails() {
    loading.value = true
    try {
        const response = await client.get(`/invoice-scans/${scanId}`)
        if (response.data.success && response.data.data) {
            scanData.value = response.data.data
            // Pre-fill invoice form with scan data
            if (scanData.value.date) {
                invoiceForm.value.date = scanData.value.date.split('T')[0] ?? today
            }
            invoiceForm.value.invoice_number = scanData.value.invoice_no ?? ''
        } else {
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
        await fetchScanDetails()
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

async function createPurchaseInvoice() {
    invoiceError.value = null
    creatingInvoice.value = true
    try {
        const payload: any = {
            invoice_scan_id: scanId,
            party_id: invoiceForm.value.party_id || undefined,
            vendor_name: !invoiceForm.value.party_id ? (scanData.value?.vendor || undefined) : undefined,
            vendor_gstin: !invoiceForm.value.party_id ? (scanData.value?.vendor_gstin || undefined) : undefined,
            vendor_address: !invoiceForm.value.party_id ? (scanData.value?.vendor_address || undefined) : undefined,
            invoice_number: invoiceForm.value.invoice_number || undefined,
            date: invoiceForm.value.date,
            due_date: invoiceForm.value.due_date,
            eway_bill_no: invoiceForm.value.eway_bill_no || undefined,
            vehicle_no: invoiceForm.value.vehicle_no || undefined,
            po_number: invoiceForm.value.po_number || undefined,
            challan_no: invoiceForm.value.challan_no || undefined,
            items: invoiceItems.value,
        }

        await client.post('/purchases/confirm-scan', payload)
        showInvoiceModal.value = false
        invoiceCreated.value = true
        fetchScanDetails() // Refresh to update status
    } catch (e: any) {
        invoiceError.value = e.response?.data?.message || 'Failed to create invoice.'
    } finally {
        creatingInvoice.value = false
    }
}
</script>
