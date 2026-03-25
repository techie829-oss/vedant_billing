<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Loading State -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                <ProgressSpinner style="width: 50px; height: 50px" />
                <p class="mt-4 text-gray-500">Loading document details...</p>
            </div>

            <div v-else-if="invoice" class="max-w-5xl mx-auto space-y-6 pb-20">
                <!-- Header & Actions -->
                <div class="flex flex-wrap items-center justify-between gap-4 print:hidden">
                    <div class="flex items-center gap-4">
                        <Button icon="pi pi-arrow-left" severity="secondary" rounded text @click="router.push(backRoute)" />
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 m-0">
                                {{ typeLabel }} #{{ invoice.invoice_number }}
                            </h1>
                            <div class="flex items-center gap-2 mt-1">
                                <Tag :value="invoice.status.toUpperCase()" :severity="getStatusSeverity(invoice.status)" />
                                <span class="text-gray-500 text-sm">Created on {{ formatDate(invoice.date) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <Button v-if="invoice.status === 'draft'" label="Edit" icon="pi pi-pencil" @click="router.push(editRoute)" />
                        <Button v-if="invoice.type === 'quote' && ['accepted', 'draft', 'sent'].includes(invoice.status)" 
                            label="Convert to Invoice" icon="pi pi-sync" severity="success" @click="convertToInvoice" :loading="converting" />
                        
                        <Button label="Print" icon="pi pi-print" severity="secondary" outlined @click="printInvoice" />
                        <Button label="PDF" icon="pi pi-file-pdf" severity="secondary" outlined @click="downloadPdf" :loading="downloading" />
                        <Button label="WhatsApp" icon="pi pi-whatsapp" severity="success" text @click="shareWhatsApp" />
                        
                        <template v-if="invoice.status === 'draft' && invoice.type !== 'quote'">
                            <Button label="Finalize" icon="pi pi-check-circle" severity="success" @click="finalize" />
                        </template>
                        
                        <Button v-if="invoice.status !== 'draft' && invoice.status !== 'paid' && invoice.type === 'invoice'" 
                            label="Record Payment" icon="pi pi-wallet" severity="success" @click="showPaymentModal = true" />
                    </div>
                </div>

                <!-- Secondary Actions Bar -->
                <div class="flex flex-wrap items-center gap-2 print:hidden bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                    <Select v-model="printCopyType" :options="copyTypes" optionLabel="label" optionValue="value" class="w-44" />
                    <Button label="Duplicate" icon="pi pi-copy" severity="secondary" text size="small" @click="duplicateInvoice" />
                    <Button v-if="invoice.status !== 'draft' && invoice.type === 'invoice'" 
                        label="Return / Credit Note" icon="pi pi-replay" severity="danger" text size="small" @click="createCreditNote" />
                    <Button label="Send Email" icon="pi pi-envelope" severity="secondary" text size="small" @click="sendEmail" :loading="sendingEmail" />
                </div>

                <!-- Related Data Tabs -->
                <div v-if="(invoice.allocations?.length || invoice.credit_notes?.length)" class="print:hidden">
                    <Tabs value="0">
                        <TabList>
                            <Tab v-if="invoice.allocations?.length" value="0">Payment History ({{ invoice.allocations.length }})</Tab>
                            <Tab v-if="invoice.credit_notes?.length" value="1">Credit Notes ({{ invoice.credit_notes.length }})</Tab>
                        </TabList>
                        <TabPanels>
                            <TabPanel value="0">
                                <DataTable :value="invoice.allocations" class="p-datatable-sm">
                                    <Column field="payment.date" header="Date"></Column>
                                    <Column field="payment.reference" header="Reference"></Column>
                                    <Column field="payment.method" header="Method">
                                        <template #body="{ data }"><span class="capitalize">{{ data.payment.method.replace('_', ' ') }}</span></template>
                                    </Column>
                                    <Column field="amount" header="Amount" style="text-align: right">
                                        <template #body="{ data }">₹{{ Number(data.amount).toFixed(2) }}</template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                            <TabPanel value="1">
                                <DataTable :value="invoice.credit_notes" class="p-datatable-sm">
                                    <Column field="invoice_number" header="Number"></Column>
                                    <Column field="date" header="Date"></Column>
                                    <Column field="reason" header="Reason"></Column>
                                    <Column field="grand_total" header="Amount" style="text-align: right">
                                        <template #body="{ data }">₹{{ Number(data.grand_total).toFixed(2) }}</template>
                                    </Column>
                                    <Column header="Actions">
                                        <template #body="{ data }">
                                            <Button icon="pi pi-eye" text rounded @click="router.push(`/credit-notes/${data.id}`)" />
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>
                </div>

                <!-- Preview Area -->
                <div ref="containerRef" class="invoice-container w-full flex justify-center pb-8 overflow-hidden bg-gray-100/50 rounded-2xl border border-gray-200 pt-4 shadow-inner">
                    <div :style="{ height: scaledHeight + 'px', width: scaledWidth + 'px' }" class="invoice-scale-wrapper relative transition-all duration-200">
                        <div ref="contentRef" class="invoice-content-wrapper origin-top-left absolute top-0 left-0 bg-white shadow-xl flex flex-col items-center"
                            :style="{ transform: `scale(${scale})`, width: '210mm', minHeight: '297mm' }">
                            <component :is="layoutComponent" :invoice="invoice" :taxBreakdown="taxBreakdown"
                                :qrCodeUrl="qrCodeUrl" :copyType="printCopyType" class="box-border" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Record Payment Dialog -->
        <Dialog v-model:visible="showPaymentModal" header="Record Payment" :modal="true" :style="{ width: '450px' }" :breakpoints="{'960px': '75vw', '641px': '100vw'}">
            <div class="flex flex-col gap-4 pt-2">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Amount Received *</label>
                    <InputNumber v-model="paymentForm.amount" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" autofocus />
                    <small class="text-red-600 font-bold">Outstanding: ₹{{ outstandingAmount }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Payment Date</label>
                    <DatePicker v-model="paymentForm.date" dateFormat="yy-mm-dd" showIcon />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Method</label>
                    <Select v-model="paymentForm.method" :options="paymentMethods" optionLabel="label" optionValue="value" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Reference / Notes</label>
                    <InputText v-model="paymentForm.reference" placeholder="e.g. UPI Ref, Cheque No." />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text @click="showPaymentModal = false" />
                <Button label="Record Payment" icon="pi pi-check" :loading="submittingPayment" @click="submitPayment" />
            </template>
        </Dialog>

        <!-- Generic Confirm Dialog (Using PrimeVue ConfirmDialog would be better but let's stick to our pattern for consistency) -->
        <Dialog v-model:visible="confirmModal.isOpen" :header="confirmModal.title" :modal="true" :style="{ width: '400px' }">
            <div class="flex flex-col gap-4">
                <p>{{ confirmModal.message }}</p>
            </div>
            <template #footer>
                <Button label="Cancel" text @click="confirmModal.isOpen = false" />
                <Button :label="confirmModal.confirmText" @click="handleConfirm" :loading="confirmModal.processing" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import { useInvoiceStore } from '../../stores/invoice'
import { storeToRefs } from 'pinia'
import DefaultLayout from './layouts/DefaultLayout.vue'
import ProfessionalLayout from './layouts/ProfessionalLayout.vue'
import GridPremiumLayout from './layouts/GridPremiumLayout.vue'
import ClassicGridLayout from './layouts/ClassicGridLayout.vue'
import HalfPageLayout from './layouts/HalfPageLayout.vue'
import client from '../../api/client'

// PrimeVue
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Select from 'primevue/select'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import ProgressSpinner from 'primevue/progressspinner'

// @ts-ignore
import QRCode from 'qrcode'
// @ts-ignore
import html2pdf from 'html2pdf.js'

const route = useRoute()
const router = useRouter()
const invoiceStore = useInvoiceStore()
const { currentInvoice: invoice, loading } = storeToRefs(invoiceStore)

const printCopyType = ref('original')
const copyTypes = [
    { label: 'Original for Recipient', value: 'original' },
    { label: 'Duplicate for Transporter', value: 'duplicate' },
    { label: 'Triplicate for Supplier', value: 'triplicate' }
]

const paymentMethods = [
    { label: 'Cash', value: 'cash' },
    { label: 'Bank Transfer', value: 'bank_transfer' },
    { label: 'UPI', value: 'upi' },
    { label: 'Check', value: 'check' },
    { label: 'Card', value: 'card' }
]

const backRoute = computed(() => {
    if (!invoice.value) return '/invoices'
    return invoice.value.type === 'credit_note' ? '/credit-notes' : '/invoices'
})

const editRoute = computed(() => {
    if (!invoice.value) return ''
    const path = invoice.value.type === 'quote' ? 'quotations' : (invoice.value.type === 'credit_note' ? 'credit-notes' : 'invoices')
    return `/${path}/${invoice.value.id}/edit`
})

const typeLabel = computed(() => {
    if (!invoice.value) return 'Invoice'
    if (invoice.value.type === 'quote') return 'Estimate'
    if (invoice.value.type === 'credit_note') return 'Credit Note'
    return 'Invoice'
})

const getStatusSeverity = (status: string) => {
    switch (status) {
        case 'paid': return 'success'
        case 'sent': return 'info'
        case 'overdue': return 'danger'
        default: return 'secondary'
    }
}

const formatDate = (d: any) => d ? new Date(d).toLocaleDateString() : ''

// Scaling Logic for A4 Preview
const containerRef = ref<HTMLElement | null>(null)
const contentRef = ref<HTMLElement | null>(null)
const scale = ref(1)
const scaledHeight = ref(1123)
const scaledWidth = ref(794)

const updateScale = () => {
    if (!containerRef.value || !contentRef.value) return
    const padding = 24
    const availableWidth = Math.min(window.innerWidth - padding, containerRef.value.clientWidth)
    const contentOriginalWidth = 794
    const newScale = availableWidth < contentOriginalWidth ? availableWidth / contentOriginalWidth : 1
    scale.value = newScale
    const originalHeight = contentRef.value.scrollHeight || 1123
    scaledHeight.value = originalHeight * newScale
    scaledWidth.value = contentOriginalWidth * newScale
}

let resizeObserver: ResizeObserver | null = null
onMounted(() => {
    window.addEventListener('resize', updateScale)
    loadInvoice()
})
onUnmounted(() => {
    window.removeEventListener('resize', updateScale)
    if (resizeObserver) resizeObserver.disconnect()
})
watch(contentRef, (el) => {
    if (el) {
        if (resizeObserver) resizeObserver.disconnect()
        resizeObserver = new ResizeObserver(updateScale)
        resizeObserver.observe(el)
        updateScale()
    }
})

// Business Logic
const converting = ref(false)
const downloading = ref(false)
const sendingEmail = ref(false)
const showPaymentModal = ref(false)
const submittingPayment = ref(false)
const qrCodeUrl = ref('')

const paymentForm = reactive({
    amount: 0,
    date: new Date(),
    method: 'bank_transfer',
    reference: ''
})

const outstandingAmount = computed(() => {
    if (!invoice.value) return 0
    const paid = (invoice.value.allocations || []).reduce((sum: number, a: any) => sum + Number(a.amount), 0)
    return (Number(invoice.value.grand_total) - paid).toFixed(2)
})

watch(() => showPaymentModal.value, (val) => {
    if (val && invoice.value) paymentForm.amount = Number(outstandingAmount.value)
})

const loadInvoice = async () => {
    await invoiceStore.fetchInvoice(route.params.id as string)
    if (invoice.value?.business?.meta?.upi_id) {
        const upiUrl = `upi://pay?pa=${invoice.value.business.meta.upi_id}&pn=${encodeURIComponent(invoice.value.business.name)}&am=${Math.round(invoice.value.grand_total)}&cu=INR`
        qrCodeUrl.value = await QRCode.toDataURL(upiUrl)
    }
}

const submitPayment = async () => {
    if (!invoice.value || !paymentForm.amount) return
    submittingPayment.value = true
    try {
        await invoiceStore.recordPayment(invoice.value.id, {
            amount: paymentForm.amount,
            payment_date: paymentForm.date.toISOString().split('T')[0],
            payment_method: paymentForm.method,
            notes: paymentForm.reference
        })
        showPaymentModal.value = false
        await loadInvoice()
    } finally { submittingPayment.value = false }
}

const finalize = async () => {
    if (!invoice.value) return
    if (confirm('Once finalized, you cannot edit this document. Continue?')) {
        await invoiceStore.finalizeInvoice(invoice.value.id)
        await loadInvoice()
    }
}

const convertToInvoice = async () => {
    if (!invoice.value) return
    converting.value = true
    try {
        const newInv = await invoiceStore.convertEstimateToInvoice(invoice.value.id)
        router.push(`/invoices/${newInv.id}/edit`)
    } finally { converting.value = false }
}

const duplicateInvoice = async () => {
    if (!invoice.value) return
    const newInv = await invoiceStore.duplicateInvoice(invoice.value.id)
    router.push(`/invoices/${newInv.id}/edit`)
}

const createCreditNote = () => {
    if (!invoice.value) return
    router.push({ name: 'credit-note-create', query: { parent_id: invoice.value.id } })
}

const printInvoice = () => {
    if (!invoice.value) return
    let printContainer = document.getElementById('print-container') || document.createElement('div')
    printContainer.id = 'print-container'
    document.body.appendChild(printContainer)
    if (contentRef.value) {
        printContainer.innerHTML = ''
        const clone = contentRef.value.cloneNode(true) as HTMLElement
        clone.style.transform = 'none'
        clone.style.width = '100%'
        printContainer.appendChild(clone)
    }
    window.print()
}

const downloadPdf = async () => {
    if (!invoice.value) return
    downloading.value = true
    try {
        const source = contentRef.value?.firstElementChild as HTMLElement
        if (!source) return
        const clone = source.cloneNode(true) as HTMLElement
        clone.style.maxWidth = '210mm'
        clone.style.width = '210mm'
        clone.style.height = 'auto'
        clone.style.minHeight = '296mm'
        
        const opt = {
            margin: 0,
            filename: `${invoice.value.invoice_number}.pdf`,
            image: { type: 'jpeg' as const, quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0, width: 794 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' as const }
        }
        await html2pdf().set(opt).from(clone).save()
    } finally { downloading.value = false }
}

const shareWhatsApp = () => {
    if (!invoice.value || !invoice.value.party) return
    const phone = invoice.value.party.phone
    if (!phone) return alert('Phone number missing')
    const appUrl = import.meta.env.VITE_MAIN_URL || window.location.origin
    const text = encodeURIComponent(`Hello ${invoice.value.party.name},\n\nYour Invoice ${invoice.value.invoice_number} for ₹${outstandingAmount.value} is ready.\n\nView here: ${appUrl}/p/invoices/${invoice.value.id}\n\nThank you!`)
    window.open(`https://wa.me/91${phone.replace(/[^0-9]/g, '')}?text=${text}`, '_blank')
}

const sendEmail = async () => {
    if (!invoice.value) return
    sendingEmail.value = true
    try {
        await client.post(`/invoices/${invoice.value.id}/email`)
        alert('Email sent successfully')
    } finally { sendingEmail.value = false }
}

// Layout & Tax Helper
const layoutComponent = computed(() => {
    const layout = invoice.value?.meta?.invoice_layout || invoice.value?.business?.meta?.invoice_layout || 'default'
    const layouts: any = { professional: ProfessionalLayout, grid_premium: GridPremiumLayout, classic: ClassicGridLayout, half_page: HalfPageLayout }
    return layouts[layout] || DefaultLayout
})

const taxBreakdown = computed(() => {
    if (!invoice.value) return {}
    const hsnMap: any = {}
    const bState = invoice.value.business?.meta?.state?.toLowerCase()
    const pState = (invoice.value.party?.shipping_address?.state || invoice.value.party?.billing_address?.state || '').toLowerCase()
    const isInter = bState && pState && bState !== pState

    invoice.value.items.forEach((item: any) => {
        const hsn = item.hsn_code || '-'
        if (!hsnMap[hsn]) hsnMap[hsn] = { hsn, taxable: 0, cgst_amount: 0, sgst_amount: 0, igst_amount: 0, cess_amount: 0, total_tax: 0, tax_rate: Number(item.tax_rate) }
        const g = hsnMap[hsn]
        const tax = Number(item.tax_amount) || 0
        g.taxable += Number(item.total) || 0
        if (isInter) g.igst_amount += tax
        else { g.cgst_amount += tax/2; g.sgst_amount += tax/2 }
        g.cess_amount += Number(item.cess_amount) || 0
        g.total_tax += (tax + Number(item.cess_amount))
    })
    return { isInter, taxType: isInter ? 'IGST' : 'CGST+SGST', hsnGroups: Object.values(hsnMap) }
})

// Modal states for confirm
const confirmModal = reactive({ isOpen: false, title: '', message: '', confirmText: 'Confirm', processing: false, onConfirm: async () => {} })
const handleConfirm = async () => {
    confirmModal.processing = true
    try { await confirmModal.onConfirm(); confirmModal.isOpen = false } 
    finally { confirmModal.processing = false }
}
</script>

<style scoped>
.items-card :deep(.p-card-body) { padding: 0; }
@media print {
    :global(body > *:not(#print-container)) { display: none !important; }
    :global(#print-container) { display: block !important; position: absolute; top: 0; left: 0; width: 100%; background: white; }
}
</style>
