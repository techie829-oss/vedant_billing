<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <Button icon="pi pi-arrow-left" severity="secondary" rounded text @click="router.back()" />
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 m-0">
                            {{ isEditMode ? `${getDocumentTypeLabel()} #${form.invoice_number}` : `New ${getDocumentTypeLabel()}` }}
                        </h1>
                        <div class="flex items-center gap-2 mt-1">
                            <Tag :value="form.status.toUpperCase()" :severity="getStatusSeverity(form.status)" />
                            <span class="text-gray-500 text-sm">Create and manage your business documents.</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button label="Save Draft" icon="pi pi-file" severity="secondary" outlined :loading="saving" @click="save('draft')" />
                    <Button label="Save & Finalize" icon="pi pi-check-circle" :loading="saving" @click="save('sent')" />
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column: Document Details & Items -->
                <div class="col-span-12 lg:col-span-9 space-y-6">
                    
                    <!-- Display Options Card -->
                    <Card class="border-none shadow-sm">
                        <template #title>
                            <div class="flex items-center justify-between cursor-pointer" @click="showDisplayOptions = !showDisplayOptions">
                                <span class="text-lg font-bold">Display & Type Settings</span>
                                <i :class="['pi', showDisplayOptions ? 'pi-chevron-up' : 'pi-chevron-down']"></i>
                            </div>
                        </template>
                        <template #content>
                            <div v-show="showDisplayOptions" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-sm">Document Type</label>
                                        <Select v-model="form.type" :options="docTypeOptions" optionLabel="label" optionValue="value" optionGroupLabel="label" optionGroupChildren="options" />
                                    </div>
                                    <div v-if="form.type === 'bill_of_supply'" class="flex flex-col gap-2">
                                        <label class="font-semibold text-sm">Copy Type</label>
                                        <Select v-model="form.meta.copy_type" :options="copyTypeOptions" optionLabel="label" optionValue="value" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="flex items-center gap-2">
                                        <Checkbox v-model="form.meta.display_options.show_hsn" binary id="opt_hsn" />
                                        <label for="opt_hsn" class="text-sm">HSN/SAC</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Checkbox v-model="form.meta.display_options.show_gst_breakdown" binary id="opt_gst" />
                                        <label for="opt_gst" class="text-sm">GST Breakdown</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Checkbox v-model="form.meta.display_options.show_discount" binary id="opt_disc" />
                                        <label for="opt_disc" class="text-sm">Discount</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Checkbox v-model="form.meta.display_options.show_transport_details" binary id="opt_trans" />
                                        <label for="opt_trans" class="text-sm">Transport</label>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Main Document Card -->
                    <Card class="border-none shadow-sm">
                        <template #content>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Customer</label>
                                    <div class="flex gap-2">
                                        <Select v-model="form.party_id" :options="customers" optionLabel="name" optionValue="id" filter placeholder="Select Customer" class="flex-1" />
                                        <Button icon="pi pi-user-plus" severity="secondary" outlined @click="showCustomerModal = true" />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Invoice Number</label>
                                    <InputText v-model="form.invoice_number" placeholder="(Auto-generated)" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Date</label>
                                    <DatePicker v-model="form.date" dateFormat="yy-mm-dd" showIcon />
                                </div>
                            </div>

                            <div v-if="form.meta.display_options.show_transport_details" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Challan No.</label>
                                    <InputText v-model="form.challan_no" size="small" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Vehicle No.</label>
                                    <InputText v-model="form.vehicle_no" size="small" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-semibold text-gray-500 uppercase">E-Way Bill</label>
                                    <InputText v-model="form.eway_bill_no" size="small" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-semibold text-gray-500 uppercase">PO Number</label>
                                    <InputText v-model="form.po_number" size="small" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Items Card -->
                    <Card class="border-none shadow-sm overflow-hidden items-card">
                        <template #title>Document Items</template>
                        <template #content>
                            <DataTable :value="form.items" class="p-datatable-sm" responsiveLayout="stack" breakpoint="960px">
                                <Column header="Product" style="min-width: 250px">
                                    <template #body="{ data }">
                                        <div class="flex flex-col gap-1">
                                            <Select v-model="data.product_id" :options="products" optionLabel="name" optionValue="id" filter 
                                                placeholder="Select Product" @change="(e) => onProductSelect(data, e.value)" class="w-full" />
                                            <InputText v-if="form.meta.display_options.show_description" v-model="data.description" placeholder="Notes/Description" size="small" class="text-xs" />
                                        </div>
                                    </template>
                                </Column>

                                <Column v-if="form.meta.display_options.show_hsn" header="HSN" style="width: 100px">
                                    <template #body="{ data }">
                                        <InputText v-model="data.hsn_code" size="small" />
                                    </template>
                                </Column>

                                <Column header="Qty / Unit" style="width: 180px">
                                    <template #body="{ data }">
                                        <div class="flex gap-1">
                                            <InputNumber v-model="data.quantity" :minFractionDigits="2" size="small" class="w-20" />
                                            <Select v-model="data.unit" :options="getItemUnitOptions(data)" optionLabel="label" optionValue="value" size="small" class="flex-1" @change="() => onUnitChange(data)" />
                                        </div>
                                    </template>
                                </Column>

                                <Column header="Price" style="width: 140px">
                                    <template #body="{ data }">
                                        <InputNumber v-model="data.unit_price" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" size="small" />
                                    </template>
                                </Column>

                                <Column v-if="form.meta.display_options.show_discount" header="Discount" style="width: 120px">
                                    <template #body="{ data }">
                                        <div class="flex flex-col gap-1">
                                            <div class="p-inputgroup p-fluid">
                                                <Button :label="data.discount_type === 'percentage' ? '%' : '₹'" severity="secondary" size="small" @click="toggleDiscountType(data)" />
                                                <InputNumber v-model="data.discount" :minFractionDigits="2" size="small" />
                                            </div>
                                        </div>
                                    </template>
                                </Column>

                                <Column v-if="form.meta.display_options.show_gst_breakdown" header="Tax" style="width: 100px">
                                    <template #body="{ data }">
                                        <div class="flex flex-col gap-1">
                                            <InputNumber v-model="data.tax_rate" suffix="%" size="small" />
                                            <InputNumber v-if="data.cess_rate > 0" v-model="data.cess_rate" suffix="%" size="small" placeholder="Cess" />
                                        </div>
                                    </template>
                                </Column>

                                <Column header="Total" style="width: 120px" class="text-right">
                                    <template #body="{ data }">
                                        <span class="font-bold">₹{{ Number(calculateLineTotal(data)).toFixed(2) }}</span>
                                    </template>
                                </Column>

                                <Column style="width: 50px">
                                    <template #body="{ index }">
                                        <Button icon="pi pi-trash" severity="danger" text rounded @click="removeItem(index)" />
                                    </template>
                                </Column>

                                <template #footer>
                                    <div class="flex justify-start">
                                        <Button label="Add New Item" icon="pi pi-plus" text @click="addItem" />
                                    </div>
                                </template>
                            </DataTable>
                        </template>
                    </Card>

                    <!-- Addresses -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card class="border-none shadow-sm">
                            <template #title>Billing Address</template>
                            <template #content>
                                <div class="flex flex-col gap-3">
                                    <InputText v-model="form.meta.billing_address.street" placeholder="Street" />
                                    <div class="grid grid-cols-2 gap-3">
                                        <InputText v-model="form.meta.billing_address.city" placeholder="City" />
                                        <StateSelect v-model="form.meta.billing_address.state" />
                                    </div>
                                    <InputText v-model="form.meta.billing_address.zip" placeholder="ZIP/PIN Code" />
                                </div>
                            </template>
                        </Card>
                        <Card v-if="form.meta.display_options.show_shipping_address" class="border-none shadow-sm">
                            <template #title>
                                <div class="flex items-center justify-between">
                                    <span>Shipping Address</span>
                                    <Button label="Same as Billing" text size="small" @click="copyBillingToShipping" />
                                </div>
                            </template>
                            <template #content>
                                <div class="flex flex-col gap-3">
                                    <InputText v-model="form.meta.shipping_address.street" placeholder="Street" />
                                    <div class="grid grid-cols-2 gap-3">
                                        <InputText v-model="form.meta.shipping_address.city" placeholder="City" />
                                        <StateSelect v-model="form.meta.shipping_address.state" />
                                    </div>
                                    <InputText v-model="form.meta.shipping_address.zip" placeholder="ZIP/PIN Code" />
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>

                <!-- Right Column: Summary & Notes -->
                <div class="col-span-12 lg:col-span-3 space-y-6">
                    <!-- Totals Summary -->
                    <Card class="border-none shadow-sm bg-primary-50">
                        <template #title>Summary</template>
                        <template #content>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span>Subtotal</span>
                                    <span class="font-semibold">₹{{ Number(totals.subtotal).toFixed(2) }}</span>
                                </div>
                                <div v-if="totals.igst > 0" class="flex justify-between text-sm">
                                    <span>IGST</span>
                                    <span class="font-semibold">₹{{ Number(totals.igst).toFixed(2) }}</span>
                                </div>
                                <template v-else>
                                    <div class="flex justify-between text-sm">
                                        <span>CGST</span>
                                        <span class="font-semibold">₹{{ Number(totals.cgst).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>SGST</span>
                                        <span class="font-semibold">₹{{ Number(totals.sgst).toFixed(2) }}</span>
                                    </div>
                                </template>
                                <div v-if="totals.cess > 0" class="flex justify-between text-sm">
                                    <span>CESS</span>
                                    <span class="font-semibold">₹{{ Number(totals.cess).toFixed(2) }}</span>
                                </div>
                                <div class="border-t border-primary-200 pt-3 flex justify-between items-center">
                                    <span class="text-lg font-bold">Grand Total</span>
                                    <span class="text-xl font-black text-primary">₹{{ Number(totals.grandTotal).toFixed(2) }}</span>
                                </div>
                                <div class="text-[10px] text-gray-500 text-right uppercase tracking-tighter">
                                    {{ totals.taxType }} | {{ totals.posState }}
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Notes & Terms -->
                    <Card class="border-none shadow-sm">
                        <template #content>
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Notes</label>
                                    <Textarea v-model="form.notes" rows="3" autoResize placeholder="Internal or client notes..." />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Terms & Conditions</label>
                                    <Textarea v-model="form.terms" rows="3" autoResize placeholder="Payment terms, returns, etc." />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Quick Actions -->
                    <div class="flex flex-col gap-2">
                        <Button label="Save & Finalize" icon="pi pi-check-circle" size="large" :loading="saving" @click="save('sent')" />
                        <Button label="Save Draft" icon="pi pi-file" severity="secondary" text @click="save('draft')" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Customer Dialog -->
        <Dialog v-model:visible="showCustomerModal" header="Add New Customer" :style="{ width: '450px' }" :modal="true">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold">Name *</label>
                    <InputText v-model="newCustomerForm.name" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold">GSTIN</label>
                    <div class="p-inputgroup">
                        <InputText v-model="newCustomerForm.gstin" />
                        <Button icon="pi pi-search" severity="secondary" @click="fetchGstForNewCustomer" :loading="fetchingGst" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold">Phone</label>
                        <InputText v-model="newCustomerForm.phone" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold">City</label>
                        <InputText v-model="newCustomerForm.city" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text @click="showCustomerModal = false" />
                <Button label="Save Customer" icon="pi pi-check" :loading="savingCustomer" @click="saveNewCustomer" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import { useInvoiceStore } from '../../stores/invoice'
import { usePartyStore } from '../../stores/party'
import { useProductStore, type Product } from '../../stores/product'
import { useAuthStore } from '../../stores/auth'
import client from '../../api/client'

// PrimeVue Components
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'

// Existing Custom Components (Assuming they are fine or will be updated)
import StateSelect from '../../components/StateSelect.vue'

const router = useRouter()
const route = useRoute()
const invoiceStore = useInvoiceStore()
const partyStore = usePartyStore()
const productStore = useProductStore()
const authStore = useAuthStore()

const isEditMode = computed(() => route.params.id !== undefined)

const customers = ref<any[]>([])
const products = ref<Product[]>([])
const saving = ref(false)
const showDisplayOptions = ref(false)

const form = ref({
    invoice_number: '',
    type: 'tax_invoice',
    party_id: '',
    date: new Date(),
    due_date: new Date(),
    items: [] as any[],
    notes: '',
    terms: '',
    challan_no: '',
    vehicle_no: '',
    eway_bill_no: '',
    po_number: '',
    status: 'draft' as 'draft' | 'sent' | 'paid',
    meta: {
        display_options: {
            show_transport_details: false,
            show_hsn: true,
            show_gst_breakdown: true,
            show_discount: false,
            show_shipping_address: false,
            show_description: true
        },
        billing_address: { street: '', city: '', state: '', zip: '' },
        shipping_address: { street: '', city: '', state: '', zip: '' },
        copy_type: 'original'
    }
})

// Options
const docTypeOptions = [
    {
        label: 'Sales Documents',
        options: [
            { label: 'Tax Invoice (GST)', value: 'tax_invoice' },
            { label: 'Bill of Supply', value: 'bill_of_supply' },
            { label: 'Proforma (Estimate)', value: 'proforma_invoice' }
        ]
    },
    {
        label: 'Logistics',
        options: [
            { label: 'Delivery Challan', value: 'delivery_challan' }
        ]
    }
]

const copyTypeOptions = [
    { label: 'Original for Recipient', value: 'original' },
    { label: 'Duplicate for Transporter', value: 'duplicate' },
    { label: 'Triplicate for Supplier', value: 'triplicate' }
]

// Methods
const addItem = () => {
    form.value.items.push({
        product_id: null,
        name: '',
        description: '',
        hsn_code: '',
        quantity: 1,
        unit: 'pcs',
        conversion_factor: 1.00,
        unit_price: 0,
        discount: 0,
        discount_type: 'amount',
        tax_rate: 0,
        cess_rate: 0
    })
}

const removeItem = (index: number) => {
    form.value.items.splice(index, 1)
}

const toggleDiscountType = (item: any) => {
    item.discount_type = item.discount_type === 'percentage' ? 'amount' : 'percentage'
}

const calculateLineTotal = (item: any) => {
    const qty = Number(item.quantity) || 0
    const price = Number(item.unit_price) || 0
    const discount = item.discount_type === 'percentage' ? (qty * price * (item.discount / 100)) : (item.discount || 0)
    const taxable = (qty * price) - discount
    const tax = taxable * (item.tax_rate / 100)
    const cess = taxable * (item.cess_rate / 100)
    return Math.max(0, taxable + tax + cess)
}

const totals = computed(() => {
    let subtotal = 0, cgst = 0, sgst = 0, igst = 0, cess = 0
    const businessState = authStore.activeBusiness?.meta?.state?.toLowerCase()
    const party = customers.value.find(c => c.id === form.value.party_id)
    const partyState = (party?.shipping_address?.state || party?.billing_address?.state || '').toLowerCase()
    const isInterState = businessState && partyState && businessState !== partyState

    form.value.items.forEach(item => {
        const qty = Number(item.quantity) || 0
        const price = Number(item.unit_price) || 0
        const discount = item.discount_type === 'percentage' ? (qty * price * (item.discount / 100)) : (item.discount || 0)
        const taxable = Math.max(0, (qty * price) - discount)
        const lineTax = taxable * (item.tax_rate / 100)
        const lineCess = taxable * (item.cess_rate / 100)

        subtotal += taxable
        cess += lineCess
        if (isInterState) igst += lineTax
        else { cgst += lineTax / 2; sgst += lineTax / 2; }
    })

    return { subtotal, cgst, sgst, igst, cess, taxType: isInterState ? 'IGST' : 'CGST/SGST', posState: partyState || 'Local', grandTotal: subtotal + cgst + sgst + igst + cess }
})

const onProductSelect = (item: any, productId: string) => {
    const product = products.value.find(p => p.id === productId)
    if (product) {
        item.name = product.name
        item.hsn_code = product.hsn_code || ''
        item.unit = product.unit || 'pcs'
        item.tax_rate = Number(product.tax_rate) || 0
        item.cess_rate = Number(product.cess_rate) || 0
        
        const rawPrice = Number(product.sale_price) || 0
        if (product.is_tax_inclusive) {
            item.unit_price = Number((rawPrice / (1 + ((item.tax_rate + item.cess_rate) / 100))).toFixed(4))
        } else {
            item.unit_price = rawPrice
        }
    }
}

const getItemUnitOptions = (item: any) => {
    const p = products.value.find(prod => prod.id === item.product_id)
    const opts = [{ label: p?.unit || 'Base', value: p?.unit || 'pcs' }]
    if (p?.secondary_unit) opts.push({ label: p.secondary_unit, value: p.secondary_unit })
    return opts
}

const onUnitChange = (item: any) => {
    const product = products.value.find(p => p.id === item.product_id)
    if (!product) return
    
    if (item.unit === product.secondary_unit) {
        item.conversion_factor = 1 / (Number(product.conversion_factor) || 1)
        item.unit_price = (Number(product.sale_price) || 0) / (Number(product.conversion_factor) || 1)
    } else {
        item.conversion_factor = 1.00
        item.unit_price = Number(product.sale_price) || 0
    }
}

const copyBillingToShipping = () => {
    form.value.meta.shipping_address = { ...form.value.meta.billing_address }
}

const getStatusSeverity = (status: string) => {
    switch (status) {
        case 'paid': return 'success'
        case 'sent': return 'info'
        case 'draft': return 'secondary'
        default: return 'warn'
    }
}

const getDocumentTypeLabel = () => {
    return docTypeOptions.flatMap(g => g.options).find(o => o.value === form.value.type)?.label || 'Invoice'
}

// Save Logic
const save = async (status: 'draft' | 'sent') => {
    if (!form.value.party_id) return alert('Please select a customer')
    saving.value = true
    try {
        const payload = { 
            ...form.value, 
            status, 
            date: form.value.date.toISOString().split('T')[0],
            due_date: form.value.due_date.toISOString().split('T')[0],
            subtotal: totals.value.subtotal,
            tax_total: totals.value.cgst + totals.value.sgst + totals.value.igst,
            grand_total: totals.value.grandTotal,
            business_id: authStore.currentBusinessId
        }
        if (isEditMode.value) await invoiceStore.updateInvoice(route.params.id as string, payload as any)
        else await invoiceStore.createInvoice(payload as any)
        router.push('/invoices')
    } catch (e) { alert('Failed to save') } finally { saving.value = false }
}

// Quick Add Customer Logic
const showCustomerModal = ref(false)
const savingCustomer = ref(false)
const fetchingGst = ref(false)
const newCustomerForm = ref({ name: '', gstin: '', phone: '', city: '' })

const fetchGstForNewCustomer = async () => {
    fetchingGst.value = true
    try {
        const res = await client.get(`/gst-lookup/${newCustomerForm.value.gstin}`)
        if (res.data.legal_name) {
            newCustomerForm.value.name = res.data.trade_name || res.data.legal_name
            newCustomerForm.value.city = res.data.address?.city || ''
        }
    } finally { fetchingGst.value = false }
}

const saveNewCustomer = async () => {
    savingCustomer.value = true
    try {
        const res = await client.post('/parties', { ...newCustomerForm.value, party_type: 'customer' })
        customers.value.push(res.data.data)
        form.value.party_id = res.data.data.id
        showCustomerModal.value = false
    } finally { savingCustomer.value = false }
}

onMounted(async () => {
    await Promise.all([partyStore.fetchParties({ type: 'customer' }), productStore.fetchProducts()])
    customers.value = partyStore.parties
    products.value = productStore.products
    
    if (isEditMode.value) {
        const inv = await invoiceStore.fetchInvoice(route.params.id as string)
        if (inv) {
            form.value = { ...inv, date: new Date(inv.date), due_date: new Date(inv.due_date) }
        }
    } else { addItem() }
})
</script>

<style scoped>
.items-card :deep(.p-card-body) {
    padding: 0;
}
</style>
