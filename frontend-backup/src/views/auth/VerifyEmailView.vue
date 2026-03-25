<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900">
                Verify your email
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                We've sent a verification link to your email address using the backend queue.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 text-center">
                <p class="text-gray-700 mb-6">
                    Please check your inbox and click the link to verify your account. If you don't see it, check your
                    spam folder.
                </p>

                <button @click="resendEmail" :disabled="loading"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors">
                    <span v-if="loading">Sending...</span>
                    <span v-else>Resend Verification Email</span>
                </button>

                <p v-if="message" class="mt-4 text-sm text-green-600 font-medium animate-fade-in">
                    {{ message }}
                </p>

                <div class="mt-6 border-t pt-4">
                    <a href="/login" class="text-sm font-medium text-gray-500 hover:text-gray-900">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import client from '../../api/client'

const loading = ref(false)
const message = ref('')

const resendEmail = async () => {
    loading.value = true
    message.value = ''
    try {
        await client.post('/email/resend')
        message.value = 'A new verification link has been sent!'
    } catch (error) {
        console.error(error)
        message.value = 'Failed to resend email. Please try again later.'
    } finally {
        loading.value = false
    }
}
</script>
