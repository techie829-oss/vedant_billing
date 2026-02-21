<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ isEditMode ? 'Edit Purchase Invoice' : 'New Purchase Invoice' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          Record a bill received from a vendor/supplier.
        </p>
      </div>
      <div class="mt-4 flex sm:mt-0 sm:ml-4 gap-2">
        <button @click="$router.back()" type="button"
          class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
        <button @click="save" :disabled="saving" type="button"
          class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-70">
          {{ saving ? 'Saving...' : (isEditMode ? 'Update' : 'Save Draft') }}
        </button>
      </div>
    </div>

    <div class="space-y-6">
      <!-- Header Info -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-base font-semibold leading-7 text-gray-900 mb-6">Invoice Details</h3>
          <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">

            <!-- Vendor -->
            <div class="sm:col-span-6">
              <label class="block text-sm font-medium leading-6 text-gray-900">Vendor <span
                  class="text-red-500">*</span></label>
              <div class="mt-2">
                <select v-model="form.party_id" required
                  class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                  <option value="">-- Select Vendor --</option>
                  <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                </select>
                <router-link to="/vendors/create" target="_blank"
                  class="mt-1 inline-block text-xs text-indigo-600 hover:underline">
                  + Add new vendor
                </router-link>
              </div>
            </div>

            <!-- Invoice Number -->
            <div class="sm:col-span-3">
              <label class="block text-sm font-medium leading-6 text-gray-900">Vendor's Invoice No.</label>
              <div class="mt-2">
                <input type="text" v-model="form.invoice_number"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                  placeholder="Auto-generated if blank" />
              </div>
            </div>

            <!-- Date -->
            <div class="sm:col-span-3">
              <label class="block text-sm font-medium leading-6 text-gray-900">Invoice Date <span
                  class="text-red-500">*</span></label>
              <div class="mt-2">
                <input type="date" v-model="form.date" required
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
              </div>
            </div>

            <!-- Due Date -->
            <div class="sm:col-span-3">
              <label class="block text-sm font-medium leading-6 text-gray-900">Due Date <span
                  class="text-red-500">*</span></label>
              <div class="mt-2">
                <input type="date" v-model="form.due_date" required
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
              </div>
            </div>

            <!-- E-Way Bill Number -->
            <div class="sm:col-span-3">
              <label class="block text-sm font-medium leading-6 text-gray-900">E-Way Bill No.</label>
              <div class="mt-2">
                <input type="text" v-model="form.eway_bill_no"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
              </div>
            </div>

            <!-- Vehicle Number -->
            <div class="sm:col-span-3">
              <label class="block text-sm font-medium leading-6 text-gray-900">Vehicle No.</label>
              <div class="mt-2">
                <input type="text" v-model="form.vehicle_no"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
              </div>
            </div>

            <!-- PO Number -->
            <div class="sm:col-span-3">
              <label class="block text-sm font-medium leading-6 text-gray-900">PO Number</label>
              <div class="mt-2">
                <input type="text" v-model="form.po_number"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
              </div>
            </div>

            <!-- Challan No. -->
            <div class="sm:col-span-3">
              <label class="block text-sm font-medium leading-6 text-gray-900">Challan No.</label>
              <div class="mt-2">
                <input type="text" v-model="form.challan_no"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold leading-7 text-gray-900">Items</h3>
          </div>

          <!-- Desktop Items Table -->
          <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="pb-2 text-left text-xs font-medium text-gray-500 uppercase pr-2 min-w-[200px]">Item /
                    Description
                  </th>
                  <th class="pb-2 text-left text-xs font-medium text-gray-500 uppercase px-2 w-28">MRP</th>
                  <th class="pb-2 text-left text-xs font-medium text-gray-500 uppercase px-2 w-32">Batch No</th>
                  <th class="pb-2 text-left text-xs font-medium text-gray-500 uppercase px-2 w-36">Mfg/Exp Date</th>
                  <th class="pb-2 text-right text-xs font-medium text-gray-500 uppercase px-2 w-20">Qty</th>
                  <th class="pb-2 text-right text-xs font-medium text-gray-500 uppercase px-2 w-24">Rate</th>
                  <th class="pb-2 text-right text-xs font-medium text-gray-500 uppercase px-2 w-24">Disc</th>
                  <th class="pb-2 text-right text-xs font-medium text-gray-500 uppercase px-2 w-20">Tax %</th>
                  <th class="pb-2 text-right text-xs font-medium text-gray-500 uppercase px-2 w-28">Total</th>
                  <th class="pb-2 w-10"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(item, idx) in form.items" :key="idx">
                  <td class="py-3 pr-2 align-top">
                    <div class="relative w-full">
                      <ProductAutocomplete :items="products" :model-value="item.product_id ?? null"
                        :initial-display="item.name || (item.product_id && products.find(p => p.id === item.product_id)?.name) || ''"
                        @update:model-value="(val: any) => item.product_id = val"
                        @select="(prod: any) => onProductSelect(item, prod)"
                        @change="(val: string) => { item.name = val; item.product_id = null; }" />
                    </div>
                  </td>
                  <td class="py-3 px-2 align-top">
                    <input type="number" step="any" v-model.number="item.mrp" placeholder="MRP"
                      class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 text-sm" />
                  </td>
                  <td class="py-3 px-2 align-top">
                    <input type="text" v-model="item.batch_number" placeholder="Batch"
                      class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 text-sm" />
                  </td>
                  <td class="py-3 px-2 align-top">
                    <input type="date" v-model="item.expiry_date"
                      class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm" />
                  </td>
                  <td class="py-3 px-2 align-top">
                    <input type="number" step="any" v-model.number="item.quantity" min="0.01" @input="calcItem(item)"
                      class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 text-right focus:ring-2 focus:ring-indigo-600 text-sm" />
                  </td>
                  <td class="py-3 px-2 align-top">
                    <div class="relative">
                      <span class="absolute inset-y-0 left-2 flex items-center text-gray-400 text-xs mt-1.5">₹</span>
                      <input type="number" step="any" v-model.number="item.unit_price" min="0" @input="calcItem(item)"
                        class="block w-full rounded-md border-0 py-1.5 pl-6 pr-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 text-right focus:ring-2 focus:ring-indigo-600 text-sm" />
                    </div>
                  </td>
                  <td class="py-3 px-2 align-top">
                    <div class="relative">
                      <span class="absolute inset-y-0 left-2 flex items-center text-gray-400 text-xs mt-1.5">₹</span>
                      <input type="number" step="any" v-model.number="item.discount" min="0" @input="calcItem(item)"
                        class="block w-full rounded-md border-0 py-1.5 pl-6 pr-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 text-right focus:ring-2 focus:ring-indigo-600 text-sm" />
                    </div>
                  </td>
                  <td class="py-3 px-2 align-top">
                    <input type="number" step="any" v-model.number="item.tax_rate" min="0" max="100"
                      @input="calcItem(item)"
                      class="block w-full rounded-md border-0 py-1.5 px-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 text-right focus:ring-2 focus:ring-indigo-600 text-sm" />
                  </td>
                  <td class="py-3 px-2 text-right text-sm font-medium text-gray-900 align-top pt-5">
                    ₹{{ item.total.toFixed(2) }}
                  </td>
                  <td class="py-3 pl-2 align-top pt-4">
                    <button @click="removeItem(idx)" type="button" class="text-red-400 hover:text-red-600"
                      v-if="form.items.length > 1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Items -->
          <div class="sm:hidden space-y-4">
            <div v-for="(item, idx) in form.items" :key="idx"
              class="p-4 bg-gray-50 rounded-lg shadow-sm border border-gray-200 space-y-4">

              <div class="flex justify-between items-start">
                <div class="w-full mr-4">
                  <label class="block text-xs font-medium text-gray-700 mb-1">Item</label>
                  <ProductAutocomplete :items="products" :model-value="item.product_id ?? null"
                    :initial-display="item.name || (item.product_id && products.find(p => p.id === item.product_id)?.name) || ''"
                    @update:model-value="(val: any) => item.product_id = val"
                    @select="(prod: any) => onProductSelect(item, prod)"
                    @change="(val: string) => { item.name = val; item.product_id = null; }" />
                </div>
                <button @click="removeItem(idx)" v-if="form.items.length > 1" type="button"
                  class="text-red-500 hover:text-red-700 p-1 mt-5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-3 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">MRP</label>
                  <input type="number" step="any" v-model.number="item.mrp" placeholder="MRP"
                    class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm focus:ring-2 focus:ring-indigo-600" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Batch</label>
                  <input type="text" v-model="item.batch_number" placeholder="Batch"
                    class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm focus:ring-2 focus:ring-indigo-600" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
                  <input type="date" v-model="item.expiry_date"
                    class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm focus:ring-2 focus:ring-indigo-600" />
                </div>
              </div>

              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Qty</label>
                  <input type="number" step="any" v-model.number="item.quantity" @input="calcItem(item)"
                    class="block w-full rounded-md border-0 py-1.5 px-2 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Rate (₹)</label>
                  <input type="number" step="any" v-model.number="item.unit_price" @input="calcItem(item)"
                    class="block w-full rounded-md border-0 py-1.5 px-2 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Disc (₹)</label>
                  <input type="number" step="any" v-model.number="item.discount" @input="calcItem(item)"
                    class="block w-full rounded-md border-0 py-1.5 px-2 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Tax %</label>
                  <input type="number" step="any" v-model.number="item.tax_rate" @input="calcItem(item)"
                    class="block w-full rounded-md border-0 py-1.5 px-2 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600" />
                </div>
              </div>
              <div class="flex justify-between items-center bg-white p-3 rounded border border-gray-100 mt-2">
                <span class="text-xs font-medium text-gray-500 uppercase">Item Total</span>
                <span class="text-base font-bold text-gray-900">₹{{ item.total.toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <button @click="addItem" type="button"
            class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Item
          </button>

          <!-- Totals -->
          <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end">
            <div class="w-64 space-y-1.5 text-sm">
              <div class="flex justify-between text-gray-500">
                <span>Subtotal</span>
                <span>₹{{ subtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-gray-500">
                <span>Tax</span>
                <span>₹{{ taxTotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between font-bold text-gray-900 text-base pt-1 border-t border-gray-200">
                <span>Grand Total</span>
                <span>₹{{ grandTotal.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Notes</h3>
          <textarea v-model="form.notes" rows="3" placeholder="Internal notes about this purchase..."
            class="block w-full max-w-2xl rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"></textarea>
        </div>
      </div>

      <div v-if="error" class="rounded-md bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'
import ProductAutocomplete from '../../components/ProductAutocomplete.vue'
import { useProductStore } from '../../stores/product'

const router = useRouter()
const route = useRoute()
const productStore = useProductStore()

const saving = ref(false)
const error = ref<string | null>(null)
const vendors = ref<any[]>([])

const products = computed(() => productStore.products)

const isEditMode = computed(() => !!route.params.id)

const today = new Date().toISOString().split('T')[0]
const due = new Date(Date.now() + 30 * 864e5).toISOString().split('T')[0]

const makeItem = () => ({
  product_id: null,
  name: '',
  quantity: 1,
  unit_price: 0,
  mrp: null,
  batch_number: '',
  expiry_date: '',
  tax_rate: 0,
  discount: 0,
  total: 0
})

const form = ref({
  type: 'purchase_invoice',
  party_id: '',
  invoice_number: '',
  date: today,
  due_date: due,
  notes: '',
  eway_bill_no: '',
  vehicle_no: '',
  po_number: '',
  challan_no: '',
  items: [makeItem()]
})

const calcItem = (item: any) => {
  const base = Math.max(0, item.quantity * item.unit_price - (item.discount || 0))
  item.total = base + base * (item.tax_rate / 100)
}

const onProductSelect = (item: any, product: any) => {
  if (!product) return
  item.product_id = product.id
  item.name = product.name

  if (product.purchase_price) {
    let basePrice = Number(product.purchase_price)
    if (product.is_tax_inclusive && product.tax_rate) {
      basePrice = basePrice / (1 + (product.tax_rate / 100))
    }
    item.unit_price = basePrice
  }

  if (product.tax_rate) {
    item.tax_rate = product.tax_rate
  }
  calcItem(item)
}

const subtotal = computed(() => form.value.items.reduce((s: number, i: any) => {
  const base = Math.max(0, i.quantity * i.unit_price - (i.discount || 0))
  return s + base
}, 0))

const taxTotal = computed(() => form.value.items.reduce((s: number, i: any) => {
  const base = Math.max(0, i.quantity * i.unit_price - (i.discount || 0))
  return s + base * (i.tax_rate / 100)
}, 0))

const grandTotal = computed(() => subtotal.value + taxTotal.value)

const addItem = () => form.value.items.push(makeItem())
const removeItem = (idx: number) => form.value.items.splice(idx, 1)

onMounted(async () => {
  if (productStore.products.length === 0) {
    await productStore.fetchProducts()
  }

  // Load vendors
  const res = await client.get('/parties', { params: { type: 'vendor', per_page: 200 } })
  vendors.value = res.data.data ?? res.data

  // Load existing if editing
  if (isEditMode.value) {
    const inv = await client.get(`/invoices/${route.params.id}`)
    const d = inv.data
    form.value.party_id = d.party_id
    form.value.invoice_number = d.invoice_number
    form.value.date = d.date?.split('T')[0] ?? today
    form.value.due_date = d.due_date?.split('T')[0] ?? due
    form.value.notes = d.notes ?? ''
    form.value.eway_bill_no = d.eway_bill_no ?? ''
    form.value.vehicle_no = d.vehicle_no ?? ''
    form.value.po_number = d.po_number ?? ''
    form.value.challan_no = d.challan_no ?? ''

    form.value.items = d.items.map((i: any) => ({
      product_id: i.product_id,
      name: i.name,
      quantity: Number(i.quantity),
      unit_price: Number(i.unit_price),
      mrp: i.mrp ? Number(i.mrp) : null,
      batch_number: i.batch_number ?? '',
      expiry_date: i.expiry_date ?? '',
      tax_rate: Number(i.tax_rate),
      discount: Number(i.discount),
      total: Number(i.total)
    }))
  }
})

const save = async () => {
  if (!form.value.party_id) { error.value = 'Please select a vendor.'; return }
  saving.value = true
  error.value = null
  try {
    const payload = {
      type: 'purchase_invoice',
      party_id: form.value.party_id,
      invoice_number: form.value.invoice_number || undefined,
      date: form.value.date,
      due_date: form.value.due_date,
      notes: form.value.notes,
      eway_bill_no: form.value.eway_bill_no,
      vehicle_no: form.value.vehicle_no,
      po_number: form.value.po_number,
      challan_no: form.value.challan_no,
      items: form.value.items.map((i: any) => ({
        product_id: i.product_id,
        name: i.name,
        quantity: i.quantity,
        unit_price: i.unit_price,
        mrp: i.mrp,
        batch_number: i.batch_number,
        expiry_date: i.expiry_date,
        tax_rate: i.tax_rate,
        discount: i.discount || 0
      }))
    }
    if (isEditMode.value) {
      await client.put(`/invoices/${route.params.id}`, payload)
    } else {
      await client.post('/invoices', payload)
    }
    router.push('/purchases')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to save purchase invoice.'
  } finally {
    saving.value = false
  }
}
</script>
