<template>
    <AuthLayout>
        <template #title>Reset your password</template>

        <div class="text-center text-sm text-gray-600 space-y-4">
            <p>Please contact support or the administrator to reset your password for now.</p>

            <div class="mt-6">
                <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                    &larr; Back to login
                </router-link>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '../../layouts/AuthLayout.vue'
</script>
