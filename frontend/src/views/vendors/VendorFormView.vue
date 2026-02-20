<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ isEditMode ? 'Edit Vendor' : 'Add New Vendor' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          {{ isEditMode ? 'Update vendor information.' : 'Add a new supplier or vendor for purchase invoices.' }}
        </p>
      </div>
      <div class="mt-4 flex sm:mt-0 sm:ml-4">
        <button @click="$router.back()" type="button"
          class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
        <button @click="saveVendor" :disabled="loading" type="button"
          class="ml-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
          {{ loading ? 'Saving...' : 'Save Vendor' }}
        </button>
      </div>
    </div>

    <form @submit.prevent="saveVendor" class="space-y-6">
      <!-- Basic Info -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
          <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-full">
              <h3 class="text-base font-semibold leading-7 text-gray-900">Basic Information</h3>
              <p class="mt-1 text-sm leading-6 text-gray-500">Search by GSTIN to auto-fill details or enter manually.</p>
            </div>

            <div class="sm:col-span-4">
              <label for="gstin" class="block text-sm font-medium leading-6 text-gray-900">GSTIN / Tax ID</label>
              <div class="flex gap-2 mt-2">
                <input type="text" id="gstin" v-model="form.gstin"
                  class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                  placeholder="27AAAC..." />
                <button type="button" @click="fetchGst" :disabled="fetchingGst || !form.gstin || form.gstin.length < 15"
                  class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-600 shadow-sm hover:bg-indigo-100 disabled:opacity-50">
                  {{ fetchingGst ? 'Fetching...' : 'Fetch' }}
                </button>
              </div>
            </div>

            <div class="sm:col-span-4">
              <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Vendor Name <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <input type="text" id="name" v-model="form.name" required
                  class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                  placeholder="e.g. ABC Suppliers Ltd" />
              </div>
            </div>

            <div class="sm:col-span-2">
              <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
              <div class="mt-2">
                <select id="status" v-model="form.status"
                  class="block w-full rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
          <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-full">
              <h3 class="text-base font-semibold leading-7 text-gray-900">Contact & Tax</h3>
            </div>
            <div class="sm:col-span-3">
              <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email Address</label>
              <div class="mt-2">
                <input type="email" id="email" v-model="form.email"
                  class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                  placeholder="contact@vendor.com" />
              </div>
            </div>
            <div class="sm:col-span-3">
              <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Phone</label>
              <div class="mt-2">
                <input type="text" id="phone" v-model="form.phone"
                  class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
              </div>
            </div>
            <div class="sm:col-span-3">
              <label for="pan" class="block text-sm font-medium leading-6 text-gray-900">PAN Number</label>
              <div class="mt-2">
                <input type="text" id="pan" v-model="form.pan"
                  class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                  placeholder="ABCDE1234F" />
              </div>
            </div>

            <!-- Billing Address -->
            <div class="sm:col-span-full border-t border-gray-100 pt-6 mt-2">
              <h4 class="text-sm font-medium leading-6 text-gray-900 mb-4">Billing Address</h4>
              <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <div class="sm:col-span-6">
                  <label class="block text-sm font-medium leading-6 text-gray-900">Street Address</label>
                  <div class="mt-2">
                    <textarea rows="2" v-model="form.billing_address.street"
                      class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"></textarea>
                  </div>
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium leading-6 text-gray-900">City</label>
                  <div class="mt-2">
                    <input type="text" v-model="form.billing_address.city"
                      class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
                  </div>
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium leading-6 text-gray-900">State</label>
                  <div class="mt-2">
                    <StateSelect v-model="form.billing_address.state" />
                  </div>
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium leading-6 text-gray-900">PIN Code</label>
                  <div class="mt-2">
                    <input type="text" v-model="form.billing_address.zip"
                      class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
          <h3 class="text-base font-semibold leading-7 text-gray-900">Notes</h3>
          <div class="mt-4">
            <textarea rows="3" v-model="form.notes"
              class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
              placeholder="Internal notes about this vendor..."></textarea>
          </div>
        </div>
      </div>

      <!-- Financial -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
          <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Financial Details</h3>
          <div class="max-w-xs">
            <label class="block text-sm font-medium leading-6 text-gray-900">Opening Balance</label>
            <div class="relative mt-2 rounded-md shadow-sm">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 sm:text-sm">₹</span>
              </div>
              <input type="number" v-model.number="form.opening_balance"
                class="block w-full rounded-md border-0 py-2 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                placeholder="0.00" />
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <span class="text-gray-500 sm:text-sm">INR</span>
              </div>
            </div>
            <p class="mt-1 text-xs text-gray-500">Negative = amount owed to vendor.</p>
          </div>
        </div>
      </div>

      <div v-if="error" class="rounded-md bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
    </form>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePartyStore } from '../../stores/party'
import { useGeneralStore } from '../../stores/general'
import { storeToRefs } from 'pinia'
import AppLayout from '../../layouts/AppLayout.vue'
import StateSelect from '../../components/StateSelect.vue'
import client from '../../api/client'

const router = useRouter()
const route = useRoute()
const partyStore = usePartyStore()
const generalStore = useGeneralStore()
const { states } = storeToRefs(generalStore)

const loading = ref(false)
const fetchingGst = ref(false)
const error = ref<string | null>(null)

const form = ref({
  party_type: 'vendor',
  name: '',
  email: '',
  phone: '',
  gstin: '',
  pan: '',
  notes: '',
  billing_address: { street: '', city: '', state: '', zip: '' },
  shipping_address: { street: '', city: '', state: '', zip: '' },
  opening_balance: 0,
  status: 'active'
})

const isEditMode = computed(() => route.params.id !== undefined)

onMounted(async () => {
  generalStore.fetchStates()
  if (isEditMode.value) {
    loading.value = true
    try {
      const party = await partyStore.fetchParty(route.params.id as string)
      if (party) {
        form.value = {
          party_type: 'vendor',
          name: party.name,
          email: party.email || '',
          phone: party.phone || '',
          gstin: party.gstin || '',
          pan: party.pan || '',
          notes: party.notes || '',
          billing_address: party.billing_address || { street: '', city: '', state: '', zip: '' },
          shipping_address: party.shipping_address || { street: '', city: '', state: '', zip: '' },
          opening_balance: party.opening_balance,
          status: party.status
        }
      }
    } catch (e) {
      error.value = 'Failed to load vendor details.'
    } finally {
      loading.value = false
    }
  }
})

const fetchGst = async () => {
  if (!form.value.gstin || form.value.gstin.length < 15) return
  fetchingGst.value = true
  try {
    const response = await client.get(`/gst-lookup/${form.value.gstin}`)
    const data = response.data
    form.value.name = data.trade_name || data.legal_name || ''
    if (form.value.gstin.length === 15) form.value.pan = form.value.gstin.substring(2, 12)
    const raw = data.raw || data
    if (data.city) form.value.billing_address.city = data.city
    if (data.pincode) form.value.billing_address.zip = data.pincode
    const stateName = data.state || raw.state
    if (stateName) {
      const matched = states.value.find((s: any) => s.name.toLowerCase() === stateName.toLowerCase())
      form.value.billing_address.state = matched ? matched.name : stateName
    }
  } catch (e: any) {
    alert('Failed to fetch GST details: ' + (e.response?.data?.message || e.message))
  } finally {
    fetchingGst.value = false
  }
}

const saveVendor = async () => {
  loading.value = true
  error.value = null
  try {
    if (isEditMode.value) {
      await partyStore.updateParty(route.params.id as string, form.value as any)
    } else {
      await partyStore.createParty(form.value as any)
    }
    router.push('/vendors')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'An error occurred while saving.'
  } finally {
    loading.value = false
  }
}
</script>
