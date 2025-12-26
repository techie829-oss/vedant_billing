<template>
    <AppLayout>
        <!-- Header Summary Cards -->
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <!-- Total In -->
            <div
                class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5 flex flex-col group hover:shadow-md transition-shadow">
                <dt class="text-sm font-medium text-gray-500 truncate">Total In (Credit)</dt>
                <dd class="mt-2 text-3xl font-bold text-green-600">₹{{ abbreviateNumber(summary.total_in) }}</dd>
                <div class="mt-auto pt-2 text-xs text-green-600/80 font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Income from Sales
                </div>
            </div>

            <!-- Total Out -->
            <div
                class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5 flex flex-col group hover:shadow-md transition-shadow">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Out (Debit)</dt>
                <dd class="mt-2 text-3xl font-bold text-red-600">₹{{ abbreviateNumber(summary.total_out) }}</dd>
                <div class="mt-auto pt-2 text-xs text-red-600/80 font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                    Expenses
                </div>
            </div>

            <!-- Balance -->
            <div
                class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5 flex flex-col group hover:shadow-md transition-shadow">
                <dt class="text-sm font-medium text-gray-500 truncate">Net Balance</dt>
                <dd class="mt-2 text-3xl font-bold" :class="summary.balance >= 0 ? 'text-indigo-600' : 'text-red-500'">
                    ₹{{ abbreviateNumber(summary.balance) }}
                </dd>
                <div class="mt-auto pt-2 text-xs font-medium text-gray-400">
                    Current Cash on Hand
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cashbook</h1>
                <p class="text-sm text-gray-500 mt-1">Track your daily income and expenses.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-3">
                <router-link to="/invoices/create"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Income
                </router-link>

                <button @click="openExpenseModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    Add Expense
                </button>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Details
                            </th>
                            <th scope="col"
                                class="px-3 py-3.5 text-right text-sm font-semibold text-red-600 bg-red-50/50">Out (Dr)
                            </th>
                            <th scope="col"
                                class="px-3 py-3.5 text-right text-sm font-semibold text-green-600 bg-green-50/50">In
                                (Cr)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="loading && entries.length === 0">
                            <td colspan="4" class="text-center py-4 text-gray-500">Loading ledger...</td>
                        </tr>
                        <tr v-else-if="entries.length === 0">
                            <td colspan="4" class="text-center py-4 text-gray-500">No transactions found.</td>
                        </tr>
                        <tr v-for="entry in entries" :key="entry.id + entry.type"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-6">
                                {{ formatDate(entry.date) }}
                                <div class="text-[10px] text-gray-400 font-normal">
                                    {{ new Date(entry.date).toLocaleTimeString([], {
                                        hour: '2-digit', minute: '2-digit'
                                    })
                                    }}
                                </div>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ entry.title || (entry.type === 'IN' ? 'Payment' : 'Expense')
                                    }}</div>
                                <div class="text-xs text-gray-500" v-if="entry.description">{{ entry.description }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5" v-if="entry.payment_method">{{
                                    entry.payment_method }}</div>
                            </td>
                            <!-- Debit Column -->
                            <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-bold bg-red-50/10"
                                :class="entry.type === 'OUT' ? 'text-red-600' : 'text-gray-200'">
                                {{ entry.type === 'OUT' ? '₹' + entry.amount : '-' }}
                            </td>
                            <!-- Credit Column -->
                            <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-bold bg-green-50/10"
                                :class="entry.type === 'IN' ? 'text-green-600' : 'text-gray-200'">
                                {{ entry.type === 'IN' ? '₹' + entry.amount : '-' }}
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

        <!-- Add Expense Modal -->
        <Modal :show="showExpenseModal" title="Add Expense" @close="closeExpenseModal">
            <div class="space-y-4">
                <div>
                    <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount</label>
                    <div class="relative mt-2 rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" name="amount" id="amount" v-model.number="form.amount"
                            class="block w-full rounded-md border-0 py-2 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            placeholder="0.00" aria-describedby="price-currency">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 sm:text-sm" id="price-currency">INR</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium leading-6 text-gray-900">Category</label>
                    <div class="relative mt-2">
                        <select id="category" v-model="form.category"
                            class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option>Rent</option>
                            <option>Food</option>
                            <option>Travel</option>
                            <option>Utilities</option>
                            <option>Salary</option>
                            <option>Office Supplies</option>
                            <option>Other</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium leading-6 text-gray-900">Date</label>
                    <div class="mt-2">
                        <input type="date" id="date" v-model="form.date"
                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Details /
                        Notes</label>
                    <div class="mt-2">
                        <textarea id="description" v-model="form.description" rows="3"
                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                </div>
            </div>

            <template #footer>
                <button type="button"
                    class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                    @click="saveExpense">Save Expense</button>
                <button type="button"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                    @click="closeExpenseModal">Cancel</button>
            </template>
        </Modal>

    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, ref, reactive } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { useCashbookStore } from '../../stores/cashbook'
import { useExpenseStore } from '../../stores/expense'
import { storeToRefs } from 'pinia'
import Modal from '../../components/Modal.vue'

const cashbookStore = useCashbookStore()
const expenseStore = useExpenseStore()
const { entries, summary, loading, pagination } = storeToRefs(cashbookStore)

const showExpenseModal = ref(false)
const form = reactive({
    amount: 0,
    category: 'Other',
    date: new Date().toISOString().split('T')[0],
    description: '',
    payment_method: 'Cash'
})

const openExpenseModal = () => {
    form.amount = 0
    form.category = 'Other'
    form.date = new Date().toISOString().split('T')[0]
    form.description = ''
    showExpenseModal.value = true
}

const closeExpenseModal = () => {
    showExpenseModal.value = false
}

const saveExpense = async () => {
    if (!form.amount || !form.date) return

    try {
        await expenseStore.createExpense(form)
        showExpenseModal.value = false
        refresh() // Refresh cashbook list
    } catch (e) {
        alert('Failed to save expense')
    }
}

const refresh = () => {
    cashbookStore.fetchCashbook({ page: pagination.value.current_page })
}

const changePage = (page: number) => {
    if (page < 1 || page > pagination.value.last_page) return
    cashbookStore.fetchCashbook({ page })
}

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString() // Or meaningful format
}

const abbreviateNumber = (value: number) => {
    return new Intl.NumberFormat('en-IN', {
        maximumSignificantDigits: 10, // Full number roughly or just formatted
    }).format(value);
}

onMounted(() => {
    refresh()
})
</script>
