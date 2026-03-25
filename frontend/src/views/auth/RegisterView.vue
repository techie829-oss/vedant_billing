<template>
    <AuthLayout>
        <template #title>Create your account</template>

        <div class="flex flex-col gap-6 p-fluid">
            <!-- Error Message -->
            <Message v-if="authStore.error" severity="error" closable @close="authStore.error = null">
                {{ authStore.error }}
            </Message>

            <div class="flex flex-col gap-2">
                <label for="name" class="font-semibold text-sm">Full Name</label>
                <InputText id="name" v-model="name" placeholder="John Doe" autofocus />
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="font-semibold text-sm">Email Address</label>
                <InputText id="email" v-model="email" type="email" placeholder="you@example.com" />
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="font-semibold text-sm">Password</label>
                <Password id="password" v-model="password" toggleMask placeholder="••••••••" />
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="font-semibold text-sm">Confirm Password</label>
                <Password id="password_confirmation" v-model="password_confirmation" :feedback="false" toggleMask placeholder="••••••••" />
            </div>

            <Button label="Register Account" icon="pi pi-user-plus" :loading="authStore.isLoading" @click="handleRegister" />

            <Divider align="center" class="my-4">
                <span class="text-xs text-gray-400 uppercase font-bold px-2">Or continue with</span>
            </Divider>

            <Button label="Google" icon="pi pi-google" severity="secondary" outlined @click="redirectToGoogle" />
        </div>

        <template #footer>
            <div class="text-center">
                <span class="text-sm text-gray-500">Already have an account? </span>
                <router-link to="/login" class="text-sm font-bold text-primary hover:underline">Sign in</router-link>
            </div>
        </template>
    </AuthLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import AuthLayout from '../../layouts/AuthLayout.vue'
import client from '../../api/client'

// PrimeVue
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Message from 'primevue/message'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')

// Load reCAPTCHA script and pre-fill data
onMounted(() => {
    const route = useRouter().currentRoute.value
    if (route.query.email) email.value = route.query.email as string
    if (route.query.name) name.value = route.query.name as string

    const script = document.createElement('script')
    script.src = `https://www.google.com/recaptcha/api.js?render=${import.meta.env.VITE_RECAPTCHA_SITE_KEY}`
    document.head.appendChild(script)
})

const handleRegister = async () => {
    if (password.value !== password_confirmation.value) {
        authStore.error = "Passwords do not match."
        return
    }

    try {
        authStore.isLoading = true
        // @ts-ignore
        const token = await new Promise<string>((resolve) => {
            // @ts-ignore
            grecaptcha.ready(() => {
                // @ts-ignore
                grecaptcha.execute(import.meta.env.VITE_RECAPTCHA_SITE_KEY, { action: 'register' }).then(resolve)
            })
        })

        const success = await authStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value,
            'g-recaptcha-response': token
        })

        if (success) {
            router.push('/verify-email')
        }
    } catch (error) {
        authStore.error = "Anti-spam check failed. Please refresh and try again."
    } finally {
        authStore.isLoading = false
    }
}

const redirectToGoogle = () => {
    window.location.href = `${client.defaults.baseURL}/auth/google/redirect`
}
</script>
