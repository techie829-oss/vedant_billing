<template>
  <AppLayout>

    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ isEditMode ? 'Edit Customer' : 'Add New Customer' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          {{ headerDescription }}
        </p>
      </div>
      <div class="mt-4 flex sm:mt-0 sm:ml-4">
        <button @click="$router.back()" type="button"
          class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
        <button @click="saveCustomer" :disabled="loading" type="button"
          class="ml-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
          {{ loading ? 'Saving...' : 'Save Customer' }}
        </button>
      </div>
    </div>

    <form @submit.prevent="saveCustomer" class="space-y-6">
      <!-- Basic Information Card -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-full">
              <h3 class="text-base font-semibold leading-7 text-gray-900">Basic Information</h3>
              <p class="mt-1 text-sm leading-6 text-gray-500">Search by GSTIN to auto-fill details or enter manually.
              </p>
            </div>

            <div class="sm:col-span-4">
              <label for="gstin" class="block text-sm font-medium leading-6 text-gray-900">GSTIN / Tax ID</label>
              <div class="flex gap-2">
                <input type="text" name="gstin" id="gstin" v-model="form.gstin"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  placeholder="27AAAC..." />
                <button type="button" @click="fetchGst" :disabled="fetchingGst || !form.gstin || form.gstin.length < 15"
                  class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-600 shadow-sm hover:bg-indigo-100 disabled:opacity-50">
                  {{ fetchingGst ? 'Fetching...' : 'Fetch' }}
                </button>
              </div>
            </div>

            <div class="sm:col-span-4">
              <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Display Name <span
                  class="text-red-500">*</span></label>
              <div class="mt-2">
                <input type="text" name="name" id="name" v-model="form.name" required
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  placeholder="e.g. Acme Corp" />
              </div>
            </div>

            <div class="sm:col-span-2">
              <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
              <div class="relative mt-2">
                <select id="status" name="status" v-model="form.status"
                  class="block w-full appearance-none rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                  <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact & Tax Info Card -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-full">
              <h3 class="text-base font-semibold leading-7 text-gray-900">Contact & Tax</h3>
              <p class="mt-1 text-sm leading-6 text-gray-500">Communication and tax compliance details.</p>
            </div>

            <div class="sm:col-span-3">
              <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email Address</label>
              <div class="mt-2">
                <input type="email" name="email" id="email" v-model="form.email"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  placeholder="contact@example.com" />
              </div>
            </div>

            <div class="sm:col-span-3">
              <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Phone</label>
              <div class="mt-2">
                <input type="text" name="phone" id="phone" v-model="form.phone"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
              </div>
            </div>

            <div class="sm:col-span-3">
              <label for="pan" class="block text-sm font-medium leading-6 text-gray-900">PAN Number</label>
              <div class="mt-2">
                <input type="text" name="pan" id="pan" v-model="form.pan"
                  class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  placeholder="ABCDE1234F" />
              </div>
            </div>

            <div class="sm:col-span-full border-t border-gray-100 pt-6 mt-2">
              <h4 class="text-sm font-medium leading-6 text-gray-900 mb-4">Billing Address</h4>
              <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <div class="sm:col-span-6">
                  <label for="street" class="block text-sm font-medium leading-6 text-gray-900">Street Address</label>
                  <div class="mt-2">
                    <textarea name="street" id="street" rows="2" v-model="form.billing_address.street"
                      class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                  </div>
                </div>

                <div class="sm:col-span-2">
                  <label for="city" class="block text-sm font-medium leading-6 text-gray-900">City</label>
                  <div class="mt-2">
                    <input type="text" name="city" id="city" v-model="form.billing_address.city"
                      class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>

                <div class="sm:col-span-2">
                  <label for="state" class="block text-sm font-medium leading-6 text-gray-900">State</label>
                  <div class="mt-2">
                    <StateSelect v-model="form.billing_address.state" />
                  </div>
                </div>

                <div class="sm:col-span-2">
                  <label for="zip" class="block text-sm font-medium leading-6 text-gray-900">ZIP / Postal Code</label>
                  <div class="mt-2">
                    <input type="text" name="zip" id="zip" v-model="form.billing_address.zip"
                      class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>
              </div>
            </div>

            <div class="sm:col-span-full border-t border-gray-100 pt-6 mt-2">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-medium leading-6 text-gray-900">Shipping Address</h4>
                <div class="relative flex items-start">
                  <div class="flex h-6 items-center">
                    <input id="same_as_billing" aria-describedby="same_as_billing-description" name="same_as_billing"
                      type="checkbox" v-model="sameAsBilling"
                      class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                  </div>
                  <div class="ml-3 text-sm leading-6">
                    <label for="same_as_billing" class="font-medium text-gray-900">Same as billing address</label>
                  </div>
                </div>
              </div>

              <div v-show="!sameAsBilling" class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <div class="sm:col-span-6">
                  <label for="shipping_street" class="block text-sm font-medium leading-6 text-gray-900">Street
                    Address</label>
                  <div class="mt-2">
                    <textarea name="shipping_street" id="shipping_street" rows="2"
                      v-model="form.shipping_address.street"
                      class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                  </div>
                </div>

                <div class="sm:col-span-2">
                  <label for="shipping_city" class="block text-sm font-medium leading-6 text-gray-900">City</label>
                  <div class="mt-2">
                    <input type="text" name="shipping_city" id="shipping_city" v-model="form.shipping_address.city"
                      class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>

                <div class="sm:col-span-2">
                  <label for="shipping_state" class="block text-sm font-medium leading-6 text-gray-900">State</label>
                  <div class="mt-2">
                    <StateSelect v-model="form.shipping_address.state" />
                  </div>
                </div>

                <div class="sm:col-span-2">
                  <label for="shipping_zip" class="block text-sm font-medium leading-6 text-gray-900">ZIP / Postal
                    Code</label>
                  <div class="mt-2">
                    <input type="text" name="shipping_zip" id="shipping_zip" v-model="form.shipping_address.zip"
                      class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes Card -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-full">
              <h3 class="text-base font-semibold leading-7 text-gray-900">Notes</h3>
              <p class="mt-1 text-sm leading-6 text-gray-500">Add any internal notes about the customer.</p>
            </div>

            <div class="sm:col-span-full">
              <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Internal Notes</label>
              <div class="mt-2">
                <textarea id="notes" name="notes" rows="3" v-model="form.notes"
                  class="block w-full rounded-md border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Details Card -->
      <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-full">
              <h3 class="text-base font-semibold leading-7 text-gray-900">Financial Details</h3>
              <p class="mt-1 text-sm leading-6 text-gray-500">Opening balances and credit settings.</p>
            </div>

            <div class="sm:col-span-3">
              <label for="opening_balance" class="block text-sm font-medium leading-6 text-gray-900">Opening
                Balance</label>
              <div class="relative mt-2 rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 sm:text-sm">₹</span>
                </div>
                <input type="number" name="opening_balance" id="opening_balance" v-model.number="form.opening_balance"
                  class="block w-full rounded-md border-0 py-2 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  placeholder="0.00" aria-describedby="price-currency" />
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                  <span class="text-gray-500 sm:text-sm" id="price-currency">INR</span>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">Positive for receivable, negative for payable.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error saving customer</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ error }}</p>
            </div>
          </div>
        </div>
      </div>
    </form>
  </AppLayout>
</template>

<script setup lang="ts">

import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePartyStore } from '../../stores/party'
import { useGeneralStore } from '../../stores/general'
import { storeToRefs } from 'pinia'
import AppLayout from '../../layouts/AppLayout.vue'
import { fetchPincodeDetails } from '../../services/PincodeService'
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
  party_type: 'customer',
  name: '',
  email: '',
  phone: '',
  gstin: '',
  pan: '',
  notes: '',
  billing_address: {
    street: '',
    city: '',
    state: '',
    zip: ''
  },
  shipping_address: {
    street: '',
    city: '',
    state: '',
    zip: ''
  },
  opening_balance: 0,
  status: 'active'
})

const sameAsBilling = ref(false)

const isEditMode = computed(() => route.params.id !== undefined)

const headerDescription = computed(() => {
  return isEditMode.value
    ? 'Update customer information and settings.'
    : 'Create a new customer profile for invoicing and billing.'
})

onMounted(async () => {
  generalStore.fetchStates()

  if (isEditMode.value) {
    loading.value = true
    try {
      const party = await partyStore.fetchParty(route.params.id as string)
      if (party) {
        form.value = {
          party_type: party.party_type,
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

        // Check if shipping address is identical to billing address
        if (JSON.stringify(party.billing_address) === JSON.stringify(party.shipping_address)) {
          sameAsBilling.value = true
        }
      }
    } catch (e) {
      error.value = 'Failed to load customer details.'
    } finally {
      loading.value = false
    }
  }
})

// Pincode Logic for Billing
watch(() => form.value.billing_address.zip, async (newZip) => {
  if (newZip && newZip.length === 6) {
    const details = await fetchPincodeDetails(newZip)
    if (details) {
      form.value.billing_address.city = details.city
      const matchedState = states.value.find(s => s.name.toLowerCase() === details.state.toLowerCase())
      if (matchedState) form.value.billing_address.state = matchedState.name
      else form.value.billing_address.state = details.state

      if (sameAsBilling.value) form.value.shipping_address = { ...form.value.billing_address }
    }
  }
})

// Pincode Logic for Shipping
watch(() => form.value.shipping_address.zip, async (newZip) => {
  if (newZip && newZip.length === 6 && !sameAsBilling.value) {
    const details = await fetchPincodeDetails(newZip)
    if (details) {
      form.value.shipping_address.city = details.city
      const matchedState = states.value.find(s => s.name.toLowerCase() === details.state.toLowerCase())
      if (matchedState) form.value.shipping_address.state = matchedState.name
      else form.value.shipping_address.state = details.state
    }
  }
})

watch(sameAsBilling, (val) => {
  if (val) {
    copyBillingToShipping()
  }
})

const copyBillingToShipping = () => {
  form.value.shipping_address = { ...form.value.billing_address }
}

// Watch for changes in billing to auto-update shipping if checked
watch(() => form.value.billing_address, (newVal) => {
  if (sameAsBilling.value) {
    form.value.shipping_address = { ...newVal }
  }
}, { deep: true })



const fetchGst = async () => {
  if (!form.value.gstin || form.value.gstin.length < 15) return

  fetchingGst.value = true
  try {
    const response = await client.get(`/gst-lookup/${form.value.gstin}`)
    const data = response.data

    // Name: Prefer Trade Name, fallback to Legal Name
    form.value.name = data.trade_name || data.legal_name || ''

    // Auto-extract PAN
    if (form.value.gstin.length === 15) {
      form.value.pan = form.value.gstin.substring(2, 12)
    }

    // Address
    const raw = data.raw || data
    if (raw) {
      // 1. Street Address
      let streetParts = []
      const fullAddr = data.full_address_details || {}

      // Prioritize verified granular details
      if (Object.keys(fullAddr).length > 0) {
        if (fullAddr.floor_no) streetParts.push(fullAddr.floor_no)
        if (fullAddr.building_no) streetParts.push(fullAddr.building_no)
        if (fullAddr.building_name) streetParts.push(fullAddr.building_name)
        if (fullAddr.street) streetParts.push(fullAddr.street)
        if (fullAddr.location) streetParts.push(fullAddr.location)
        // if (fullAddr.district && fullAddr.district !== data.city) streetParts.push(fullAddr.district)
      } else {
        // Handle various API formats (standard vs razorpay vs internal) fallback
        if (raw.flno) streetParts.push(raw.flno)
        if (raw.bno) streetParts.push(raw.bno)
        if (raw.bnm) streetParts.push(raw.bnm)
        if (raw.st) streetParts.push(raw.st)
        if (raw.loc) streetParts.push(raw.loc)
        if (raw.address_line1) streetParts.push(raw.address_line1)
        if (raw.address_line2) streetParts.push(raw.address_line2)
      }

      if (streetParts.length > 0) {
        form.value.billing_address.street = streetParts.filter(Boolean).join('\n')
      } else if (data.address) {
        form.value.billing_address.street = data.address
      }

      // 2. City
      if (data.city) form.value.billing_address.city = data.city
      else if (raw.city) form.value.billing_address.city = raw.city

      // 3. Zip / Pincode
      if (data.pincode) form.value.billing_address.zip = data.pincode
      else {
        const zip = raw.pincode || raw.pncd
        if (zip) form.value.billing_address.zip = zip
      }

      // 4. State
      const stateName = data.state || raw.state || raw.stcd
      if (stateName) {
        const matchedState = states.value.find(s => s.name.toLowerCase() === stateName.toLowerCase()) ||
          states.value.find(s => s.code == stateName) // Assuming code match if applicable
        if (matchedState) form.value.billing_address.state = matchedState.name
        else form.value.billing_address.state = stateName
      }
    }

    // Auto-update shipping if enabled
    if (sameAsBilling.value) {
      copyBillingToShipping()
    }

    // alert('Details fetched successfully!') // Optional: User might see fields filling up
  } catch (e: any) {
    console.error(e)
    alert('Failed to fetch GST details: ' + (e.response?.data?.message || e.message))
  } finally {
    fetchingGst.value = false
  }
}

const saveCustomer = async () => {
  loading.value = true
  error.value = null

  try {
    if (isEditMode.value) {
      await partyStore.updateParty(route.params.id as string, form.value as any)
    } else {
      await partyStore.createParty(form.value as any)
    }
    router.push('/customers')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'An error occurred while saving.'
  } finally {
    loading.value = false
  }
}
</script>
