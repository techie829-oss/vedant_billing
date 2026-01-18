<template>
  <AuthLayout>
    <template #title>Select Business</template>

    <div v-if="auth.userBusinesses.length === 0" class="text-center py-8">
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
        <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
      </div>
      <p class="text-gray-500 mb-6">No businesses found associated with your account.</p>

      <router-link to="/billing"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clip-rule="evenodd" />
        </svg>
        Create New Business
      </router-link>
    </div>

    <div v-else class="space-y-4">
      <p class="text-sm text-gray-500 text-center mb-6">Choose which business you want to manage.</p>

      <div class="grid gap-4">
        <button v-for="business in auth.userBusinesses" :key="business.id" @click="selectBusiness(business)"
          class="group relative flex items-center p-4 border border-gray-200 rounded-xl hover:border-indigo-500 hover:ring-1 hover:ring-indigo-500 hover:shadow-md transition-all duration-200 bg-white text-left w-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <div
            class="flex-shrink-0 h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-600 transition-colors duration-200">
            <span class="text-indigo-600 font-bold text-lg group-hover:text-white transition-colors duration-200">
              {{ business.name.charAt(0).toUpperCase() }}
            </span>
          </div>

          <div class="ml-4 flex-1">
            <h3 class="text-base font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
              {{ business.name }}
            </h3>
            <p class="text-xs text-gray-500 flex items-center">
              <span class="inline-block w-2 h-2 rounded-full mr-2" :class="[
                business.status === 'active' && (!business.subscriptions?.length || business.subscriptions[0].status === 'active')
                  ? 'bg-green-400'
                  : 'bg-red-400'
              ]"></span>
              {{ business.currency }} • {{ business.status === 'active' && (!business.subscriptions?.length ||
                business.subscriptions[0].status === 'active') ? 'Active' : 'Inactive (Cancelled)' }}
            </p>
          </div>

          <div class="ml-2">
            <svg
              class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transform group-hover:translate-x-1 transition-all"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </button>
      </div>
    </div>

    <template #footer>
      <div class="text-center">
        <button @click="auth.logout()" class="text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
          Sign out
        </button>
      </div>
    </template>
  </AuthLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import AuthLayout from '../../layouts/AuthLayout.vue'

const auth = useAuthStore()
const router = useRouter()

onMounted(async () => {
  if (auth.userBusinesses.length === 0) {
    await auth.fetchBusinesses()
  }
})

const selectBusiness = (business: any) => {
  auth.setActiveBusiness(business)
  router.push('/')
}
</script>
