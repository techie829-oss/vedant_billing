<template>
    <AuthLayout>
        <template #title>Verify your email</template>

        <div class="text-center flex flex-col gap-6">
            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-50 text-primary mx-auto">
                <i class="pi pi-envelope text-3xl"></i>
            </div>
            
            <p class="text-gray-600">
                We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.
            </p>

            <div class="p-fluid">
                <Button label="Resend Verification Email" icon="pi pi-refresh" severity="secondary" outlined :loading="loading" @click="resendEmail" />
            </div>

            <Message v-if="message" :severity="message.includes('Failed') ? 'error' : 'success'" size="small">
                {{ message }}
            </Message>

            <div class="pt-4 border-t border-gray-100">
                <Button label="Back to Login" text severity="secondary" @click="$router.push('/login')" />
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import client from '../../api/client'
import AuthLayout from '../../layouts/AuthLayout.vue'

// PrimeVue
import Button from 'primevue/button'
import Message from 'primevue/message'

const loading = ref(false)
const message = ref('')

const resendEmail = async () => {
    loading.value = true
    message.value = ''
    try {
        await client.post('/email/resend')
        message.value = 'A new verification link has been sent!'
    } catch (error) {
        message.value = 'Failed to resend email. Please try again later.'
    } finally {
        loading.value = false
    }
}
</script>
