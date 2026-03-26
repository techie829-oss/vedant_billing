<template>
    <Dialog :visible="isOpen" @update:visible="close" 
        :header="type === 'purchase' ? 'Add Stock (Purchase)' : 'Reduce Stock (Adjustment/Sale)'" 
        modal :style="{ width: '450px' }" class="p-fluid">
        
        <div class="flex flex-col gap-6 py-2">
            <div class="flex items-start gap-4 p-3 rounded-xl border" 
                :class="type === 'purchase' ? 'bg-green-50 border-green-100 text-green-700' : 'bg-red-50 border-red-100 text-red-700'">
                <i :class="['pi text-2xl mt-1', type === 'purchase' ? 'pi-plus-circle' : 'pi-minus-circle']"></i>
                <div class="flex flex-col">
                    <span class="font-bold text-sm">Updating Inventory</span>
                    <span class="text-xs opacity-80">{{ product?.name }} (Current: {{ product?.current_stock }} {{ product?.unit }})</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-sm text-gray-700">Quantity *</label>
                    <InputNumber v-model="form.quantity" :min="0.01" placeholder="0.00" autofocus />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-sm text-gray-700">Unit</label>
                    <Select v-model="form.unit" :options="unitOptions" optionLabel="label" optionValue="value" />
                </div>
            </div>

            <!-- Buy Price (Only for Purchase) -->
            <div v-if="type === 'purchase'" class="flex flex-col gap-2">
                <label class="font-bold text-sm text-gray-700">Buy Price / Unit Cost</label>
                <InputNumber v-model="form.unit_price" mode="currency" currency="INR" locale="en-IN" placeholder="0.00" />
                <small class="text-gray-500 italic">Updates the product's purchase price history.</small>
            </div>

            <!-- Supplier (Only for Purchase) -->
            <div v-if="type === 'purchase'" class="flex flex-col gap-2">
                <label class="font-bold text-sm text-gray-700">Supplier</label>
                <Select v-model="form.party_id" :options="parties" optionLabel="name" optionValue="id" 
                    filter placeholder="Select Supplier" showClear />
            </div>

            <div class="flex flex-col gap-2">
                <label class="font-bold text-sm text-gray-700">Notes / Reference</label>
                <InputText v-model="form.notes" placeholder="e.g. PO-123 or Damaged Goods" />
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text severity="secondary" @click="close" :disabled="loading" />
            <Button :label="loading ? 'Updating...' : 'Update Stock'" 
                :icon="loading ? 'pi pi-spin pi-spinner' : 'pi pi-check'" 
                :severity="type === 'purchase' ? 'success' : 'danger'"
                @click="submit" :loading="loading" :disabled="!form.quantity" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import client from '../../api/client'

// PrimeVue
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'

const props = defineProps<{
    isOpen: boolean
    product: any
    type: 'purchase' | 'adjustment'
}>()

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const parties = ref<any[]>([])

const form = ref({
    quantity: null as number | null,
    unit: '' as string,
    unit_price: null as number | null,
    party_id: null as string | null,
    notes: ''
})

const unitOptions = computed(() => {
    const options = [{ label: `${props.product?.unit} (Base)`, value: props.product?.unit }]
    if (props.product?.secondary_unit) {
        options.push({ label: props.product.secondary_unit, value: props.product.secondary_unit })
    }
    return options
})

// Load parties (Suppliers)
const loadParties = async () => {
    try {
        const res = await client.get('/parties?type=supplier')
        parties.value = res.data.data || res.data
    } catch (e) {
        console.error("Failed to load parties", e)
    }
}

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        // Reset form
        form.value = {
            quantity: null,
            unit: props.product?.unit || '',
            unit_price: props.product?.purchase_price || null,
            party_id: null,
            notes: ''
        }
        if (props.type === 'purchase') {
            loadParties()
        }
    }
})

const close = () => {
    emit('close')
}

const submit = async () => {
    if (!form.value.quantity || form.value.quantity <= 0) return

    loading.value = true
    try {
        let finalQty = form.value.quantity
        let conversionFactor = 1.00

        if (props.product?.secondary_unit && form.value.unit === props.product.secondary_unit) {
            conversionFactor = 1 / (Number(props.product.conversion_factor) || 1.00)
        }

        const baseQty = (finalQty || 0) * conversionFactor

        if (props.type === 'adjustment') {
            finalQty = -Math.abs(baseQty)
        } else {
            finalQty = baseQty
        }

        await client.post('/inventory', {
            product_id: props.product.id,
            type: props.type,
            quantity: finalQty,
            unit: form.value.unit,
            conversion_factor: conversionFactor,
            unit_price: form.value.unit_price,
            party_id: form.value.party_id,
            notes: form.value.notes
        })

        emit('saved')
        close()
    } catch (e: any) {
        alert(e.response?.data?.message || 'Failed to update stock')
    } finally {
        loading.value = false
    }
}
</script>
