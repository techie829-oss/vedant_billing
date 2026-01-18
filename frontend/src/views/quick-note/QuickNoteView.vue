<template>
    <AppLayout>
        <div
            class="min-h-full sm:h-[calc(100vh-2rem)] flex flex-col items-center justify-center p-2 sm:p-6 bg-[#e8eaed] font-sans">

            <!-- The Paper Note Card -->
            <div class="bg-white w-full max-w-lg shadow-xl sm:shadow-2xl rounded-lg flex flex-col relative overflow-hidden h-[85dvh] sm:h-full sm:max-h-[800px] transition-all"
                id="receipt-card">

                <!-- Paper Header -->
                <div class="p-6 pb-4 border-b-2 border-dashed border-gray-200 shrink-0 z-10 bg-white">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <input v-model="noteTitle" type="text"
                                class="text-2xl font-bold text-gray-800 tracking-tight font-mono bg-transparent border-none p-0 focus:ring-0 placeholder:text-gray-300 w-full"
                                placeholder="Note Title...">
                            <div class="text-xs text-gray-500 mt-1 font-mono flex items-center gap-2">
                                <span>{{ currentDate }}</span>
                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                <span class="uppercase tracking-wider">Note #{{ Math.floor(Math.random()
                                    * 1000) + 4000
                                    }}</span>
                            </div>
                        </div>

                        <!-- Mode Stamp -->
                        <div class="flex bg-gray-100 p-0.5 rounded-lg border border-gray-200">
                            <button @click="mode = 'order_receipt'"
                                :class="['px-3 py-1 text-[11px] font-bold uppercase tracking-wide rounded-md transition-all', mode === 'order_receipt' ? 'bg-white text-gray-900 shadow-sm border border-gray-100' : 'text-gray-400 hover:text-gray-600']">
                                Order
                            </button>
                            <button @click="mode = 'hisab'"
                                :class="['px-3 py-1 text-[11px] font-bold uppercase tracking-wide rounded-md transition-all', mode === 'hisab' ? 'bg-white text-indigo-600 shadow-sm border border-gray-100' : 'text-gray-400 hover:text-gray-600']">
                                Hisab
                            </button>
                        </div>

                        <!-- History Toggle -->
                        <button @click="showHistory = !showHistory"
                            class="ml-2 p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                            title="View Saved Notes">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-5 h-5">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>


                    <!-- Column Headers -->
                    <div class="flex text-[10px] uppercase tracking-widest text-gray-400 font-bold font-mono">
                        <div class="w-8 text-center">#</div>
                        <div class="flex-1 pl-2">{{ mode === 'order_receipt' ? 'Item' : 'Entry' }}</div>
                        <div class="w-24 text-right">{{ mode === 'order_receipt' ? 'Amount' : 'Result' }}</div>
                    </div>
                </div>

                <!-- Scrollable Writing Area -->
                <div class="flex-1 overflow-y-auto bg-white relative custom-scrollbar flex flex-col"
                    @click="focusInput">
                    <!-- Background Lines -->
                    <div class="absolute inset-0 pointer-events-none"
                        style="background-image: linear-gradient(#f0f2f5 1px, transparent 1px); background-size: 100% 3rem; margin-top: 2.9rem;">
                    </div>

                    <!-- Items List -->
                    <div class="relative z-10 p-6 pt-0 min-h-full pb-32"> <!-- pb-32 for input space -->

                        <div v-for="(item, index) in items" :key="index" @click.stop="editItem(index)"
                            class="flex items-center h-[3rem] border-b border-transparent group hover:bg-gray-50 -mx-6 px-6 cursor-pointer text-sm font-mono text-gray-700">

                            <!-- Index -->
                            <div class="w-8 text-center text-gray-300 text-xs">{{ index + 1 }}</div>

                            <!-- Content -->
                            <div class="flex-1 pl-2 flex items-center overflow-hidden">
                                <template v-if="mode === 'order_receipt'">
                                    <span class="font-medium text-gray-900 truncate mr-2">{{ item.name }}</span>
                                    <span v-if="item.qty > 1"
                                        class="text-xs text-gray-400 bg-gray-100 px-1.5 rounded-sm">x{{ item.qty
                                        }}</span>
                                </template>
                                <template v-else>
                                    <!-- Chained Math Display (Calculator Tape Style) -->
                                    <template v-if="item.is_chained_math">
                                        <div class="flex items-center text-gray-800 font-bold">
                                            <span class="text-xl mr-2 text-indigo-600">{{ item.chained_op === '*' ? '×'
                                                : '÷'
                                                }}</span>
                                            <span class="text-lg">{{ item.chained_val }}</span>
                                        </div>
                                    </template>

                                    <!-- Standard Entry -->
                                    <template v-else>
                                        <div class="flex items-center">
                                            <span v-if="item.total < 0"
                                                class="text-red-500 font-bold mr-2 text-lg">−</span>
                                            <span v-else-if="index > 0"
                                                class="text-green-500 font-bold mr-2 text-lg">+</span>

                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900">{{
                                                    formatCurrency(Math.abs(item.total)) }}</span>
                                                <!-- Show breakdown if Qty > 1 or it has a name -->
                                                <div v-if="item.name || item.qty > 1"
                                                    class="text-xs text-gray-400 font-medium flex items-center gap-1">
                                                    <span v-if="item.name" class="italic text-gray-500">{{ item.name
                                                        }}</span>
                                                    <span v-if="item.name && item.qty > 1"
                                                        class="text-gray-300">•</span>
                                                    <span v-if="item.qty > 1 || item.price !== item.total"
                                                        class="font-mono text-gray-400">
                                                        {{ item.qty }} × {{ item.price }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>

                            <!-- Amount/Action -->
                            <div class="w-24 text-right font-bold relative">
                                <span v-if="mode === 'order_receipt'" class="text-gray-900">{{
                                    formatCurrency(item.total) }}</span>
                                <span v-else class="text-gray-500">{{ formatCurrency(runningTotals[index] || 0)
                                    }}</span>

                                <button @click.stop="removeItem(index)"
                                    class="absolute -right-5 top-1/2 -translate-y-1/2 p-1 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        class="w-4 h-4">
                                        <path
                                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Active Input Row (The "Pen") -->
                        <div class="flex items-center h-[3rem] -mx-6 px-6 bg-blue-50/30 relative" @click.stop>
                            <div class="w-8 text-center text-blue-300 text-xs font-bold">
                                {{ items.length + 1 }}
                            </div>

                            <!-- Hisab Operator Toggles (Inline) -->
                            <div v-if="mode === 'hisab'"
                                class="flex items-center gap-0.5 mr-3 bg-white p-0.5 rounded-md shadow-sm border border-gray-200 shrink-0">
                                <button v-for="op in ['+', '-', '*', '/']" :key="op"
                                    @click="selectedOperator = op; focusInput()" :class="['w-7 h-7 flex items-center justify-center text-sm font-bold transition-all rounded',
                                        selectedOperator === op
                                            ? 'bg-indigo-600 text-white shadow-sm'
                                            : 'text-gray-400 hover:text-indigo-600 hover:bg-gray-50']">
                                    {{ op === '*' ? '×' : op === '/' ? '÷' : op === '-' ? '−' : '+' }}
                                </button>
                            </div>

                            <form @submit.prevent="addItem" class="flex-1 flex items-center relative min-w-0">
                                <input ref="inputRef" type="text" v-model="inputLine"
                                    :placeholder="mode === 'hisab' ? 'Enter amount...' : 'Write item...'"
                                    class="flex-1 bg-transparent border-none p-0 text-base sm:text-sm font-mono text-gray-900 placeholder:text-gray-400 focus:ring-0 min-w-0"
                                    autocomplete="off" enterkeyhint="next">
                                <button type="submit" v-if="inputLine"
                                    class="text-indigo-600 hover:text-indigo-700 ml-2 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        class="w-5 h-5">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Custom Note / Remarks Section -->
                <div class="px-6 py-2 bg-gray-50 z-10">
                    <textarea v-model="noteDescription"
                        class="w-full text-sm font-mono bg-white border-gray-200 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-gray-400 p-2"
                        rows="2" placeholder="Add custom note..."></textarea>
                </div>

                <!-- Footer Total -->
                <div class="p-6 pt-2 bg-gray-50 border-t border-gray-200 z-10">
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <button @click="clearAll" v-if="items.length > 0"
                                class="text-xs font-bold text-red-500 hover:bg-red-50 px-3 py-2 rounded uppercase tracking-wide transition-colors">
                                Reset
                            </button>
                            <button @click="convertToInvoice" v-if="items.length > 0 && mode === 'order_receipt'"
                                class="text-xs font-bold text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded uppercase tracking-wide transition-colors">
                                Bill
                            </button>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Due
                            </div>
                            <div class="text-3xl font-bold text-gray-900 font-mono tracking-tighter">{{
                                formatCurrency(grandTotal) }}
                            </div>
                        </div>
                    </div>
                    <!-- Save Fab (Integrated in Footer) -->
                    <button @click="saveToBackend" :disabled="loading" v-if="items.length > 0"
                        class="w-full mt-4 flex items-center justify-center gap-2 bg-gray-900 text-white py-3 rounded-lg text-sm font-bold shadow-lg hover:bg-gray-800 active:scale-95 transition-all">
                        {{ loading ? 'Saving...' : 'Save Note' }}
                    </button>
                </div>

            </div>

            <!-- History Drawer (Left Side) -->
            <transition name="slide">
                <div v-if="showHistory"
                    class="fixed inset-y-0 left-0 z-50 w-80 bg-white shadow-2xl flex flex-col font-sans">
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h2 class="font-bold text-gray-800">Saved Notes</h2>
                        <button @click="showHistory = false" class="text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-5 h-5">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        <div v-if="loading" class="text-center py-4 text-gray-400 text-sm">Loading...</div>
                        <div v-else-if="notes.length === 0" class="text-center py-8 text-gray-400 text-sm">
                            No saved notes yet.
                        </div>
                        <div v-for="note in notes" :key="note.id"
                            class="group relative bg-white border border-gray-200 rounded-lg p-3 hover:border-indigo-300 hover:shadow-sm transition-all cursor-pointer"
                            @click="loadNote(note)">
                            <div class="flex justify-between items-start mb-2">
                                <span
                                    class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                                    {{ note.type === 'order_receipt' ? 'Order' : 'Hisab' }}
                                </span>
                                <span class="text-xs text-gray-400">{{ new Date(note.created_at).toLocaleDateString()
                                }}</span>
                            </div>
                            <h3 class="font-bold text-gray-800 text-sm mb-1 line-clamp-1">{{ note.title }}</h3>
                            <div class="flex justify-between items-end">
                                <span class="font-mono font-bold text-indigo-600">{{
                                    formatCurrency(Number(note.total_amount))
                                }}</span>
                                <button @click.stop="deleteNote(note.id)"
                                    class="text-gray-300 hover:text-red-500 p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        class="w-4 h-4">
                                        <path fill-rule="evenodd"
                                            d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Overlay for history -->
            <div v-if="showHistory" class="fixed inset-0 bg-black/20 z-40" @click="showHistory = false"></div>

            <!-- Edit Modal Overlay (Darker) -->
            <div v-if="editingIndex !== null"
                class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="cancelEdit"></div>
                <div
                    class="relative w-full sm:max-w-sm bg-white rounded-t-xl sm:rounded-xl shadow-2xl p-6 animate-in slide-in-from-bottom duration-300">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Edit Line #{{ editingIndex + 1 }}</h3>
                        <button @click="cancelEdit" class="text-gray-400 hover:text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-5 h-5">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                            <input type="text" v-model="editingItem.name" ref="editNameRef"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Qty</label>
                                <input type="number" v-model.number="editingItem.qty"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Price</label>
                                <input type="number" v-model.number="editingItem.price"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            </div>
                        </div>
                        <button @click="saveEdit"
                            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold shadow hover:bg-indigo-700 mt-2">
                            Update
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { parseLine, type ParsedItem } from '../../utils/smartParser'
import { useRouter } from 'vue-router'
import { useQuickNoteStore, type QuickNote } from '../../stores/quickNote'
import { storeToRefs } from 'pinia'

const router = useRouter()
const quickNoteStore = useQuickNoteStore()

const { loading, notes } = storeToRefs(quickNoteStore)

const mode = ref<'order_receipt' | 'hisab'>('order_receipt')
const inputLine = ref('')
const selectedOperator = ref('+') // Default operator
const orderItems = ref<ParsedItem[]>([])
const hisabItems = ref<ParsedItem[]>([])
// Note Metadata
const noteTitle = ref('')
const noteDescription = ref('')

// History State
const showHistory = ref(false)

const items = computed({
    get: () => mode.value === 'order_receipt' ? orderItems.value : hisabItems.value,
    set: (val) => {
        if (mode.value === 'order_receipt') orderItems.value = val
        else hisabItems.value = val
    }
})
const inputRef = ref<HTMLInputElement | null>(null)
const editNameRef = ref<HTMLInputElement | null>(null)

// Editing State
const editingIndex = ref<number | null>(null)
const editingItem = ref<ParsedItem>({ raw: '', qty: 0, name: '', price: 0, total: 0, is_valid: false })

// Format: "31 Dec, 2025"
const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })
})

const grandTotal = computed(() => {
    return items.value.reduce((sum, item) => sum + item.total, 0)
})

// Calculate running totals for display
const runningTotals = computed(() => {
    let sum = 0;
    return items.value.map(item => {
        sum += item.total;
        return sum;
    });
})

const focusInput = () => {
    // Small delay to ensure click events propagate if needed
    setTimeout(() => {
        if (inputRef.value) inputRef.value.focus()
    }, 50)
}

const addItem = () => {
    let rawInput = inputLine.value.trim()
    if (!rawInput) return

    // Apply Logic based on selectedOperator if strict parsing is needed
    // Logic:
    // '-': Prepend '-' if not already present
    // '*' or '/': If user types "5 500", treat as "5 * 500" or "5 / 500"

    if (mode.value === 'hisab') {
        const isSimpleNumber = /^(\d+(\.\d+)?)$/.test(rawInput);

        if (selectedOperator.value === '-') {
            // If it doesn't start with - or +, prepend -
            if (!/^[+-]/.test(rawInput)) {
                rawInput = `- ${rawInput}`
            }
        } else if (selectedOperator.value === '*' || selectedOperator.value === '/') {
            if (isSimpleNumber) {
                // Case 1: "2" -> "* 2" (Chained Math)
                rawInput = `${selectedOperator.value} ${rawInput}`
            } else if (!/[*x\/]/.test(rawInput) && /^\d+(\.\d+)?\s+\d+(\.\d+)?/.test(rawInput)) {
                // Case 2: "5 500" -> "5 * 500" (Explicit Math)
                rawInput = rawInput.replace(/\s+/, ` ${selectedOperator.value} `)
            }
        }
    }

    const parsed = parseLine(rawInput)

    // Handle Percentage Calculation relative to current Grand Total
    if (parsed.is_percentage && parsed.percentage_val !== undefined) {
        const currentTotal = grandTotal.value;
        const calcAmount = currentTotal * (Math.abs(parsed.percentage_val) / 100);

        // Update the item with concrete values
        parsed.price = calcAmount;
        // Total is signed
        parsed.total = parsed.percentage_val < 0 ? -calcAmount : calcAmount;

        // Refine the name if generic
        if (parsed.name === 'Discount' || parsed.name === 'Tax/Charge' || !parsed.name) {
            // Only update name if it wasn't a specific text input like "Special Disc 5%"
            if (parsed.name === 'Discount' || parsed.name === 'Tax/Charge' || parsed.name === '') {
                parsed.name = `${Math.abs(parsed.percentage_val)}%`;
            }
        }
    }

    // Handle Chained Math (* 2, / 2) relative to current Grand Total
    if (parsed.is_chained_math && parsed.chained_val !== undefined) {
        const currentTotal = grandTotal.value;
        let diff = 0;

        if (parsed.chained_op === '*') {
            // Target = Current * Val.  Diff = Target - Current = Current * (Val - 1)
            // Example: 100 * 2 = 200. Diff = 100.
            diff = currentTotal * (parsed.chained_val - 1);
            parsed.name = `× ${parsed.chained_val}`;
        } else if (parsed.chained_op === '/') {
            // Target = Current / Val. Diff = Target - Current = Current * (1/Val - 1)
            if (parsed.chained_val !== 0) {
                const target = currentTotal / parsed.chained_val;
                diff = target - currentTotal;
                parsed.name = `÷ ${parsed.chained_val}`;
            }
        }

        parsed.price = Math.abs(diff);
        parsed.total = diff;
    }

    items.value.push(parsed)
    inputLine.value = ''

    // Auto-scroll to bottom of paper
    nextTick(() => {
        const paper = document.getElementById('receipt-paper')?.parentElement
        if (paper) paper.scrollTop = paper.scrollHeight
        focusInput()
    })
}

const removeItem = (index: number) => {
    items.value.splice(index, 1)
}

const clearAll = () => {
    if (confirm('Clear entire note?')) {
        items.value = []
    }
}

const editItem = (index: number) => {
    const item = items.value[index]
    if (!item) return;

    editingIndex.value = index
    // Clone to avoid reactive immediate updates
    editingItem.value = { ...item }

    nextTick(() => {
        if (editNameRef.value) editNameRef.value.focus()
    })
}

const saveEdit = () => {
    if (editingIndex.value !== null) {
        // Recalculate total
        editingItem.value.total = editingItem.value.qty * editingItem.value.price
        items.value[editingIndex.value] = { ...editingItem.value }
        cancelEdit()
    }
}

const cancelEdit = () => {
    editingIndex.value = null
}

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val)
}

// Actions
const convertToInvoice = () => {
    if (items.value.length === 0) return

    // We need to pass data to the InvoiceForm. 
    // Since we don't have a sophisticated "DraftStore" yet, we can use the invoiceStore to set a "pending items" state
    // Or just pass via query params if small, or simpler: push to store.

    // Assuming invoiceStore has a method or we can patch the state directly?
    // Let's use localStorage for a quick hand-off to avoid complex store mutations blindly.
    const invoiceData = {
        items: items.value.map(i => ({
            name: i.name,
            quantity: i.qty,
            unit_price: i.price,
            tax_rate: 0, // Default 0 for rough notes
            discount: 0
        })),
        notes: noteDescription.value // Pass description to invoice notes
    }

    // Just a simple hack: Store in session/local storage and read in QuotationForm
    localStorage.setItem('pending_invoice_items', JSON.stringify(invoiceData))
    router.push({ name: 'invoice-create', query: { from_quick_note: 'true' } })
}

const saveToBackend = async () => {
    if (items.value.length === 0) return

    try {
        await quickNoteStore.saveNote({
            type: mode.value,
            title: noteTitle.value || 'Untitled Note',
            description: noteDescription.value,
            content: items.value,
            total_amount: grandTotal.value
        })
        alert('Note saved successfully!')
        // Refresh list
        quickNoteStore.fetchNotes()
    } catch (e) {
        alert('Failed to save note. Please ensure you are online.')
    }
}

// History Actions
const loadNote = (note: QuickNote) => {
    if (items.value.length > 0) {
        if (!confirm('Current note will be cleared. Load saved note?')) return
    }
    mode.value = note.type
    noteTitle.value = note.title
    noteDescription.value = note.description || ''
    // Deep copy content to avoid reference issues
    // Ensure content is parsed correctly if backend sends plain object
    items.value = JSON.parse(JSON.stringify(note.content))
    showHistory.value = false
}

const deleteNote = async (id: string) => {
    if (!confirm('Delete this saved note?')) return
    await quickNoteStore.deleteNote(id)
}

// @ts-ignore
const downloadImage = () => {
    // Placeholder: In a real app, use html2canvas or similar.
    alert("In a full implementation, this button would generate a PNG image of the note above to share on WhatsApp.")
}

onMounted(() => {
    if (inputRef.value) inputRef.value.focus()
    quickNoteStore.fetchNotes()
})

</script>

<style scoped>
/* Add a font that looks like a receipt/mono if possible */
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap');

#receipt-paper {
    font-family: 'Space Mono', monospace;
}

/* Custom scrollbar for the paper container */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 20px;
}
</style>
