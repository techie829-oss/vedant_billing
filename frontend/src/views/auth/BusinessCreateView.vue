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

const form = reactive({
    name: '',
    currency: 'INR'
})

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
