<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Vendors</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your suppliers and vendors for purchase invoices.</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <router-link to="/vendors/create"
          class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-3 sm:py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Vendor
        </router-link>
      </div>
    </div>

    <div v-if="partyStore.error" class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
      {{ partyStore.error }}
    </div>

    <div class="mt-8 flow-root">
      <div v-if="partyStore.loading" class="flex justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <EmptyState v-else-if="vendors.length === 0" title="No vendors found"
        description="Get started by adding your first vendor or supplier." action-label="Add Vendor"
        @action="$router.push('/vendors/create')" />

      <div v-else>
        <!-- Mobile Cards -->
        <div class="sm:hidden divide-y divide-gray-100 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
          <div v-for="vendor in vendors" :key="vendor.id" class="p-3 hover:bg-gray-50 flex flex-col gap-2 transition-colors">
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-bold text-[10px] flex-shrink-0">
                  {{ vendor.name.substring(0, 2).toUpperCase() }}
                </div>
                <div class="font-bold text-gray-900 text-sm truncate max-w-[60%]">{{ vendor.name }}</div>
              </div>
              <div class="font-bold text-sm text-right"
                :class="vendor.current_balance > 0 ? 'text-green-600' : (vendor.current_balance < 0 ? 'text-red-600' : 'text-gray-900')">
                ₹{{ Math.abs(vendor.current_balance).toFixed(2) }}
              </div>
            </div>
            <div class="flex justify-end gap-4 mt-1 pt-1.5 border-t border-gray-100">
              <router-link :to="`/customers/${vendor.id}/ledger`" class="text-xs font-medium text-amber-600 hover:text-amber-900">Ledger</router-link>
              <router-link :to="`/vendors/${vendor.id}/edit`" class="text-xs font-medium text-indigo-600 hover:text-indigo-900">Edit</router-link>
              <button @click="deleteVendor(vendor.id)" class="text-xs font-medium text-red-600 hover:text-red-900">Delete</button>
            </div>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden sm:block -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl bg-white">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="py-2.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                    <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-900">Contact</th>
                    <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-900">Balance</th>
                    <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="relative py-2.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="vendor in vendors" :key="vendor.id" class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="whitespace-nowrap py-2.5 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                      <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-bold text-xs mr-3">
                          {{ vendor.name.substring(0, 2).toUpperCase() }}
                        </div>
                        <div>
                          <div class="font-medium text-gray-900">{{ vendor.name }}</div>
                          <div class="text-gray-500 font-normal text-xs" v-if="vendor.gstin">GSTIN: {{ vendor.gstin }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2.5 text-sm text-gray-500">
                      <div v-if="vendor.email">{{ vendor.email }}</div>
                      <div v-if="vendor.phone">{{ vendor.phone }}</div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2.5 text-sm">
                      <div class="font-medium"
                        :class="vendor.current_balance > 0 ? 'text-green-600' : (vendor.current_balance < 0 ? 'text-red-600' : 'text-gray-900')">
                        ₹{{ Math.abs(vendor.current_balance).toFixed(2) }}
                        <span class="text-xs text-gray-400">{{ vendor.current_balance > 0 ? ' (Dr)' : (vendor.current_balance < 0 ? ' (Cr)' : '') }}</span>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2.5 text-sm text-gray-500">
                      <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                        :class="vendor.status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-600 ring-gray-500/10'">
                        {{ vendor.status }}
                      </span>
                    </td>
                    <td class="relative whitespace-nowrap py-2.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                      <router-link :to="`/customers/${vendor.id}/ledger`" class="text-amber-600 hover:text-amber-900 mr-4">Ledger</router-link>
                      <router-link :to="`/vendors/${vendor.id}/edit`" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</router-link>
                      <button @click="deleteVendor(vendor.id)" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { usePartyStore } from '../../stores/party'
import AppLayout from '../../layouts/AppLayout.vue'
import EmptyState from '../../components/EmptyState.vue'

const partyStore = usePartyStore()
const vendors = computed(() => partyStore.vendors ?? partyStore.parties?.filter((p: any) => p.party_type === 'vendor') ?? [])

const deleteVendor = async (id: string) => {
  if (!confirm('Are you sure you want to delete this vendor?')) return
  try {
    await partyStore.deleteParty(id)
  } catch (e) { }
}

onMounted(() => {
  partyStore.fetchParties({ type: 'vendor' })
})
</script>
