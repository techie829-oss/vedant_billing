<template>
    <div v-if="isOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Billing
                                    Address Required</h3>
                                <div class="mt-2 text-sm text-gray-500">
                                    <p>Please update your business address to proceed with the subscription and invoice
                                        generation.</p>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Address</label>
                                        <textarea v-model="form.address" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Pincode</label>
                                            <input type="text" v-model="form.meta.pincode"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
                                                placeholder="000000" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">City</label>
                                            <input type="text" v-model="form.meta.city"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2" />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">State</label>
                                        <StateSelect v-model="form.meta.state" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" @click="save" :disabled="saving || !isValid"
                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50">
                            {{ saving ? 'Saving...' : 'Update & Continue' }}
                        </button>
                        <button type="button" @click="$emit('close')"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import client from '../api/client'
import StateSelect from './StateSelect.vue'
import { fetchPincodeDetails } from '../services/PincodeService'
import { useAuthStore } from '../stores/auth' // To update global state

const props = defineProps<{
    isOpen: boolean
    business: any
}>()

const emit = defineEmits(['close', 'saved'])
const authStore = useAuthStore()

const saving = ref(false)
const form = ref({
    address: '',
    meta: {
        pincode: '',
        city: '',
        state: ''
    }
})

// Initialize form when opened
watch(() => props.isOpen, (newVal) => {
    if (newVal && props.business) {
        form.value = {
            address: props.business.address || '',
            meta: {
                pincode: props.business.meta?.pincode || '',
                city: props.business.meta?.city || '',
                state: props.business.meta?.state || ''
            }
        }
    }
})

// Pincode lookup
watch(() => form.value.meta.pincode, async (newVal) => {
    if (newVal && newVal.length === 6) {
        const details = await fetchPincodeDetails(newVal)
        if (details) {
            form.value.meta.city = details.city
            form.value.meta.state = details.state
        }
    }
})

const isValid = computed(() => {
    return form.value.address && form.value.meta.city && form.value.meta.state && form.value.meta.pincode
})

const save = async () => {
    if (!props.business?.id) return
    saving.value = true
    try {
        // We update only the fields we collected, merging with existing business data structure if handled by backend
        // Or we send a partial update. Assuming backend handles partial or we need to be careful?
        // In BusinessProfileView it sends the whole form. 
        // Ideally we should fetch fresh business data first or trust props.business is reasonably fresh.

        // Construct payload. Note: The backend likely expects a structure similar to BusinessProfileView
        // which puts generic fields at root and meta fields in meta column.
        // However, to be safe, we might just want to PATCH. 
        // Let's assume standard PUT for now, but we need to ensure we don't wipe other data.
        // Actually, let's fetch the full business first to be safe, update it, and send it back.

        const { data: fullBusiness } = await client.get(`/businesses/${props.business.id}`)

        const updatedPayload = {
            ...fullBusiness,
            address: form.value.address,
            meta: {
                ...fullBusiness.meta,
                pincode: form.value.meta.pincode,
                city: form.value.meta.city,
                state: form.value.meta.state
            }
        }

        const response = await client.put(`/businesses/${props.business.id}`, updatedPayload)

        // Update store
        if (authStore.activeBusiness?.id === props.business.id) {
            // Preserve pivot data from current business before updating
            const currentPivot = authStore.activeBusiness?.pivot;
            const updatedBusiness = {
                ...response.data,
                pivot: currentPivot || response.data.pivot
            };
            authStore.setActiveBusiness(updatedBusiness)
        }

        emit('saved')
    } catch (e) {
        console.error(e)
        alert('Failed to update address.')
    } finally {
        saving.value = false
    }
}
</script>
