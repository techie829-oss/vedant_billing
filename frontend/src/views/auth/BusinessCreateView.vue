<template>
    <AuthLayout>
        <template #title>Create New Business</template>

        <form class="space-y-6" @submit.prevent="createBusiness">
            <!-- Error Message -->
            <div v-if="error" class="rounded-lg bg-red-50 p-4 border border-red-100 flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
                </div>
            </div>

            <div>
                <label for="gstin" class="block text-sm font-medium text-gray-700">GSTIN / Tax ID</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input id="gstin" name="gstin" type="text" v-model="form.gstin" @input="handleGstinInput"
                        class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="29AABCG1017F1Z2" maxlength="15" />
                    <button type="button" @click="fetchGstDetails"
                        :disabled="isFetching || !form.gstin || form.gstin.length !== 15"
                        class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg v-if="isFetching" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Fetch
                    </button>
                </div>
                <p v-if="gstError" class="mt-1 text-xs text-red-600">{{ gstError }}</p>
                <p class="mt-1 text-xs text-gray-500">Enter GSTIN to auto-fill business details.</p>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Business Name</label>
                <div class="mt-1">
                    <input id="name" name="name" type="text" required v-model="form.name"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="My Great Company" />
                </div>
            </div>

            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                <div class="mt-1">
                    <select id="currency" name="currency" v-model="form.currency"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="INR">INR - Indian Rupee</option>
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <div class="mt-1">
                    <textarea id="address" name="address" v-model="form.address" rows="3"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Business Address"></textarea>
                </div>
            </div>

            <div>
                <button type="submit" :disabled="loading"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                    <span v-if="loading">Creating...</span>
                    <span v-else>Create Business</span>
                </button>
            </div>

            <div class="text-center">
                <router-link to="/businesses" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </router-link>
            </div>
        </form>
    </AuthLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import client from '../../api/client'
import AuthLayout from '../../layouts/AuthLayout.vue'

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
        const res = await client.get(`/gst-lookup/${form.gstin}`) // Ensure /api prefix if not redundant with base URL
        const data = res.data

        if (data) {
            form.name = data.trade_name || data.legal_name || ''

            // Construct detailed address
            let addrParts = []
            const fullAddr = data.full_address_details || {}

            if (Object.keys(fullAddr).length > 0) {
                if (fullAddr.floor_no) addrParts.push(fullAddr.floor_no)
                if (fullAddr.building_no) addrParts.push(fullAddr.building_no)
                if (fullAddr.building_name) addrParts.push(fullAddr.building_name)
                if (fullAddr.street) addrParts.push(fullAddr.street)
                if (fullAddr.location) addrParts.push(fullAddr.location)
                if (fullAddr.city) addrParts.push(fullAddr.city)
                if (fullAddr.state) addrParts.push(fullAddr.state)
                if (fullAddr.pincode) addrParts.push(fullAddr.pincode)
            } else if (data.address) {
                addrParts.push(data.address)
            }

            if (addrParts.length > 0) {
                form.address = addrParts.join('\n')
            }

            // Auto-extract PAN from GSTIN (chars 3-12)
            if (form.gstin.length === 15) {
                form.pan = form.gstin.substring(2, 12)
            }
        }
    } catch (e: any) {
        console.error('GST Lookup failed', e)
        gstError.value = e.response?.data?.message || 'Failed to fetch GST details. Please try again.'
    } finally {
        isFetching.value = false
    }
}

const createBusiness = async () => {
    loading.value = true
    error.value = ''

    try {
        const res = await client.post('/businesses', form)
        const newBusiness = res.data

        // Refresh businesses in store
        await authStore.fetchBusinesses()

        // Find the full business object with pivot data from the store
        const fullBusiness = authStore.userBusinesses.find((b: any) => b.id === newBusiness.id)

        if (fullBusiness) {
            authStore.setActiveBusiness(fullBusiness)
        } else {
            // Fallback
            authStore.setActiveBusiness(newBusiness)
        }

        // Redirect to Dashboard
        router.push('/')
    } catch (e: any) {
        console.error(e)
        error.value = e.response?.data?.message || 'Failed to create business.'
    } finally {
        loading.value = false
    }
}
</script>
