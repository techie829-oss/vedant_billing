<template>
    <!-- Print Controls Header (Hidden in Print) -->
    <div v-if="invoice" class="fixed top-0 inset-x-0 bg-white border-b border-gray-200 z-50 print:hidden shadow-sm">
        <!-- Header Bar -->
        <div class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <button @click="closeWindow"
                    class="text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center gap-1">
                    &larr; Close
                </button>
                <span class="text-gray-300">|</span>
                <h1 class="text-lg font-bold text-gray-900">Print Preview</h1>
            </div>
            <div class="flex items-center gap-3">
                <button @click="printInvoice"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print / Download PDF
                </button>
            </div>
        </div>

        <!-- Notice Bar for Browser Blocking -->
        <div class="bg-yellow-50 border-t border-yellow-100 px-4 sm:px-6 lg:px-8 py-2">
            <p class="text-xs text-yellow-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span><strong>Button not working?</strong> Some browsers block print popups. Press <kbd
                        class="px-1.5 py-0.5 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded">Ctrl+P</kbd>
                    (Windows/Linux) or <kbd
                        class="px-1.5 py-0.5 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded">⌘+P</kbd>
                    (Mac) instead.</span>
            </p>
        </div>
    </div>

    <div class="print-container bg-white" v-if="invoice">
        <component :is="layoutComponent" :invoice="invoice" :taxBreakdown="taxBreakdown" :qrCodeUrl="qrCodeUrl" />
    </div>
    <div v-else class="flex items-center justify-center h-screen">
        <p class="text-gray-500 animate-pulse">{{ status }}</p>
    </div>

    <!-- Debug Status Footer -->
    <div
        class="fixed bottom-0 inset-x-0 bg-gray-900 text-white text-[10px] p-1 text-center print:hidden opacity-75 z-50">
        Status: {{ status }} <span v-if="debugMsg" class="text-red-300">| Error: {{ debugMsg }}</span>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useInvoiceStore } from '../../stores/invoice'
import { storeToRefs } from 'pinia'
import DefaultLayout from './layouts/DefaultLayout.vue'
import ProfessionalLayout from './layouts/ProfessionalLayout.vue'
import GridPremiumLayout from './layouts/GridPremiumLayout.vue'
import ClassicGridLayout from './layouts/ClassicGridLayout.vue'
// @ts-ignore
import QRCode from 'qrcode'

const route = useRoute()
const invoiceStore = useInvoiceStore()
const { currentInvoice: invoice } = storeToRefs(invoiceStore)
const qrCodeUrl = ref('')

const layoutComponent = computed(() => {
    const queryLayout = route.query.layout as string
    const layout = queryLayout || invoice.value?.business?.meta?.invoice_layout || 'default'

    if (layout === 'professional') return ProfessionalLayout
    if (layout === 'grid_premium') return GridPremiumLayout
    if (layout === 'classic') return ClassicGridLayout
    // Fallback to classic for now if user just asked for it? No, better safe.
    // Actually, let's default to classic if the user specifically requested "make one more template" 
    // and we are in dev mode? No, stick to query param or explicit setting.
    return DefaultLayout
})

const taxBreakdown = computed(() => {
    if (!invoice.value) return { cgst: 0, sgst: 0, igst: 0, taxType: '', posState: '' }

    let cgst = 0
    let sgst = 0
    let igst = 0

    const businessState = invoice.value.business?.meta?.state?.toLowerCase()
    const customer = invoice.value.party
    const customerState = (customer?.shipping_address?.state || customer?.billing_address?.state || '').toLowerCase()

    const isInterState = businessState && customerState && businessState !== customerState
    const taxType = isInterState ? 'IGST' : 'CGST+SGST'

    invoice.value.items.forEach((item: any) => {
        const lineTax = Number(item.tax_amount) || 0
        if (isInterState) {
            igst += lineTax
        } else {
            cgst += lineTax / 2
            sgst += lineTax / 2
        }
    })

    return {
        cgst, sgst, igst, taxType,
        posState: customer?.shipping_address?.state || customer?.billing_address?.state || 'N/A'
    }
})

const generateQR = async () => {
    if (!invoice.value?.business?.meta?.upi_id) return
    const upiId = invoice.value.business.meta.upi_id
    const payName = invoice.value.business.name.replace(/\s/g, '+')
    const amount = Math.round(invoice.value.grand_total)
    const upiUrl = `upi://pay?pa=${upiId}&pn=${payName}&am=${amount}&cu=INR`
    try {
        qrCodeUrl.value = await QRCode.toDataURL(upiUrl)
    } catch (err) {
        console.error(err)
    }
}

const status = ref('Initializing...')
const debugMsg = ref('')

const printInvoice = () => {
    status.value = 'Printing...'
    try {
        // Set logical title for print (replaces "Vedant Billing" in browser header)
        // This title is used by the browser as the default filename for "Save as PDF"
        document.title = invoice.value?.invoice_number || 'Invoice'

        window.print()

        // We do NOT restore the title immediately because some browsers might capture it late.
        // It's a dedicated print view anyway, so keeping the invoice number as title is fine/better.
        status.value = 'Print dialog triggered - Use Ctrl+P or ⌘+P if blocked'
    } catch (e: any) {
        status.value = 'Print Error: ' + e.message
    }
}

const closeWindow = () => {
    console.log('closeWindow() called')
    window.close()
}

onMounted(async () => {
    status.value = 'Fetching Invoice...'

    try {
        await invoiceStore.fetchInvoice(route.params.id as string)

        if (invoice.value) {
            status.value = 'Generating QR...'
            await generateQR()

            status.value = 'Rendering...'
            await nextTick()

            status.value = 'Ready - Press Ctrl+P or ⌘+P to print'
        } else {
            status.value = 'Invoice not found'
        }
    } catch (e: any) {
        status.value = 'Error loading invoice'
        debugMsg.value = e.message
    }
})
</script>

<style>
/* Global print styles for this view */
@media print {
    @page {
        margin: 0;
        size: A4;
    }

    html,
    body {
        height: auto !important;
        overflow: visible !important;
        background: white;
        padding: 0;
        margin: 0;
    }

    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .print-container {
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        display: block !important;
    }

    #invoice-paper {
        box-shadow: none !important;
        margin: 0 !important;
        border: none !important;
        width: 100% !important;
        max-width: 100% !important;
        min-height: 0 !important;
        height: auto !important;
        overflow: visible !important;
        display: block !important;
    }
}

/* Screen styles for preview */
@media screen {
    body {
        background: #f3f4f6;
    }

    /* gray-100 */
    .print-container {
        display: flex;
        justify-content: center;
        padding: 120px 0 60px;
        /* Account for header + notice bar */
        min-height: 100vh;
    }
}
</style>
