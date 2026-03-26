<template>
    <AuthLayout>
        <template #title>Register New Business</template>

        <div class="flex flex-col gap-6 p-fluid">
            <!-- Error Message -->
            <Message v-if="error" severity="error" closable @close="error = ''">{{ error }}</Message>

            <div class="flex flex-col gap-2">
                <label for="gstin" class="font-semibold text-sm">GSTIN / Tax ID</label>
                <InputGroup>
                    <InputText id="gstin" v-model="form.gstin" placeholder="Ex. 27AAAC..." maxlength="15" @input="handleGstinInput" />
                    <Button icon="pi pi-search" severity="secondary" @click="fetchGstDetails" :loading="isFetching" />
                </InputGroup>
                <small class="text-gray-500">Auto-fill details using GSTIN</small>
                <small v-if="gstError" class="text-red-500">{{ gstError }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label for="name" class="font-semibold text-sm">Business Name *</label>
                <InputText id="name" v-model="form.name" required placeholder="Legal Entity Name" />
            </div>

            <div class="flex flex-col gap-2">
                <label for="currency" class="font-semibold text-sm">Base Currency</label>
                <Select id="currency" v-model="form.currency" :options="currencyOptions" optionLabel="label" optionValue="value" />
            </div>

            <div class="flex flex-col gap-2">
                <label for="address" class="font-semibold text-sm">Primary Address</label>
                <Textarea id="address" v-model="form.address" rows="3" autoResize placeholder="Head office address..." />
            </div>

            <Button label="Create Business" icon="pi pi-building" :loading="loading" @click="createBusiness" />

            <div class="text-center">
                <Button label="Cancel" text severity="secondary" @click="router.push('/businesses')" />
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import client from '../../api/client'
import AuthLayout from '../../layouts/AuthLayout.vue'

// PrimeVue
import InputText from 'primevue/inputtext'
import InputGroup from 'primevue/inputgroup'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Message from 'primevue/message'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const error = ref('')
const isFetching = ref(false)
const gstError = ref('')

const form = reactive({
    name: '',
    currency: 'INR',
    gstin: '',
    address: '',
    pan: ''
})

const currencyOptions = [
    { label: 'INR - Indian Rupee', value: 'INR' },
    { label: 'USD - US Dollar', value: 'USD' },
    { label: 'EUR - Euro', value: 'EUR' },
    { label: 'GBP - British Pound', value: 'GBP' }
]

const handleGstinInput = () => {
    form.gstin = form.gstin.toUpperCase()
    if (form.gstin.length === 15) {
        fetchGstDetails()
    }
}

const fetchGstDetails = async () => {
    if (!form.gstin || form.gstin.length !== 15) return
    isFetching.value = true
    gstError.value = ''
    try {
        const res = await client.get(`/gst-lookup/${form.gstin}`)
        const data = res.data
        if (data) {
            form.name = data.trade_name || data.legal_name || ''
            form.address = data.address || ''
            form.pan = form.gstin.substring(2, 12)
        }
    } catch (e: any) {
        gstError.value = 'Failed to fetch GST details.'
    } finally {
        isFetching.value = false
    }
}

const createBusiness = async () => {
    if (!form.name) return error.value = 'Business name is required.'
    loading.value = true
    error.value = ''
    try {
        const res = await client.post('/businesses', form)
        await authStore.fetchBusinesses()
        const fullBusiness = authStore.userBusinesses.find((b: any) => b.id === res.data.id)
        authStore.setActiveBusiness(fullBusiness || res.data)
        router.push('/')
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Failed to create business.'
    } finally {
        loading.value = false
    }
}
</script>
