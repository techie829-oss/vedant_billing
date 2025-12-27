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

                        <!-- OCR Scan Receipt Section -->
                        <div class="col-span-full border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Scan Receipt</h3>
                                    <p class="mt-1 text-sm text-gray-500">Upload a receipt image to automatically
                                        extract details</p>
                                </div>
                                <button type="button" @click="triggerFileUpload" :disabled="scanning"
                                    class="inline-flex items-center gap-x-2 rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="!scanning" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                    </svg>
                                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ scanning ? 'Scanning...' : 'Scan Receipt' }}
                                </button>
                            </div>

                            <!-- Hidden file input -->
                            <input ref="fileInput" type="file" accept="image/*" @change="handleFileUpload"
                                class="hidden" />

                            <!-- Scan result preview -->
                            <div v-if="scanResult" class="mt-4 rounded-md bg-green-50 p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">Receipt scanned successfully!
                                        </h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>Extracted: ₹{{ scanResult.amount }} • {{ scanResult.merchant || 'Unknown
                                                merchant' }} • {{ scanResult.date || 'No date' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error message -->
                            <div v-if="scanError" class="mt-4 rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Scan failed</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <p>{{ scanError }}</p>
                                        </div>
                                    </div>
                                </div>
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
import axios from 'axios'

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

// OCR scan state
const scanning = ref(false)
const scanResult = ref<any>(null)
const scanError = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const fetchExpense = async () => {
    if (!id) return
    const expense = expenseStore.expenses.find(e => e.id === id)
    if (expense) {
        // Clone to avoid mutating store directly
        form.value = { ...expense }
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

// Trigger file input dialog
const triggerFileUpload = () => {
    fileInput.value?.click()
}

// Handle file upload and scan
const handleFileUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    // Validate file type
    if (!file.type.startsWith('image/')) {
        scanError.value = 'Please upload an image file (JPG, PNG, etc.)'
        return
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        scanError.value = 'Image size must be less than 5MB'
        return
    }

    // Reset state
    scanError.value = null
    scanResult.value = null
    scanning.value = true

    try {
        // Create form data
        const formData = new FormData()
        formData.append('receipt', file)

        // Call scan API
        const response = await axios.post('/api/expenses/scan', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        // Store result
        scanResult.value = response.data.data

        // Auto-populate form fields
        if (response.data.data.amount) {
            form.value.amount = parseFloat(response.data.data.amount)
        }
        if (response.data.data.date) {
            form.value.date = response.data.data.date
        }
        if (response.data.data.merchant) {
            form.value.description = response.data.data.merchant
        }
        if (response.data.data.category) {
            // Map to our category if it matches
            const categories = ['Rent', 'Salaries', 'Utilities', 'Supplies', 'Travel', 'Software', 'Marketing', 'Other']
            const matchedCategory = categories.find(c =>
                c.toLowerCase() === response.data.data.category.toLowerCase()
            )
            if (matchedCategory) {
                form.value.category = matchedCategory
            }
        }

        // Reset file input
        if (fileInput.value) {
            fileInput.value.value = ''
        }

    } catch (error: any) {
        console.error('Scan error:', error)
        scanError.value = error.response?.data?.message || 'Failed to scan receipt. Please try again.'
        scanResult.value = null
    } finally {
        scanning.value = false
    }
}

onMounted(() => {
    if (isEditing.value) {
        fetchExpense()
    }
})
</script>
