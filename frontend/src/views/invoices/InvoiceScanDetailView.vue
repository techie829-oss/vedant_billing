<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <Button icon="pi pi-arrow-left" severity="secondary" rounded text @click="router.push('/invoice-scans')" />
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 m-0">Scan Analysis Details</h1>
                        <p class="text-gray-500 mt-1 uppercase tracking-wider text-xs font-semibold">
                            Ref: {{ scanData?.invoice_no || 'Unknown' }} | Scanned Vendor: {{ scanData?.vendor || 'Unknown' }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button v-if="canCreateInvoice" label="Create Purchase Bill" icon="pi pi-file-plus" severity="success" @click="showInvoiceModal = true" />
                    <Button v-else-if="scanData?.invoice_id" label="View Purchase Bill" icon="pi pi-eye" severity="info" @click="router.push(`/purchases/${scanData.invoice_id}/edit`)" />
                    <Button label="Back to List" icon="pi pi-list" severity="secondary" outlined @click="router.push('/invoice-scans')" />
                </div>
            </div>

            <!-- Banner Message -->
            <Message v-if="invoiceCreated" severity="success" class="mb-6">
                Purchase invoice created successfully! 
                <Button label="View Now" text size="small" @click="router.push('/purchases')" />
            </Message>

            <!-- Loading State -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                <ProgressSpinner style="width: 50px; height: 50px" />
                <p class="mt-4 text-gray-500">Processing scan results...</p>
            </div>

            <div v-else-if="scanData" class="space-y-8">
                <!-- Vendor Mapping Card -->
                <Card class="border-none shadow-sm">
                    <template #title>Vendor Identification</template>
                    <template #content>
                        <div class="flex flex-col md:flex-row items-center gap-8 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <div class="flex-1">
                                <span class="text-xs font-bold text-gray-400 uppercase">Scanned Entity Name</span>
                                <div class="text-2xl font-black text-gray-900 mt-1">{{ scanData.vendor || 'Unknown' }}</div>
                                <div class="flex items-center gap-2 mt-2" v-if="scanData.vendor_gstin">
                                    <Tag :value="scanData.vendor_gstin" severity="secondary" rounded />
                                    <span class="text-xs text-gray-500">Extracted GSTIN</span>
                                </div>
                            </div>
                            <div class="w-full md:w-96 space-y-2">
                                <label class="font-semibold text-sm">Map to Existing Ledger</label>
                                <Select v-model="invoiceForm.party_id" :options="vendors" optionLabel="name" optionValue="id" filter 
                                    placeholder="+ Auto-create new vendor entry" showClear />
                                <small class="text-gray-400">Linking ensures accurate account statements.</small>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Extracted Products Card -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span>Extracted Products & Pricing</span>
                            <Tag :value="scanData.temp_products?.length + ' Items'" severity="info" />
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div v-for="item in scanData.temp_products" :key="item.temp_product.id" 
                                class="p-4 rounded-2xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-md transition-all">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                    <!-- Scanned Info -->
                                    <div class="md:col-span-5">
                                        <div class="flex flex-col gap-1">
                                            <span class="font-bold text-gray-900">{{ item.temp_product.name }}</span>
                                            <span class="text-xs text-gray-500" v-if="item.temp_product.description">{{ item.temp_product.description }}</span>
                                            <div class="flex flex-wrap gap-2 mt-2">
                                                <Tag :value="item.temp_product.quantity + ' ' + (item.temp_product.unit || 'pcs')" severity="secondary" size="small" />
                                                <Tag :value="'₹' + item.temp_product.price" severity="info" size="small" />
                                                <Tag v-if="item.temp_product.tax_rate" :value="item.temp_product.tax_rate + '% GST'" severity="warn" size="small" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status & Mapping -->
                                    <div class="md:col-span-4 border-l pl-6 border-gray-200">
                                        <div v-if="item.temp_product.status === 'pending'" class="space-y-3">
                                            <div v-if="item.suggested_matches?.length > 0" class="flex flex-col gap-2">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase">AI Suggestions</span>
                                                <div v-for="match in item.suggested_matches" :key="match.product_id" 
                                                    class="flex items-center justify-between p-2 bg-white rounded-lg border text-sm">
                                                    <span class="truncate w-32 font-medium">{{ match.name }}</span>
                                                    <Button label="Match" size="small" text @click="matchProduct(item.temp_product.id, match.product_id)" />
                                                </div>
                                            </div>
                                            <div v-else class="text-center py-2">
                                                <span class="text-xs text-gray-400 italic">No exact catalog matches found.</span>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center gap-2">
                                            <i :class="getStatusIcon(item.temp_product.status)" class="text-xl"></i>
                                            <span class="font-bold uppercase tracking-widest text-xs" :class="getStatusClass(item.temp_product.status)">
                                                {{ item.temp_product.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="md:col-span-3 flex justify-end gap-2">
                                        <template v-if="item.temp_product.status === 'pending'">
                                            <Button label="Add New" icon="pi pi-plus" severity="success" outlined size="small" @click="openNewProductModal(item)" />
                                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="rejectProduct(item.temp_product.id)" />
                                        </template>
                                        <Button v-else label="Undo" icon="pi pi-undo" severity="secondary" text size="small" @click="undoMatch(item.temp_product.id)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Create Invoice Dialog -->
        <Dialog v-model:visible="showInvoiceModal" header="Confirm Purchase Bill Details" :modal="true" :style="{ width: '500px' }">
            <div class="flex flex-col gap-4 pt-2">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Vendor Bill Number</label>
                    <InputText v-model="invoiceForm.invoice_number" placeholder="Reference from scan" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Bill Date</label>
                        <DatePicker v-model="invoiceForm.date" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Due Date</label>
                        <DatePicker v-model="invoiceForm.due_date" dateFormat="yy-mm-dd" showIcon />
                    </div>
                </div>
                
                <Divider align="left"><span class="text-xs font-bold text-gray-400">Logistics (Optional)</span></Divider>
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs uppercase text-gray-500">E-Way Bill</label>
                        <InputText v-model="invoiceForm.eway_bill_no" size="small" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs uppercase text-gray-500">Vehicle No.</label>
                        <InputText v-model="invoiceForm.vehicle_no" size="small" />
                    </div>
                </div>

                <div class="bg-primary-50 p-4 rounded-xl border border-primary-100 flex justify-between items-center mt-2">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-primary uppercase">Estimated Total</span>
                        <span class="font-black text-primary-900">{{ invoiceItems.length }} extracted items</span>
                    </div>
                    <div class="text-2xl font-black text-primary">₹{{ invoiceTotal.toFixed(2) }}</div>
                </div>

                <Message v-if="invoiceError" severity="error" size="small">{{ invoiceError }}</Message>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="showInvoiceModal = false" />
                <Button label="Generate Purchase Bill" icon="pi pi-check" severity="success" :loading="creatingInvoice" @click="createPurchaseInvoice" />
            </template>
        </Dialog>

        <!-- New Product Edit Dialog -->
        <Dialog v-model:visible="showNewProductModal" header="Edit Item Before Catalog Entry" :modal="true" :style="{ width: '450px' }">
            <div class="flex flex-col gap-4 pt-2">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Product Name</label>
                    <InputText v-model="newProductForm.name" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Base Unit</label>
                        <InputText v-model="newProductForm.unit" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Initial Stock</label>
                        <InputNumber v-model="newProductForm.quantity" :minFractionDigits="2" />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Price</label>
                        <InputNumber v-model="newProductForm.price" mode="currency" currency="INR" locale="en-IN" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">GST %</label>
                        <InputNumber v-model="newProductForm.tax_rate" suffix="%" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">CESS %</label>
                        <InputNumber v-model="newProductForm.cess_rate" suffix="%" />
                    </div>
                </div>
                <Message v-if="newProductError" severity="error" size="small">{{ newProductError }}</Message>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="showNewProductModal = false" />
                <Button label="Confirm & Add" icon="pi pi-check" :loading="creatingNewProduct" @click="submitNewProduct" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Select from 'primevue/select'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'
import Divider from 'primevue/divider'
import ProgressSpinner from 'primevue/progressspinner'

const router = useRouter()
const route = useRoute()
const scanId = route.params.id as string

const loading = ref(true)
const scanData = ref<any>(null)
const showInvoiceModal = ref(false)
const invoiceCreated = ref(false)
const creatingInvoice = ref(false)
const invoiceError = ref<string | null>(null)
const vendors = ref<any[]>([])

const showNewProductModal = ref(false)
const creatingNewProduct = ref(false)
const newProductError = ref<string | null>(null)
const newProductForm = reactive({
    temp_id: '', name: '', quantity: 1, price: 0, unit: 'pcs', secondary_unit: '', conversion_factor: 1, tax_rate: 0, cess_rate: 0
})

const invoiceForm = reactive({
    party_id: '', invoice_number: '', date: new Date(), due_date: new Date(Date.now() + 30 * 864e5),
    po_number: '', eway_bill_no: '', vehicle_no: '', challan_no: '',
})

function openNewProductModal(item: any) {
    Object.assign(newProductForm, {
        temp_id: item.temp_product.id,
        name: item.temp_product.name || '',
        quantity: Number(item.temp_product.quantity) || 1,
        price: Number(item.temp_product.price) || 0,
        unit: item.temp_product.unit || 'pcs',
        secondary_unit: '',
        conversion_factor: 1,
        tax_rate: Number(item.temp_product.tax_rate) || 0,
        cess_rate: Number(item.temp_product.cess_rate) || 0
    })
    showNewProductModal.value = true
}

const canCreateInvoice = computed(() => !invoiceCreated.value && !scanData.value?.invoice_id && scanData.value?.temp_products?.length > 0)

const invoiceItems = computed(() => {
    return (scanData.value?.temp_products ?? []).map((tp: any) => ({
        name: tp.temp_product.name,
        product_id: tp.temp_product.matched_product_id ?? null,
        quantity: Number(tp.temp_product.quantity) || 1,
        unit: tp.temp_product.unit || 'pcs',
        conversion_factor: 1.00,
        unit_price: Number(tp.temp_product.price) || 0,
        mrp: tp.temp_product.mrp ? Number(tp.temp_product.mrp) : null,
        discount: Number(tp.temp_product.discount) || 0,
        tax_rate: Number(tp.temp_product.tax_rate) || 0,
        cess_rate: Number(tp.temp_product.cess_rate) || 0,
        hsn_code: tp.temp_product.hsn_code ?? null,
        batch_number: tp.temp_product.batch_number ?? null,
        expiry_date: tp.temp_product.expiry_date ?? null,
        description: tp.temp_product.description ?? null,
    }))
})

const invoiceTotal = computed(() => invoiceItems.value.reduce((sum: number, i: any) => {
    const base = (i.quantity * i.unit_price) - i.discount
    const tax = base * (i.tax_rate / 100)
    const cess = base * (i.cess_rate / 100)
    return sum + base + tax + cess
}, 0))

const fetchScanDetails = async () => {
    loading.value = true
    try {
        const res = await client.get(`/invoice-scans/${scanId}`)
        scanData.value = res.data.data
        if (scanData.value.date) invoiceForm.date = new Date(scanData.value.date)
        invoiceForm.invoice_number = scanData.value.invoice_no || ''
    } finally { loading.value = false }
}

async function matchProduct(tempId: string, productId: string) {
    await client.post(`/temp-products/${tempId}/match`, { product_id: productId })
    await fetchScanDetails()
}

async function undoMatch(tempId: string) {
    await client.post(`/temp-products/${tempId}/undo-match`)
    await fetchScanDetails()
}

async function submitNewProduct() {
    newProductError.value = null
    creatingNewProduct.value = true
    try {
        await client.post(`/temp-products/${newProductForm.temp_id}/add-new`, { ...newProductForm, update_inventory: true })
        showNewProductModal.value = false
        await fetchScanDetails()
    } catch (e: any) { newProductError.value = e.response?.data?.message || 'Add failed' } 
    finally { creatingNewProduct.value = false }
}

async function rejectProduct(tempId: string) {
    if (!confirm('Reject this item?')) return
    await client.delete(`/temp-products/${tempId}`)
    await fetchScanDetails()
}

async function createPurchaseInvoice() {
    invoiceError.value = null
    creatingInvoice.value = true
    try {
        const payload = {
            ...invoiceForm,
            invoice_scan_id: scanId,
            date: invoiceForm.date.toISOString().split('T')[0],
            due_date: invoiceForm.due_date.toISOString().split('T')[0],
            items: invoiceItems.value,
        }
        await client.post('/purchases/confirm-scan', payload)
        showInvoiceModal.value = false
        invoiceCreated.value = true
        fetchScanDetails()
    } catch (e: any) { invoiceError.value = e.response?.data?.message || 'Failed to create invoice.' } 
    finally { creatingInvoice.value = false }
}

const getStatusIcon = (s: string) => s === 'matched' ? 'pi pi-check-circle' : (s === 'added' ? 'pi pi-plus-circle' : 'pi pi-times-circle')
const getStatusClass = (s: string) => s === 'matched' ? 'text-green-600' : (s === 'added' ? 'text-blue-600' : 'text-red-600')

onMounted(async () => {
    fetchScanDetails()
    client.get('/parties', { params: { type: 'vendor', per_page: 200 } }).then(res => vendors.value = res.data.data ?? res.data)
})
</script>
