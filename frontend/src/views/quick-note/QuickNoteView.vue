<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Rough Bill (Kaccha)</h1>
                    <p class="text-gray-500 mt-1 uppercase tracking-wider text-xs font-semibold">Fast drafting for quick quotes and hisab</p>
                </div>
                <div class="flex gap-2">
                    <Button v-if="items.length > 0" label="Clear All" icon="pi pi-trash" severity="danger" text @click="clearAll" />
                    <Button v-if="items.length > 0" label="Save Note" icon="pi pi-save" :loading="loading" @click="saveToBackend" />
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column: The Paper Note -->
                <div class="col-span-12 lg:col-span-8 flex flex-col gap-4">
                    <Card class="border-none shadow-xl relative overflow-hidden paper-note-card">
                        <template #header>
                            <div class="p-6 pb-4 border-b-2 border-dashed border-gray-200 bg-white sticky top-0 z-20">
                                <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
                                    <div class="flex-1 space-y-3">
                                        <InputText v-model="noteTitle" placeholder="Bill Title..." 
                                            class="text-3xl font-black border-none bg-transparent p-0 focus:ring-0 placeholder:text-gray-200" />
                                        
                                        <div class="flex flex-col md:flex-row gap-3">
                                            <InputGroup class="flex-1">
                                                <InputGroupAddon class="bg-transparent border-none pl-0 text-gray-400"><i class="pi pi-user"></i></InputGroupAddon>
                                                <InputText v-model="customerName" placeholder="Customer Name" class="border-none border-b border-gray-100 rounded-none focus:border-primary" />
                                            </InputGroup>
                                            <InputGroup class="flex-1">
                                                <InputGroupAddon class="bg-transparent border-none pl-0 text-gray-400"><i class="pi pi-mobile"></i></InputGroupAddon>
                                                <InputText v-model="customerMobile" placeholder="Phone" class="border-none border-b border-gray-100 rounded-none focus:border-primary" />
                                            </InputGroup>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-2">
                                        <SelectButton v-model="mode" :options="modeOptions" optionLabel="label" optionValue="value" class="mode-toggle" />
                                        <Tag :value="currentDate" severity="secondary" rounded />
                                    </div>
                                </div>

                                <div class="flex text-[10px] font-black uppercase tracking-widest text-gray-400 px-2">
                                    <div class="w-10">#</div>
                                    <div class="flex-1">{{ mode === 'order_receipt' ? 'Item / Description' : 'Entry Breakdown' }}</div>
                                    <div class="w-32 text-right">{{ mode === 'order_receipt' ? 'Amount' : 'Running Total' }}</div>
                                </div>
                            </div>
                        </template>

                        <template #content>
                            <div class="paper-writing-area relative min-h-[400px]">
                                <!-- Ledger Lines -->
                                <div class="absolute inset-0 pointer-events-none ledger-lines"></div>

                                <div class="relative z-10">
                                    <!-- Items List -->
                                    <div v-for="(item, index) in items" :key="index" 
                                        class="flex items-center h-[3rem] px-2 hover:bg-primary-50/30 cursor-pointer group transition-colors font-mono"
                                        @click="editItem(index)">
                                        
                                        <div class="w-10 text-center text-gray-300 text-xs">{{ index + 1 }}</div>
                                        
                                        <div class="flex-1 flex items-center min-w-0">
                                            <template v-if="mode === 'order_receipt'">
                                                <span class="font-bold text-gray-900 truncate">{{ item.name }}</span>
                                                <span v-if="item.qty > 1" class="ml-2 text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-500 font-bold">x{{ item.qty }}</span>
                                            </template>
                                            <template v-else>
                                                <div class="flex items-center">
                                                    <i :class="item.total < 0 ? 'pi pi-minus-circle text-red-500' : 'pi pi-plus-circle text-green-500'" class="mr-2 text-xs"></i>
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-gray-900">{{ formatCurrency(Math.abs(item.total)) }}</span>
                                                        <span v-if="item.name" class="text-[10px] text-gray-400 uppercase leading-none">{{ item.name }}</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="w-32 text-right font-black text-gray-900 pr-2">
                                            {{ formatCurrency(mode === 'order_receipt' ? item.total : (runningTotals[index] || 0)) }}
                                            <Button icon="pi pi-times" severity="danger" text rounded size="small" 
                                                class="absolute -right-2 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity" 
                                                @click.stop="removeItem(index)" />
                                        </div>
                                    </div>

                                    <!-- The Input "Pen" -->
                                    <div class="flex items-center h-[3rem] px-2 bg-blue-50/50 border-y border-blue-100/50">
                                        <div class="w-10 text-center text-blue-300 text-xs font-bold">{{ items.length + 1 }}</div>
                                        
                                        <div v-if="mode === 'hisab'" class="flex gap-1 mr-3">
                                            <Button v-for="op in ['+', '-', '*', '/']" :key="op" 
                                                :label="op === '*' ? '×' : op === '/' ? '÷' : op"
                                                size="small" :severity="selectedOperator === op ? 'primary' : 'secondary'"
                                                text raised class="w-8 h-8 p-0 font-black"
                                                @click="selectedOperator = op; focusInput()" />
                                        </div>

                                        <form @submit.prevent="addItem" class="flex-1 flex items-center">
                                            <InputText ref="inputRef" v-model="inputLine" 
                                                :placeholder="mode === 'hisab' ? 'Enter amount (e.g. 500)...' : 'Type item (e.g. 2 Milk 50)...'"
                                                class="border-none bg-transparent font-mono focus:ring-0" 
                                                autocomplete="off" />
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template #footer>
                            <div class="p-6 bg-gray-50 border-t border-gray-200">
                                <div class="flex flex-col md:flex-row justify-between gap-6 items-end">
                                    <div class="w-full md:w-1/2 space-y-2">
                                        <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Remarks / Terms</label>
                                        <Textarea v-model="noteDescription" autoResize rows="2" placeholder="Add custom notes..." class="bg-white" />
                                    </div>
                                    
                                    <div class="text-right">
                                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Estimated Total Due</span>
                                        <div class="text-5xl font-black text-gray-900 font-mono tracking-tighter">{{ formatCurrency(grandTotal) }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-8">
                                    <Button label="Print Slip" icon="pi pi-print" severity="secondary" outlined size="large" @click="printReceipt" />
                                    <Button label="WhatsApp" icon="pi pi-whatsapp" severity="success" outlined size="large" @click="shareToWhatsApp" />
                                    <Button label="Save Rough Note" icon="pi pi-check-circle" size="large" @click="saveToBackend" :loading="loading" />
                                </div>
                                
                                <div v-if="mode === 'order_receipt'" class="mt-4 text-center">
                                    <Button label="Convert to Tax Invoice" icon="pi pi-arrow-right" text severity="secondary" @click="convertToInvoice" />
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Right Column: Recent Notes Sidebar -->
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-4">
                    <Card class="border-none shadow-sm h-[600px] flex flex-col">
                        <template #title>
                            <div class="flex items-center justify-between">
                                <span>Recent Notes</span>
                                <IconField class="w-40">
                                    <InputIcon class="pi pi-search" />
                                    <InputText v-model="searchQuery" placeholder="Search..." size="small" />
                                </IconField>
                            </div>
                        </template>
                        <template #content>
                            <div class="overflow-y-auto h-full pr-2 flex flex-col gap-3">
                                <div v-if="loading && notes.length === 0" class="text-center py-10">
                                    <ProgressSpinner style="width: 30px; height: 30px" />
                                </div>
                                <div v-else-if="filteredNotes.length === 0" class="text-center py-10 text-gray-400">
                                    No saved notes found.
                                </div>
                                <div v-for="note in filteredNotes" :key="note.id" 
                                    class="p-4 rounded-xl border border-gray-100 hover:border-primary hover:shadow-md cursor-pointer transition-all group relative bg-gray-50/50"
                                    @click="loadNote(note)">
                                    
                                    <div class="flex justify-between items-start mb-2">
                                        <Tag :value="note.type === 'order_receipt' ? 'Order' : 'Hisab'" 
                                            :severity="note.type === 'order_receipt' ? 'info' : 'warn'" size="small" />
                                        <span class="text-[10px] text-gray-400 font-bold uppercase">{{ formatDateSimple(note.created_at) }}</span>
                                    </div>
                                    
                                    <h3 class="font-black text-gray-900 text-sm truncate pr-6">{{ note.title || 'Untitled Bill' }}</h3>
                                    <p class="text-xs text-gray-500 truncate" v-if="note.customer_name">Client: {{ note.customer_name }}</p>
                                    
                                    <div class="flex justify-between items-end mt-3 pt-3 border-t border-gray-100">
                                        <span class="text-lg font-black text-primary font-mono">{{ formatCurrency(Number(note.total_amount)) }}</span>
                                        <Button icon="pi pi-trash" severity="danger" text rounded size="small" 
                                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                                            @click.stop="deleteNote(note.id)" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Edit Line Item Dialog -->
        <Dialog v-model:visible="showEditModal" header="Edit Bill Line" :modal="true" :style="{ width: '400px' }">
            <div class="flex flex-col gap-4 pt-2">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Description</label>
                    <InputText v-model="editingItem.name" ref="editNameRef" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Quantity</label>
                        <InputNumber v-model="editingItem.qty" :minFractionDigits="2" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Unit Price</label>
                        <InputNumber v-model="editingItem.price" mode="currency" currency="INR" locale="en-IN" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="cancelEdit" />
                <Button label="Update Line" icon="pi pi-check" @click="saveEdit" />
            </template>
        </Dialog>

        <!-- Hidden Print Area -->
        <div id="print-area" class="hidden print-only font-mono w-[80mm] p-4 text-black bg-white">
            <div class="text-center border-b-2 border-dashed border-gray-400 pb-4 mb-4">
                <div class="text-lg font-black">{{ mode === 'order_receipt' ? 'ROUGH ESTIMATE' : 'HISAB SLIP' }}</div>
                <div class="text-xl font-bold mt-1 uppercase">{{ noteTitle || 'Note' }}</div>
                <div class="text-xs mt-1">{{ currentDate }}</div>
            </div>
            <div class="space-y-3 mb-6">
                <div v-for="(item, i) in items" :key="i" class="flex justify-between items-start border-b border-dotted border-gray-300 pb-1">
                    <div class="flex-1 pr-2">
                        <div class="font-bold">{{ item.name || 'Item '+(i+1) }}</div>
                        <div v-if="item.qty > 1" class="text-[10px]">{{ item.qty }} x {{ formatCurrency(item.price) }}</div>
                    </div>
                    <div class="font-black">{{ formatCurrency(item.total) }}</div>
                </div>
            </div>
            <div class="text-right pt-2 border-t-2 border-dashed border-black">
                <div class="text-xs uppercase font-bold">Total Payable</div>
                <div class="text-2xl font-black">{{ formatCurrency(grandTotal) }}</div>
            </div>
            <div class="text-center text-[10px] mt-8 opacity-50 italic">* Non-GST / Non-Taxable Rough Draft *</div>
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

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import SelectButton from 'primevue/selectbutton'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import ProgressSpinner from 'primevue/progressspinner'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const router = useRouter()
const quickNoteStore = useQuickNoteStore()
const { loading, notes } = storeToRefs(quickNoteStore)

const mode = ref<'order_receipt' | 'hisab'>('order_receipt')
const modeOptions = [
    { label: 'Order/Sales', value: 'order_receipt' },
    { label: 'Hisab/Math', value: 'hisab' }
]

const inputLine = ref('')
const selectedOperator = ref('+')
const orderItems = ref<ParsedItem[]>([])
const hisabItems = ref<ParsedItem[]>([])

const currentNoteId = ref<string | null>(null)
const noteTitle = ref('')
const noteDescription = ref('')
const customerName = ref('')
const customerMobile = ref('')

const searchQuery = ref('')
const filteredNotes = computed(() => {
    if (!searchQuery.value) return notes.value
    const q = searchQuery.value.toLowerCase()
    return notes.value.filter(n => n.title?.toLowerCase().includes(q) || n.customer_name?.toLowerCase().includes(q) || n.customer_mobile?.includes(q))
})

const items = computed({
    get: () => mode.value === 'order_receipt' ? orderItems.value : hisabItems.value,
    set: (val) => { if (mode.value === 'order_receipt') orderItems.value = val; else hisabItems.value = val }
})

const inputRef = ref<any>(null)
const editNameRef = ref<any>(null)

const showEditModal = ref(false)
const editingIndex = ref<number | null>(null)
const editingItem = ref<ParsedItem>({ raw: '', qty: 0, name: '', price: 0, total: 0, is_valid: false })

const currentDate = computed(() => new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }))
const grandTotal = computed(() => items.value.reduce((sum, item) => sum + item.total, 0))
const runningTotals = computed(() => {
    let sum = 0; return items.value.map(item => { sum += item.total; return sum })
})

const focusInput = () => setTimeout(() => inputRef.value?.$el?.focus(), 50)

const addItem = () => {
    let raw = inputLine.value.trim(); if (!raw) return
    if (mode.value === 'hisab') {
        if (selectedOperator.value === '-') { if (!/^[+-]/.test(raw)) raw = `- ${raw}` }
        else if (['*', '/'].includes(selectedOperator.value)) {
            if (/^\d+(\.\d+)?$/.test(raw)) raw = `${selectedOperator.value} ${raw}`
            else raw = raw.replace(/\s+/, ` ${selectedOperator.value} `)
        }
    }

    const parsed = parseLine(raw)
    if (parsed.is_percentage && parsed.percentage_val !== undefined) {
        const amt = grandTotal.value * (Math.abs(parsed.percentage_val) / 100)
        parsed.price = amt; parsed.total = parsed.percentage_val < 0 ? -amt : amt
        if (!parsed.name) parsed.name = `${Math.abs(parsed.percentage_val)}%`
    }

    if (parsed.is_chained_math && parsed.chained_val !== undefined) {
        const cur = grandTotal.value
        let diff = parsed.chained_op === '*' ? cur * (parsed.chained_val - 1) : (parsed.chained_val !== 0 ? (cur / parsed.chained_val) - cur : 0)
        parsed.name = `${parsed.chained_op === '*' ? '×' : '÷'} ${parsed.chained_val}`
        parsed.price = Math.abs(diff); parsed.total = diff
    }

    items.value.push(parsed)
    inputLine.value = ''; focusInput()
}

const removeItem = (index: number) => items.value.splice(index, 1)
const clearAll = () => { if (confirm('Clear note?')) { items.value = []; currentNoteId.value = null; noteTitle.value = ''; customerName.value = ''; customerMobile.value = ''; noteDescription.value = '' } }

const editItem = (index: number) => {
    const item = items.value[index];
    if (!item) return;
    editingIndex.value = index; 
    editingItem.value = { 
        raw: item.raw || '',
        qty: item.qty || 0,
        name: item.name || '',
        price: item.price || 0,
        total: item.total || 0,
        is_valid: !!item.is_valid
    }; 
    showEditModal.value = true
    nextTick(() => editNameRef.value?.$el?.focus())
}

const saveEdit = () => {
    if (editingIndex.value !== null) {
        editingItem.value.total = editingItem.value.qty * editingItem.value.price
        items.value[editingIndex.value] = { ...editingItem.value }; showEditModal.value = false
    }
}
const cancelEdit = () => { showEditModal.value = false }

const formatCurrency = (val: number) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val)
const formatDateSimple = (d: any) => new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short' })

const convertToInvoice = () => {
    if (items.value.length === 0) return
    const data = { party: customerName.value ? { name: customerName.value } : null, items: items.value.map(i => ({ name: i.name, quantity: i.qty, unit_price: i.price, tax_rate: 0, discount: 0 })), notes: noteDescription.value }
    localStorage.setItem('pending_invoice_items', JSON.stringify(data))
    router.push({ name: 'invoice-create', query: { from_quick_note: 'true' } })
}

const saveToBackend = async () => {
    if (items.value.length === 0) return
    try {
        await quickNoteStore.saveNote({ id: currentNoteId.value || undefined, type: mode.value, title: noteTitle.value || 'Untitled Note', description: noteDescription.value, customer_name: customerName.value, customer_mobile: customerMobile.value, content: items.value, total_amount: grandTotal.value })
        alert('Saved!'); quickNoteStore.fetchNotes()
    } catch (e) { alert('Failed to save') }
}

const loadNote = (note: QuickNote) => {
    if (items.value.length > 0 && !confirm('Clear current and load?')) return
    currentNoteId.value = note.id; mode.value = note.type; noteTitle.value = note.title; noteDescription.value = note.description || ''; customerName.value = (note as any).customer_name || ''; customerMobile.value = (note as any).customer_mobile || ''; items.value = JSON.parse(JSON.stringify(note.content))
}

const deleteNote = async (id: string) => { if (confirm('Delete?')) await quickNoteStore.deleteNote(id) }
const printReceipt = () => window.print()

const shareToWhatsApp = () => {
    let phone = customerMobile.value.trim(); if (phone.length === 10) phone = '+91' + phone
    let msg = `*${noteTitle.value || 'Rough Bill'}*\nDate: ${currentDate.value}\n`;
    if (customerName.value) msg += `Client: ${customerName.value}\n`;
    msg += `----------------------------\n`;
    items.value.forEach((it, i) => msg += `• ${it.name || 'Item '+(i+1)} ${it.qty>1?`(${it.qty}x${it.price})`:''} = ${it.total}\n`);
    msg += `----------------------------\n*Total Due: ${formatCurrency(grandTotal.value)}*\n`;
    const url = `https://wa.me/${phone.replace(/[^0-9+]/g, '')}?text=${encodeURIComponent(msg)}`
    window.open(phone ? url : `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`, '_blank')
}

onMounted(() => { focusInput(); quickNoteStore.fetchNotes() })
</script>

<style scoped>
.paper-note-card { background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 20px 20px; }
.ledger-lines { background-image: linear-gradient(#f0f2f5 1px, transparent 1px); background-size: 100% 3rem; margin-top: 2.9rem; opacity: 0.5; }
.mode-toggle :deep(.p-button) { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; }
@media print {
    :global(body > *:not(#print-area)) { display: none !important; }
    #print-area { display: block !important; position: absolute; top: 0; left: 0; width: 100%; }
}
</style>
