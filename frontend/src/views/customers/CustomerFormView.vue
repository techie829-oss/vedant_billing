<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto p-fluid">
      <!-- Header Section -->
      <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex items-center gap-4">
          <Button icon="pi pi-arrow-left" severity="secondary" rounded text @click="router.back()" />
          <div>
            <h1 class="text-3xl font-bold text-gray-900 m-0">
              {{ isEditMode ? 'Edit Customer' : 'Add New Customer' }}
            </h1>
            <p class="text-gray-500 mt-1">{{ headerDescription }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <Button label="Cancel" severity="secondary" outlined @click="router.back()" />
          <Button :label="isEditMode ? 'Update Customer' : 'Save Customer'" icon="pi pi-check" :loading="loading" @click="saveCustomer" />
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6">
        <!-- Left Column: Primary Details -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
          
          <!-- Basic Information -->
          <Card class="border-none shadow-sm">
            <template #title>Basic Information</template>
            <template #content>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                  <label class="font-semibold text-sm">GSTIN / Tax ID</label>
                  <InputGroup>
                    <InputText v-model="form.gstin" placeholder="e.g., 27AAAC..." />
                    <Button icon="pi pi-search" severity="secondary" @click="fetchGst" :loading="fetchingGst" :disabled="!form.gstin || form.gstin.length < 15" />
                  </InputGroup>
                  <small class="text-gray-500 italic">Auto-fill details using GSTIN</small>
                </div>

                <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                  <label for="name" class="font-semibold text-sm">Display Name <span class="text-red-500">*</span></label>
                  <InputText id="name" v-model="form.name" required placeholder="e.g., Acme Corp" />
                </div>

                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">PAN Number</label>
                  <InputText v-model="form.pan" placeholder="ABCDE1234F" />
                </div>

                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Status</label>
                  <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" />
                </div>
              </div>
            </template>
          </Card>

          <!-- Contact Details -->
          <Card class="border-none shadow-sm">
            <template #title>Contact Information</template>
            <template #content>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Email Address</label>
                  <InputText v-model="form.email" type="email" placeholder="contact@example.com" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Phone Number</label>
                  <InputText v-model="form.phone" placeholder="e.g., +91 98765 43210" />
                </div>
              </div>
            </template>
          </Card>

          <!-- Addresses -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Billing Address -->
            <Card class="border-none shadow-sm">
              <template #title>Billing Address</template>
              <template #content>
                <div class="flex flex-col gap-3">
                  <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">Street</label>
                    <Textarea v-model="form.billing_address.street" rows="2" autoResize />
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-2">
                      <label class="text-xs font-semibold text-gray-500 uppercase">ZIP/PIN</label>
                      <InputText v-model="form.billing_address.zip" maxlength="6" />
                    </div>
                    <div class="flex flex-col gap-2">
                      <label class="text-xs font-semibold text-gray-500 uppercase">City</label>
                      <InputText v-model="form.billing_address.city" />
                    </div>
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">State</label>
                    <StateSelect v-model="form.billing_address.state" />
                  </div>
                </div>
              </template>
            </Card>

            <!-- Shipping Address -->
            <Card class="border-none shadow-sm">
              <template #title>
                <div class="flex items-center justify-between">
                  <span>Shipping Address</span>
                  <div class="flex items-center gap-2">
                    <Checkbox v-model="sameAsBilling" binary id="same_addr" />
                    <label for="same_addr" class="text-xs font-normal cursor-pointer">Same as Billing</label>
                  </div>
                </div>
              </template>
              <template #content>
                <div v-show="!sameAsBilling" class="flex flex-col gap-3">
                  <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">Street</label>
                    <Textarea v-model="form.shipping_address.street" rows="2" autoResize />
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-2">
                      <label class="text-xs font-semibold text-gray-500 uppercase">ZIP/PIN</label>
                      <InputText v-model="form.shipping_address.zip" maxlength="6" />
                    </div>
                    <div class="flex flex-col gap-2">
                      <label class="text-xs font-semibold text-gray-500 uppercase">City</label>
                      <InputText v-model="form.shipping_address.city" />
                    </div>
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">State</label>
                    <StateSelect v-model="form.shipping_address.state" />
                  </div>
                </div>
                <div v-show="sameAsBilling" class="flex flex-col items-center justify-center py-12 text-gray-400">
                  <i class="pi pi-copy text-3xl mb-2"></i>
                  <p class="text-sm">Using billing address for shipping.</p>
                </div>
              </template>
            </Card>
          </div>
        </div>

        <!-- Right Column: Finance & Notes -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
          <!-- Financials -->
          <Card class="border-none shadow-sm bg-primary-50">
            <template #title>Financial Details</template>
            <template #content>
              <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Opening Balance</label>
                  <InputNumber v-model="form.opening_balance" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" />
                  <small class="text-gray-500">Positive: Receivable (Dr), Negative: Payable (Cr)</small>
                </div>
              </div>
            </template>
          </Card>

          <!-- Internal Notes -->
          <Card class="border-none shadow-sm">
            <template #title>Internal Notes</template>
            <template #content>
              <Textarea v-model="form.notes" rows="5" autoResize placeholder="Internal notes or special instructions..." />
            </template>
          </Card>

          <!-- Error Message -->
          <Message v-if="error" severity="error" closable @close="error = null">{{ error }}</Message>
        </div>
      </div>
    </div>
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

// PrimeVue Components
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Textarea from 'primevue/textarea'
import Message from 'primevue/message'
import InputGroup from 'primevue/inputgroup'

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
  billing_address: { street: '', city: '', state: '', zip: '' },
  shipping_address: { street: '', city: '', state: '', zip: '' },
  opening_balance: 0,
  status: 'active'
})

const statusOptions = [
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

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
          opening_balance: Number(party.opening_balance) || 0,
          status: party.status || 'active'
        }

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

// Pincode Logic
watch(() => form.value.billing_address.zip, async (newZip) => {
  if (newZip && newZip.length === 6) {
    const details = await fetchPincodeDetails(newZip)
    if (details) {
      form.value.billing_address.city = details.city
      const matchedState = states.value.find(s => s.name.toLowerCase() === details.state.toLowerCase())
      form.value.billing_address.state = matchedState ? matchedState.name : details.state
      if (sameAsBilling.value) form.value.shipping_address = { ...form.value.billing_address }
    }
  }
})

watch(() => form.value.shipping_address.zip, async (newZip) => {
  if (newZip && newZip.length === 6 && !sameAsBilling.value) {
    const details = await fetchPincodeDetails(newZip)
    if (details) {
      form.value.shipping_address.city = details.city
      const matchedState = states.value.find(s => s.name.toLowerCase() === details.state.toLowerCase())
      form.value.shipping_address.state = matchedState ? matchedState.name : details.state
    }
  }
})

watch(sameAsBilling, (val) => { if (val) copyBillingToShipping() })
const copyBillingToShipping = () => { form.value.shipping_address = { ...form.value.billing_address } }

watch(() => form.value.billing_address, (newVal) => {
  if (sameAsBilling.value) form.value.shipping_address = { ...newVal }
}, { deep: true })

const fetchGst = async () => {
  if (!form.value.gstin || form.value.gstin.length < 15) return
  fetchingGst.value = true
  try {
    const response = await client.get(`/gst-lookup/${form.value.gstin}`)
    const data = response.data
    form.value.name = data.trade_name || data.legal_name || ''
    if (form.value.gstin.length === 15) form.value.pan = form.value.gstin.substring(2, 12)

    const raw = data.raw || data
    if (raw) {
      let streetParts = []
      const fullAddr = data.full_address_details || {}
      if (Object.keys(fullAddr).length > 0) {
        if (fullAddr.floor_no) streetParts.push(fullAddr.floor_no)
        if (fullAddr.building_no) streetParts.push(fullAddr.building_no)
        if (fullAddr.building_name) streetParts.push(fullAddr.building_name)
        if (fullAddr.street) streetParts.push(fullAddr.street)
        if (fullAddr.location) streetParts.push(fullAddr.location)
      } else if (data.address) form.value.billing_address.street = data.address

      if (streetParts.length > 0) form.value.billing_address.street = streetParts.filter(Boolean).join(', ')
      if (data.city || raw.city) form.value.billing_address.city = data.city || raw.city
      if (data.pincode || raw.pincode || raw.pncd) form.value.billing_address.zip = data.pincode || raw.pincode || raw.pncd

      const stateName = data.state || raw.state || raw.stcd
      if (stateName) {
        const matchedState = states.value.find(s => s.name.toLowerCase() === stateName.toLowerCase()) || states.value.find(s => s.code == stateName)
        form.value.billing_address.state = matchedState ? matchedState.name : stateName
      }
    }
    if (sameAsBilling.value) copyBillingToShipping()
  } catch (e: any) {
    alert('Failed to fetch GST details: ' + (e.response?.data?.message || e.message))
  } finally {
    fetchingGst.value = false
  }
}

const saveCustomer = async () => {
  if (!form.value.name) return error.value = 'Customer name is required.'
  loading.value = true
  error.value = null
  try {
    if (isEditMode.value) await partyStore.updateParty(route.params.id as string, form.value as any)
    else await partyStore.createParty(form.value as any)
    router.push('/customers')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'An error occurred while saving.'
  } finally {
    loading.value = false
  }
}
</script>
