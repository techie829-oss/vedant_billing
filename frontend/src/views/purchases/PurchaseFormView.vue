<template>
  <AppLayout>
    <div class="p-fluid">
      <!-- Header Section -->
      <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex items-center gap-4">
          <Button icon="pi pi-arrow-left" severity="secondary" rounded text @click="router.back()" />
          <div>
            <h1 class="text-3xl font-bold text-gray-900 m-0">
              {{ isEditMode ? 'Edit Purchase Bill' : 'New Purchase Bill' }}
            </h1>
            <p class="text-gray-500 mt-1">Record and track stock-in bills from your suppliers.</p>
          </div>
        </div>
        <div class="flex gap-2">
          <Button label="Cancel" severity="secondary" outlined @click="router.back()" />
          <Button :label="isEditMode ? 'Update Bill' : 'Save Bill'" icon="pi pi-check" :loading="saving" @click="save" />
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6">
        <!-- Left Column: Bill Details & Items -->
        <div class="col-span-12 lg:col-span-9 space-y-6">
          
          <!-- Primary Info Card -->
          <Card class="border-none shadow-sm">
            <template #content>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Vendor / Supplier</label>
                  <div class="flex gap-2">
                    <Select v-model="form.party_id" :options="vendors" optionLabel="name" optionValue="id" filter placeholder="Select Vendor" class="flex-1" />
                    <Button icon="pi pi-user-plus" severity="secondary" outlined @click="showVendorModal = true" />
                  </div>
                </div>
                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Vendor's Bill No.</label>
                  <InputText v-model="form.invoice_number" placeholder="Enter bill reference" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Bill Date</label>
                    <DatePicker v-model="form.date" dateFormat="yy-mm-dd" showIcon />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Due Date</label>
                    <DatePicker v-model="form.due_date" dateFormat="yy-mm-dd" showIcon />
                  </div>
                </div>
              </div>

              <!-- Logistics Bar -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
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
            <template #title>Bill Items (Inventory In)</template>
            <template #content>
              <DataTable :value="form.items" class="p-datatable-sm" responsiveLayout="stack" breakpoint="960px">
                <Column header="Product" style="min-width: 250px">
                  <template #body="{ data }">
                    <div class="flex flex-col gap-1">
                      <Select v-model="data.product_id" :options="products" optionLabel="name" optionValue="id" filter 
                        placeholder="Select Product" @change="(e) => onProductSelect(data, e.value)" class="w-full" />
                      <div class="flex gap-2 mt-1">
                        <InputText v-model="data.batch_number" placeholder="Batch No" size="small" class="text-[10px] flex-1" />
                        <DatePicker v-model="data.expiry_date" placeholder="Expiry" size="small" class="text-[10px] flex-1" dateFormat="yy-mm-dd" />
                      </div>
                    </div>
                  </template>
                </Column>

                <Column header="MRP" style="width: 100px">
                  <template #body="{ data }">
                    <InputNumber v-model="data.mrp" :minFractionDigits="2" size="small" />
                  </template>
                </Column>

                <Column header="Qty" style="width: 100px">
                  <template #body="{ data }">
                    <InputNumber v-model="data.quantity" :minFractionDigits="2" size="small" @input="calcItem(data)" />
                  </template>
                </Column>

                <Column header="Buy Rate" style="width: 120px">
                  <template #body="{ data }">
                    <InputNumber v-model="data.unit_price" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" size="small" @input="calcItem(data)" />
                  </template>
                </Column>

                <Column header="Disc" style="width: 110px">
                  <template #body="{ data }">
                    <div class="p-inputgroup p-fluid">
                      <Button :label="data.discount_type === 'percentage' ? '%' : '₹'" severity="secondary" size="small" 
                        @click="data.discount_type = data.discount_type === 'percentage' ? 'amount' : 'percentage'; calcItem(data)" />
                      <InputNumber v-model="data.discount" :minFractionDigits="2" size="small" @input="calcItem(data)" />
                    </div>
                  </template>
                </Column>

                <Column header="Tax" style="width: 90px">
                  <template #body="{ data }">
                    <InputNumber v-model="data.tax_rate" suffix="%" size="small" @input="calcItem(data)" />
                  </template>
                </Column>

                <Column header="Total" style="width: 120px" class="text-right">
                  <template #body="{ data }">
                    <span class="font-bold">₹{{ Number(data.total).toFixed(2) }}</span>
                  </template>
                </Column>

                <Column style="width: 50px">
                  <template #body="{ index }">
                    <Button icon="pi pi-trash" severity="danger" text rounded @click="removeItem(index)" v-if="form.items.length > 1" />
                  </template>
                </Column>

                <template #footer>
                  <div class="flex justify-start">
                    <Button label="Add Item" icon="pi pi-plus" text @click="addItem" />
                  </div>
                </template>
              </DataTable>
            </template>
          </Card>
        </div>

        <!-- Right Column: Summary & Notes -->
        <div class="col-span-12 lg:col-span-3 space-y-6">
          <!-- Totals Summary -->
          <Card class="border-none shadow-sm bg-orange-50">
            <template #title>Bill Summary</template>
            <template #content>
              <div class="space-y-3">
                <div class="flex justify-between text-sm">
                  <span>Subtotal</span>
                  <span class="font-semibold">₹{{ subtotal.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span>Tax (GST)</span>
                  <span class="font-semibold">₹{{ taxTotal.toFixed(2) }}</span>
                </div>
                <div class="border-t border-orange-200 pt-3 flex justify-between items-center">
                  <span class="text-lg font-bold">Grand Total</span>
                  <span class="text-xl font-black text-orange-700">₹{{ grandTotal.toFixed(2) }}</span>
                </div>
              </div>
            </template>
          </Card>

          <!-- Internal Notes -->
          <Card class="border-none shadow-sm">
            <template #title>Notes</template>
            <template #content>
              <Textarea v-model="form.notes" rows="5" autoResize placeholder="Internal notes about this purchase..." />
            </template>
          </Card>

          <Message v-if="error" severity="error" closable @close="error = null">{{ error }}</Message>
        </div>
      </div>
    </div>

    <!-- Quick Add Vendor Dialog -->
    <Dialog v-model:visible="showVendorModal" header="Add New Vendor" :modal="true" :style="{ width: '450px' }">
      <div class="flex flex-col gap-4 pt-2">
        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">Vendor Name *</label>
          <InputText v-model="newVendorForm.name" />
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">GSTIN</label>
          <div class="p-inputgroup">
            <InputText v-model="newVendorForm.gstin" />
            <Button icon="pi pi-search" severity="secondary" @click="fetchGstForNewVendor" :loading="fetchingGst" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-2">
            <label class="font-semibold text-sm">Phone</label>
            <InputText v-model="newVendorForm.phone" />
          </div>
          <div class="flex flex-col gap-2">
            <label class="font-semibold text-sm">Email</label>
            <InputText v-model="newVendorForm.email" />
          </div>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">City</label>
          <InputText v-model="newVendorForm.city" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" text @click="showVendorModal = false" />
        <Button label="Save Vendor" icon="pi pi-check" :loading="savingVendor" @click="saveNewVendor" />
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'
import { useProductStore } from '../../stores/product'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'

const router = useRouter()
const route = useRoute()
const productStore = useProductStore()

const saving = ref(false)
const error = ref<string | null>(null)
const vendors = ref<any[]>([])
const products = computed(() => productStore.products)
const isEditMode = computed(() => !!route.params.id)

// Bill Form
const form = ref({
  type: 'purchase_invoice',
  party_id: '',
  invoice_number: '',
  date: new Date(),
  due_date: new Date(Date.now() + 30 * 864e5),
  notes: '',
  eway_bill_no: '',
  vehicle_no: '',
  po_number: '',
  challan_no: '',
  items: [] as any[]
})

const makeItem = () => ({
  product_id: null,
  name: '',
  quantity: 1,
  unit_price: 0,
  mrp: null,
  batch_number: '',
  expiry_date: null,
  tax_rate: 0,
  discount: 0,
  discount_type: 'amount',
  total: 0
})

const addItem = () => form.value.items.push(makeItem())
const removeItem = (idx: number) => form.value.items.splice(idx, 1)

const calcItem = (item: any) => {
  const gross = (item.quantity || 0) * (item.unit_price || 0)
  const discountAmt = item.discount_type === 'percentage' ? gross * ((item.discount || 0) / 100) : (item.discount || 0)
  const base = Math.max(0, gross - discountAmt)
  item.total = base + (base * ((item.tax_rate || 0) / 100))
}

const onProductSelect = (item: any, productId: string) => {
  const product = products.value.find(p => p.id === productId)
  if (!product) return
  item.name = product.name
  item.tax_rate = Number(product.tax_rate) || 0
  
  if (product.purchase_price) {
    let basePrice = Number(product.purchase_price)
    if (product.is_tax_inclusive && product.tax_rate) {
      basePrice = basePrice / (1 + (product.tax_rate / 100))
    }
    item.unit_price = basePrice
  }
  calcItem(item)
}

const subtotal = computed(() => form.value.items.reduce((s, i) => {
  const gross = (i.quantity || 0) * (i.unit_price || 0)
  const disc = i.discount_type === 'percentage' ? gross * ((i.discount || 0) / 100) : (i.discount || 0)
  return s + Math.max(0, gross - disc)
}, 0))

const taxTotal = computed(() => form.value.items.reduce((s, i) => {
  const gross = (i.quantity || 0) * (i.unit_price || 0)
  const disc = i.discount_type === 'percentage' ? gross * ((i.discount || 0) / 100) : (i.discount || 0)
  const base = Math.max(0, gross - disc)
  return s + (base * ((i.tax_rate || 0) / 100))
}, 0))

const grandTotal = computed(() => subtotal.value + taxTotal.value)

// Vendor Logic
const showVendorModal = ref(false)
const savingVendor = ref(false)
const fetchingGst = ref(false)
const newVendorForm = reactive({ name: '', gstin: '', phone: '', email: '', city: '' })

const fetchGstForNewVendor = async () => {
  if (!newVendorForm.gstin) return
  fetchingGst.value = true
  try {
    const res = await client.get(`/gst-lookup/${newVendorForm.gstin}`)
    if (res.data.legal_name) {
      newVendorForm.name = res.data.trade_name || res.data.legal_name
      newVendorForm.city = res.data.address?.city || ''
    }
  } finally { fetchingGst.value = false }
}

const saveNewVendor = async () => {
  savingVendor.value = true
  try {
    const res = await client.post('/parties', { ...newVendorForm, party_type: 'vendor', billing_address: { city: newVendorForm.city } })
    vendors.value.push(res.data.data)
    form.value.party_id = res.data.data.id
    showVendorModal.value = false
  } finally { savingVendor.value = false }
}

onMounted(async () => {
  await Promise.all([
    productStore.fetchProducts(),
    client.get('/parties', { params: { type: 'vendor', per_page: 200 } }).then(res => vendors.value = res.data.data ?? res.data)
  ])

  if (isEditMode.value) {
    const res = await client.get(`/invoices/${route.params.id}`)
    const d = res.data
    form.value = {
      ...d,
      date: new Date(d.date),
      due_date: new Date(d.due_date),
      items: d.items.map((i: any) => ({
        ...i,
        quantity: Number(i.quantity),
        unit_price: Number(i.unit_price),
        mrp: i.mrp ? Number(i.mrp) : null,
        tax_rate: Number(i.tax_rate),
        discount: Number(i.discount),
        expiry_date: i.expiry_date ? new Date(i.expiry_date) : null
      }))
    }
  } else { addItem() }
})

const save = async () => {
  if (!form.value.party_id) return error.value = 'Please select a vendor.'
  saving.value = true
  try {
    const payload = {
      ...form.value,
      date: form.value.date.toISOString().split('T')[0],
      due_date: form.value.due_date.toISOString().split('T')[0],
      items: form.value.items.map((i: any) => ({
        ...i,
        expiry_date: i.expiry_date ? i.expiry_date.toISOString().split('T')[0] : null
      }))
    }
    if (isEditMode.value) await client.put(`/invoices/${route.params.id}`, payload)
    else await client.post('/invoices', payload)
    router.push('/purchases')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to save bill.'
  } finally { saving.value = false }
}
</script>

<style scoped>
.items-card :deep(.p-card-body) { padding: 0; }
</style>
