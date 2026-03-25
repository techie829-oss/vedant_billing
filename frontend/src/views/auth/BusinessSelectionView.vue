<template>
  <AuthLayout>
    <template #title>Select Business</template>

    <div v-if="auth.userBusinesses.length === 0" class="text-center py-8 flex flex-col items-center gap-4">
      <Avatar icon="pi pi-building" size="xlarge" shape="circle" class="bg-gray-100 text-gray-400" />
      <p class="text-gray-500">No businesses found associated with your account.</p>
      <Button label="Create New Business" icon="pi pi-plus" @click="router.push('/businesses/create')" />
    </div>

    <div v-else class="flex flex-col gap-4">
      <p class="text-sm text-gray-500 text-center mb-2">Choose which business you want to manage.</p>

      <div class="flex flex-col gap-3">
        <Button v-for="business in auth.userBusinesses" :key="business.id" 
            severity="secondary" outlined class="w-full text-left p-4 hover:bg-primary-50 hover:border-primary transition-all duration-200 group"
            @click="selectBusiness(business)">
            <div class="flex items-center gap-4 w-full">
                <Avatar :label="business.name.charAt(0).toUpperCase()" size="large" shape="circle" 
                    class="bg-primary-100 text-primary font-bold group-hover:bg-primary group-hover:text-white" />
                <div class="flex-1 flex flex-col">
                    <span class="font-bold text-gray-900 group-hover:text-primary">{{ business.name }}</span>
                    <div class="flex items-center gap-2 mt-1">
                        <Tag :value="isBusinessActive(business) ? 'Active' : 'Inactive'" 
                            :severity="isBusinessActive(business) ? 'success' : 'danger'" size="small" rounded />
                        <span class="text-[10px] text-gray-400 uppercase font-bold">{{ business.currency }}</span>
                    </div>
                </div>
                <i class="pi pi-chevron-right text-gray-300 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
            </div>
        </Button>
      </div>

      <Button label="Add Another Business" icon="pi pi-plus" text @click="router.push('/businesses/create')" class="mt-4" />
    </div>

    <template #footer>
      <div class="text-center">
        <Button label="Sign Out" icon="pi pi-sign-out" text severity="secondary" @click="auth.logout()" />
      </div>
    </template>
  </AuthLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import AuthLayout from '../../layouts/AuthLayout.vue'

// PrimeVue
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Tag from 'primevue/tag'

const auth = useAuthStore()
const router = useRouter()

onMounted(async () => {
  if (auth.userBusinesses.length === 0) {
    await auth.fetchBusinesses()
  }
})

const isBusinessActive = (business: any) => {
    return business.status === 'active' && (!business.subscriptions?.length || business.subscriptions[0].status === 'active')
}

const selectBusiness = (business: any) => {
  auth.setActiveBusiness(business)
  router.push('/')
}
</script>
