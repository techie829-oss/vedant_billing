<template>
    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    @click="close">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-10">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                :class="type === 'purchase' ? 'bg-green-100' : 'bg-red-100'">
                                <svg v-if="type === 'purchase'" class="h-6 w-6 text-green-600"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <svg v-else class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12H4" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ type === 'purchase' ? 'Add Stock (Purchase)' : 'Reduce Stock (Adjustment/Sale)'
                                    }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Update inventory for <strong>{{ product?.name }}</strong>.
                                    </p>

                                    <div class="mt-5 space-y-5">
                                        <!-- Quantity -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                            <input type="number" step="any" v-model.number="form.quantity"
                                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                                                placeholder="e.g 10" min="1">
                                        </div>

                                        <!-- Buy Price (Only for Purchase) -->
                                        <div v-if="type === 'purchase'">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Buy Price / Unit
                                                Cost
                                                (Optional)</label>
                                            <input type="number" step="any" v-model.number="form.unit_price"
                                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                                                placeholder="e.g 500.00">
                                            <p class="text-xs text-gray-500 mt-1.5">Updates the product's purchase price
                                                history.</p>
                                        </div>

                                        <!-- Supplier (Only for Purchase) -->
                                        <div v-if="type === 'purchase'">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier
                                                (Optional)</label>
                                            <select v-model="form.party_id"
                                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm bg-white">
                                                <option :value="null">-- Select Supplier --</option>
                                                <option v-for="party in parties" :key="party.id" :value="party.id">{{
                                                    party.name }}</option>
                                            </select>
                                        </div>

                                        <!-- Notes -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes /
                                                Reference</label>
                                            <input type="text" v-model="form.notes"
                                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                                                placeholder="e.g. PO-123 or Damaged Goods">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submit" :disabled="loading"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ loading ? 'Saving...' : 'Update Stock' }}
                        </button>
                        <button type="button" @click="close"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import client from '../../api/client'

const props = defineProps<{
    isOpen: boolean
    product: any
    type: 'purchase' | 'adjustment' // purchase = add, adjustment/sale = reduce
}>()

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const parties = ref<any[]>([])

const form = ref({
    quantity: null as number | null,
    unit_price: null as number | null,
    party_id: null as string | null,
    notes: ''
})

// Load parties (Suppliers)
const loadParties = async () => {
    try {
        const res = await client.get('/parties?type=supplier')
        parties.value = res.data.data || res.data // handle pagination if needed, assumption: returns array or paged
        if (res.data.data) parties.value = res.data.data;
    } catch (e) {
        console.error("Failed to load parties", e)
    }
}

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        // Reset form
        form.value = {
            quantity: null,
            unit_price: props.product?.purchase_price || null, // Pre-fill current buy price
            party_id: null,
            notes: ''
        }
        loadParties()
    }
})

const close = () => {
    emit('close')
}

const submit = async () => {
    if (!form.value.quantity || form.value.quantity <= 0) {
        alert('Please enter a valid quantity')
        return
    }

    loading.value = true
    try {
        // Determine type for backend
        // Props type is 'purchase' (Add) or 'adjustment' (Reduce usually, or specific)
        // If props.type is 'purchase', we send 'purchase'.
        // If props.type is 'adjustment' (clicked "Reduce Stock"), we send 'adjustment'? 
        // Wait, backend needs to know direction. 
        // Let's assume:
        // API: /inventory
        // type: purchase (add), sale (reduce), adjustment (reduce/add check notes), return (reduce/add)

        // For this Modal:
        // If "Add Stock" -> type: purchase
        // If "Reduce Stock" -> type: adjustment (with negative qty? No backend expects positive qty usually and type defines logic?)
        // Let's look at Backend Logic: "$product->increment('current_stock', $quantity);"
        // Wait, my backend logic was: "$product->increment('current_stock', $quantity);"  ALWAYS INCREMENTS!
        // I NEED TO FIX BACKEND LOGIC TO HANDLE REDUCTION.
        // OR Frontend sends negative quantity for reduction.

        // Let's send negative quantity for reduction.
        let finalQty = form.value.quantity
        let finalType = props.type

        if (props.type === 'adjustment') {
            // Logic for "Reduce Stock" button
            finalQty = -Math.abs(finalQty)
            // Backend logic uses increment, so adding negative reduces. Correct.
        }

        // API Call
        await client.post('/inventory', {
            product_id: props.product.id,
            type: finalType, // purchase or adjustment
            quantity: finalQty,
            unit_price: form.value.unit_price,
            party_id: form.value.party_id,
            notes: form.value.notes
        })

        emit('saved')
        close()
    } catch (e: any) {
        console.error('Stock update failed', e)
        alert(e.response?.data?.message || 'Failed to update stock')
    } finally {
        loading.value = false
    }
}
</script>
