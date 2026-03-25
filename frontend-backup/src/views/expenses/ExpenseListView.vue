<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
                <p class="text-sm text-gray-500 mt-1">Track your business expenses and spending.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                <button @click="showScanModal = true"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-3 sm:py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Scan Receipt
                </button>
                <router-link to="/expenses/create"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-3 sm:py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Expense
                </router-link>
            </div>
        </div>

        <!-- Mobile Card List -->
        <div class="sm:hidden divide-y divide-gray-100 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div v-if="loading && expenses.length === 0" class="p-4 text-center text-gray-500 text-sm">Loading
                expenses...</div>
            <div v-else-if="expenses.length === 0" class="p-4 text-center text-gray-500 text-sm">No expenses recorded.
            </div>
            <div v-for="expense in expenses" :key="expense.id"
                class="p-3 hover:bg-gray-50 flex flex-col gap-2 transition-colors">
                <div class="flex justify-between items-start">
                    <span
                        class="inline-flex items-center rounded-md bg-gray-50 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                        {{ expense.category }}
                    </span>
                    <div class="font-bold text-gray-900 text-sm">₹{{ expense.amount }}</div>
                </div>

                <div class="flex justify-between items-start">
                    <div class="text-xs text-gray-900 font-medium whitespace-none truncate max-w-[70%] pr-2">{{
                        expense.description }}</div>
                    <span class="text-[10px] text-gray-500 flex-shrink-0">{{ formatDate(expense.date) }}</span>
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5" v-if="expense.reference_no">Ref: {{ expense.reference_no
                }}</div>

                <div class="flex justify-end gap-3 mt-1 pt-1.5 border-t border-gray-100">
                    <router-link :to="`/expenses/${expense.id}/edit`"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-900 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </router-link>
                    <button @click="deleteExp(expense.id)"
                        class="text-xs font-medium text-red-600 hover:text-red-900 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="hidden sm:block bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Category
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                Description</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Reference
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount
                            </th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="loading && expenses.length === 0">
                            <td colspan="6" class="text-center py-4 text-gray-500">Loading expenses...</td>
                        </tr>
                        <tr v-else-if="expenses.length === 0">
                            <td colspan="6" class="text-center py-4 text-gray-500">No expenses recorded.</td>
                        </tr>
                        <tr v-for="expense in expenses" :key="expense.id">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                {{ formatDate(expense.date) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                    {{ expense.category }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 max-w-xs truncate">{{
                                expense.description || '-' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ expense.reference_no || '-'
                            }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">₹{{ expense.amount
                            }}</td>
                            <td
                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <router-link :to="`/expenses/${expense.id}/edit`"
                                    class="text-indigo-600 hover:text-indigo-900 mr-4" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </router-link>
                                <button @click="deleteExp(expense.id)" class="text-red-600 hover:text-red-900"
                                    title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page"
            class="mt-4 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="flex flex-1 justify-between sm:justify-end">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                    class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0 disabled:opacity-50">Previous</button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="relative ml-3 inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0 disabled:opacity-50">Next</button>
            </div>
        </div>

        <!-- Scan Receipt Modal -->
        <div v-if="showScanModal" class="relative z-50" @click.self="showScanModal = false">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <div>
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Scan Expense Receipt</h3>
                            <p class="mt-1 text-sm text-gray-500">Upload a receipt to automatically extract details</p>
                        </div>

                        <!-- File Upload -->
                        <div v-if="!scanning && !scanResult" class="mt-6">
                            <input type="file" ref="fileInput" accept="image/*" @change="handleFileSelect"
                                class="hidden">
                            <div @click="fileInput?.click()"
                                class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 cursor-pointer hover:border-indigo-500 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                        <span class="font-semibold text-indigo-600">Click to upload receipt</span>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-600">PNG, JPG up to 5MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Scanning State -->
                        <div v-if="scanning" class="mt-6 text-center py-10">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600">
                            </div>
                            <p class="mt-4 text-sm font-medium text-gray-900">Processing Receipt...</p>
                        </div>

                        <!-- Review Scan Result -->
                        <div v-if="scanResult" class="mt-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Amount</label>
                                    <input type="number" v-model="scanResult.amount"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Date</label>
                                    <input type="date" v-model="scanResult.date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500">Category</label>
                                <input type="text" v-model="scanResult.category"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500">Description</label>
                                <textarea v-model="scanResult.description" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>

                            <div class="pt-4 flex justify-end space-x-3">
                                <button @click="saveScannedExpense"
                                    class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Save Expense
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button @click="closeScanModal" type="button"
                                class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { useExpenseStore } from '../../stores/expense'
import { storeToRefs } from 'pinia'
import client from '../../api/client'

const expenseStore = useExpenseStore()
const { expenses, loading, pagination } = storeToRefs(expenseStore)

const showScanModal = ref(false)
const scanning = ref(false)
const scanResult = ref<any>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) scanReceipt(file)
}

const scanReceipt = async (file: File) => {
    scanning.value = true
    const formData = new FormData()
    formData.append('receipt', file)

    try {
        const response = await client.post('/expenses/scan', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        scanResult.value = response.data.data
    } catch (error: any) {
        alert(error.response?.data?.message || 'Scanning failed')
    } finally {
        scanning.value = false
    }
}

const saveScannedExpense = async () => {
    try {
        await client.post('/expenses', scanResult.value)
        showScanModal.value = false
        scanResult.value = null
        refresh()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Failed to save expense')
    }
}

const closeScanModal = () => {
    showScanModal.value = false
    scanResult.value = null
    scanning.value = false
}

const refresh = () => {
    expenseStore.fetchExpenses({ page: pagination.value.current_page })
}

const changePage = (page: number) => {
    if (page < 1 || page > pagination.value.last_page) return
    expenseStore.fetchExpenses({ page })
}

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString()
}

const deleteExp = async (id: string) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        await expenseStore.deleteExpense(id)
    }
}

onMounted(() => {
    refresh()
})
</script>
