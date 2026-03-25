<template>
  <AuthLayout>
    <template #title>Sign in to your account</template>

    <div class="flex flex-col gap-6 p-fluid">
      <!-- Error Message -->
      <Message v-if="authStore.error" severity="error" closable @close="authStore.error = null">
        {{ authStore.error }}
      </Message>

      <div class="flex flex-col gap-2">
        <label for="email" class="font-semibold text-sm">Email Address</label>
        <InputText id="email" v-model="email" type="email" placeholder="you@example.com" autofocus />
      </div>

      <div class="flex flex-col gap-2">
        <div class="flex justify-between items-center">
            <label for="password" class="font-semibold text-sm">Password</label>
            <router-link to="/forgot-password" class="text-xs font-bold text-primary hover:underline">Forgot password?</router-link>
        </div>
        <Password id="password" v-model="password" :feedback="false" toggleMask placeholder="••••••••" />
      </div>

      <Button label="Sign In" icon="pi pi-sign-in" :loading="authStore.isLoading" @click="handleLogin" />

      <Divider align="center" class="my-4">
        <span class="text-xs text-gray-400 uppercase font-bold px-2">Or continue with</span>
      </Divider>

      <Button label="Google" icon="pi pi-google" severity="secondary" outlined @click="redirectToGoogle" />
    </div>

    <!-- Registration Modal (PrimeVue Dialog) -->
    <Dialog v-model:visible="showRegistrationModal" header="Account Not Found" :modal="true" :style="{ width: '400px' }">
        <div class="flex flex-col items-center text-center gap-4 py-4">
            <i class="pi pi-user-plus text-5xl text-primary-200"></i>
            <p class="text-gray-600">We couldn't find an account associated with that Google email. Please register first.</p>
        </div>
        <template #footer>
            <Button label="Cancel" text @click="showRegistrationModal = false" />
            <Button label="Register Account" icon="pi pi-arrow-right" @click="router.push('/register')" />
        </template>
    </Dialog>

    <template #footer>
      <div class="text-center">
        <span class="text-sm text-gray-500">Don't have an account? </span>
        <router-link to="/register" class="text-sm font-bold text-primary hover:underline">Create one here</router-link>
      </div>
    </template>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import AuthLayout from '../../layouts/AuthLayout.vue'
import client from '../../api/client'

// PrimeVue
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Message from 'primevue/message'
import Dialog from 'primevue/dialog'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const showRegistrationModal = ref(false)

onMounted(() => {
  if (route.query.error === 'not_registered') {
    showRegistrationModal.value = true
  }
})

const handleLogin = async () => {
  if (!email.value || !password.value) return
  const success = await authStore.login({
    email: email.value,
    password: password.value
  })

  if (success) {
    router.push('/businesses')
  }
}

const redirectToGoogle = () => {
    window.location.href = `${client.defaults.baseURL}/auth/google/redirect`
}
</script>
