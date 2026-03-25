<template>
    <AuthLayout>
        <template #title>Reset your password</template>

        <div class="text-center flex flex-col gap-6">
            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-orange-50 text-orange-600 mx-auto">
                <i class="pi pi-lock text-3xl"></i>
            </div>

            <p class="text-gray-600 leading-relaxed">
                Self-service password reset is currently being upgraded. Please contact your system administrator or support team to reset your access.
            </p>

            <div class="pt-4 border-t border-gray-100">
                <Button label="Back to Login" text severity="secondary" @click="$router.push('/login')" />
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '../../layouts/AuthLayout.vue'
import Button from 'primevue/button'
</script>
