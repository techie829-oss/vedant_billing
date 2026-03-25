<template>
  <AppLayout>
    <div class="p-fluid">
      <!-- Header Section -->
      <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex items-center gap-4">
          <Button icon="pi pi-arrow-left" severity="secondary" rounded text @click="router.back()" />
          <div>
            <h1 class="text-3xl font-bold text-gray-900 m-0">
              {{ isEditMode ? 'Edit Vendor' : 'Add New Vendor' }}
            </h1>
            <p class="text-gray-500 mt-1">
              {{ isEditMode ? 'Update vendor information and settings.' : 'Add a new supplier or vendor for your business.' }}
            </p>
          </div>
        </div>
        <div class="flex gap-2">
          <Button label="Cancel" severity="secondary" outlined @click="router.back()" />
          <Button :label="isEditMode ? 'Update Vendor' : 'Save Vendor'" icon="pi pi-check" :loading="loading" @click="saveVendor" />
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
                  <div class="p-inputgroup">
                    <InputText v-model="form.gstin" placeholder="e.g., 27AAAC..." />
                    <Button icon="pi pi-search" severity="secondary" @click="fetchGst" :loading="fetchingGst" :disabled="!form.gstin || form.gstin.length < 15" />
                  </div>
                  <small class="text-gray-500 italic">Auto-fill details using GSTIN</small>
                </div>

                <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                  <label for="name" class="font-semibold text-sm">Vendor Name <span class="text-red-500">*</span></label>
                  <InputText id="name" v-model="form.name" required placeholder="e.g., ABC Suppliers Ltd" />
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
                  <InputText v-model="form.email" type="email" placeholder="contact@vendor.com" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Phone Number</label>
                  <InputText v-model="form.phone" placeholder="e.g., +91 98765 43210" />
                </div>
              </div>
            </template>
          </Card>

          <!-- Billing Address -->
          <Card class="border-none shadow-sm">
            <template #title>Address Details</template>
            <template #content>
              <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-2">
                  <label class="text-xs font-semibold text-gray-500 uppercase">Street Address</label>
                  <Textarea v-model="form.billing_address.street" rows="2" autoResize />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">ZIP/PIN Code</label>
                    <InputText v-model="form.billing_address.zip" maxlength="6" />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">City</label>
                    <InputText v-model="form.billing_address.city" />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">State</label>
                    <StateSelect v-model="form.billing_address.state" />
                  </div>
                </div>
              </div>
            </template>
          </Card>
        </div>

        <!-- Right Column: Finance & Notes -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
          <!-- Financials -->
          <Card class="border-none shadow-sm bg-orange-50">
            <template #title>Financial Details</template>
            <template #content>
              <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                  <label class="font-semibold text-sm">Opening Balance</label>
                  <InputNumber v-model="form.opening_balance" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" />
                  <small class="text-gray-500">Negative: Amount owed to vendor (Cr), Positive: Receivable (Dr)</small>
                </div>
              </div>
            </template>
          </Card>

          <!-- Internal Notes -->
          <Card class="border-none shadow-sm">
            <template #title>Internal Notes</template>
            <template #content>
              <Textarea v-model="form.notes" rows="5" autoResize placeholder="Terms, product categories, or notes..." />
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
import Textarea from 'primevue/textarea'
import Message from 'primevue/message'

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

const statusOptions = [
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

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
          opening_balance: Number(party.opening_balance) || 0,
          status: party.status || 'active'
        }
      }
    } catch (e) {
      error.value = 'Failed to load vendor details.'
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
    if (data.city || raw.city) form.value.billing_address.city = data.city || raw.city
    if (data.pincode || raw.pincode || raw.pncd) form.value.billing_address.zip = data.pincode || raw.pincode || raw.pncd
    
    const stateName = data.state || raw.state || raw.stcd
    if (stateName) {
      const matchedState = states.value.find(s => s.name.toLowerCase() === stateName.toLowerCase()) || states.value.find(s => s.code == stateName)
      form.value.billing_address.state = matchedState ? matchedState.name : stateName
    }
  } catch (e: any) {
    alert('Failed to fetch GST details: ' + (e.response?.data?.message || e.message))
  } finally {
    fetchingGst.value = false
  }
}

const saveVendor = async () => {
  if (!form.value.name) return error.value = 'Vendor name is required.'
  loading.value = true
  error.value = null
  try {
    if (isEditMode.value) await partyStore.updateParty(route.params.id as string, form.value as any)
    else await partyStore.createParty(form.value as any)
    router.push('/vendors')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'An error occurred while saving.'
  } finally {
    loading.value = false
  }
}
</script>
