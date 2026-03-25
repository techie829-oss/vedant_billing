<template>
    <Dialog :visible="isOpen" @update:visible="$emit('close')" header="Billing Address Required" modal :style="{ width: '450px' }" class="p-fluid">
        <div class="flex flex-col gap-6 py-2">
            <div class="flex items-start gap-4 p-3 bg-indigo-50 rounded-xl border border-indigo-100 text-indigo-700">
                <i class="pi pi-building text-2xl mt-1"></i>
                <p class="text-sm font-medium leading-relaxed">
                    Please update your business address to proceed with the subscription and invoice generation.
                </p>
            </div>

            <div class="flex flex-col gap-2">
                <label class="font-bold text-sm text-gray-700">Detailed Address</label>
                <Textarea v-model="form.address" rows="3" autoResize placeholder="Building, Street, Area..." />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-sm text-gray-700">Pincode</label>
                    <div class="p-inputgroup">
                        <InputText v-model="form.meta.pincode" placeholder="000000" maxlength="6" />
                        <Button icon="pi pi-search" severity="secondary" @click="lookupPincode" />
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-sm text-gray-700">City</label>
                    <InputText v-model="form.meta.city" placeholder="City Name" />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label class="font-bold text-sm text-gray-700">State</label>
                <StateSelect v-model="form.meta.state" />
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text severity="secondary" @click="$emit('close')" :disabled="saving" />
            <Button label="Update & Continue" icon="pi pi-check" @click="save" :loading="saving" :disabled="!isValid" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import client from '../api/client'
import StateSelect from './StateSelect.vue'
import { fetchPincodeDetails } from '../services/PincodeService'
import { useAuthStore } from '../stores/auth'

// PrimeVue
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'

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

const lookupPincode = async () => {
    if (form.value.meta.pincode.length === 6) {
        const details = await fetchPincodeDetails(form.value.meta.pincode)
        if (details) {
            form.value.meta.city = details.city
            form.value.meta.state = details.state
        }
    }
}

// Auto lookup on typing
watch(() => form.value.meta.pincode, (newVal) => {
    if (newVal?.length === 6) lookupPincode()
})

const isValid = computed(() => {
    return form.value.address && form.value.meta.city && form.value.meta.state && form.value.meta.pincode
})

const save = async () => {
    if (!props.business?.id) return
    saving.value = true
    try {
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

        if (authStore.activeBusiness?.id === props.business.id) {
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
