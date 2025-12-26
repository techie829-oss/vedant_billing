<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="min-w-0 flex-1">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                        {{ isEditing ? 'Edit Expense' : 'New Expense' }}
                    </h2>
                </div>
                <div class="mt-4 flex md:ml-4 md:mt-0">
                    <router-link to="/expenses"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</router-link>
                    <button @click="save" :disabled="loading"
                        class="ml-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        {{ loading ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                        <div class="sm:col-span-3">
                            <label for="date" class="block text-sm font-medium leading-6 text-gray-900">Date</label>
                            <div class="mt-2">
                                <input type="date" v-model="form.date" id="date"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount</label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" v-model.number="form.amount" id="amount" min="0" step="0.01"
                                    class="block w-full rounded-md border-0 py-1.5 pl-7 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="category"
                                class="block text-sm font-medium leading-6 text-gray-900">Category</label>
                            <div class="mt-2">
                                <select v-model="form.category" id="category"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="Rent">Rent</option>
                                    <option value="Salaries">Salaries</option>
                                    <option value="Utilities">Utilities</option>
                                    <option value="Supplies">Supplies</option>
                                    <option value="Travel">Travel</option>
                                    <option value="Software">Software</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="payment_method"
                                class="block text-sm font-medium leading-6 text-gray-900">Payment Method</label>
                            <div class="mt-2">
                                <select v-model="form.payment_method" id="payment_method"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Card">Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="reference_no"
                                class="block text-sm font-medium leading-6 text-gray-900">Reference No.</label>
                            <div class="mt-2">
                                <input type="text" v-model="form.reference_no" id="reference_no"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label for="description"
                                class="block text-sm font-medium leading-6 text-gray-900">Description / Notes</label>
                            <div class="mt-2">
                                <textarea id="description" v-model="form.description" rows="3"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import { useExpenseStore, type Expense } from '../../stores/expense'
import { storeToRefs } from 'pinia'

const route = useRoute()
const router = useRouter()
const expenseStore = useExpenseStore()
const { loading } = storeToRefs(expenseStore)

const id = route.params.id as string
const isEditing = computed(() => !!id)

const form = ref<Partial<Expense>>({
    date: new Date().toISOString().split('T')[0],
    amount: 0,
    category: 'Other',
    payment_method: 'Bank Transfer'
})

const fetchExpense = async () => {
    if (!id) return
    const expense = expenseStore.expenses.find(e => e.id === id)
    if (expense) {
        // Clone to avoid mutating store directly
        form.value = { ...expense }
    } else {
        // Ideally fetch from API if not in store, but simple implementation implies list is loaded or we load detail
        // For now, let's assume if it exists we found it, or we rely on navigation
    }
}

const save = async () => {
    if (!form.value.amount || !form.value.category || !form.value.date) {
        alert('Please fill required fields (Date, Amount, Category)')
        return
    }

    try {
        if (isEditing.value) {
            await expenseStore.updateExpense(id, form.value)
        } else {
            await expenseStore.createExpense(form.value)
        }
        router.push('/expenses')
    } catch (e) {
        console.error(e)
        alert('Failed to save expense')
    }
}

onMounted(() => {
    if (isEditing.value) {
        fetchExpense()
    }
})
</script>
