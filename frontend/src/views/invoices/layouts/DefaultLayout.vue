<template>
    <div class="print-layout-container bg-gray-100 print:bg-white pb-10 print:pb-0 px-4 print:px-0 pt-8 print:pt-0">

        <!-- Loop Explicit Pages -->
        <!-- Each page is a strict A4 sized block -->
        <div v-for="(page, pageIndex) in pages" :key="pageIndex"
            class="a4-page mx-auto bg-white shadow-lg print:shadow-none print:m-0 flex flex-col relative mb-8 print:mb-0 break-after-page">

            <!-- HEADER (Fixed height Visual) -->
            <div class="px-[10mm] py-6 border-b border-gray-200 shrink-0">
                <div class="flex gap-5 justify-between items-start">
                    <div class="flex gap-5">
                        <div v-if="invoice.business?.meta?.logo_url"
                            class="h-14 w-14 bg-gray-50 rounded-lg flex items-center justify-center overflow-hidden border border-gray-100">
                            <img :src="invoice.business.meta.logo_url" class="h-full w-full object-contain" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-gray-900">{{ invoice.business?.name }}</h1>
                            <div class="mt-1 text-xs text-gray-500 space-y-0.5">
                                <p class="whitespace-pre-line">{{ invoice.business?.address }}</p>
                                <p v-if="invoice.business?.state || invoice.business?.meta?.state">
                                    {{ invoice.business?.meta?.city || invoice.business?.city }}, {{
                                        invoice.business?.meta?.state || invoice.business?.state }} - {{
                                        invoice.business?.meta?.zip || invoice.business?.zip || invoice.business?.pincode ||
                                        invoice.business?.meta?.pincode }}
                                </p>
                                <p v-if="invoice.business?.gstin" class="font-bold text-gray-700">GSTIN: {{
                                    invoice.business.gstin }}</p>
                                <p v-if="invoice.business?.pan" class="font-bold text-gray-700">PAN: {{
                                    invoice.business.pan }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ documentTitle }}
                        </p>
                        <p v-if="complianceText" class="text-[9px] font-bold text-gray-500 uppercase mt-1">
                            {{ complianceText }}
                        </p>
                        <p v-if="copyType && isTaxDocument" class="text-[10px] font-bold text-gray-500 uppercase mb-1">
                            ({{ copyLabel }})
                        </p>
                        <h2 class="text-lg font-mono font-bold text-gray-900">{{ invoice.invoice_number }}</h2>
                        <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                            <p>Date: <span class="font-medium text-gray-900">{{ formatDate(invoice.date) }}</span></p>
                            <p v-if="invoice.type !== 'credit_note'">Due: <span class="font-medium text-gray-900">{{
                                formatDate(invoice.due_date) }}</span></p>
                            <!-- Page Indicator (Only if multiple) -->
                            <p v-if="pages.length > 1" class="text-[10px] text-gray-400 mt-1">Page {{ pageIndex + 1 }}
                                of {{ pages.length }}</p>
                        </div>
                    </div>
                </div>
            </div> <!-- Page Content (Grows to fill space) -->
            <div class="flex-grow flex flex-col px-[10mm] py-2 relative">

                <!-- Page 1 Only: Addresses & Metadata -->
                <!-- Page 1 Only: Addresses & Metadata -->
                <div v-if="pageIndex === 0" class="mb-4">
                    <div :class="headerGridClass" class="grid gap-4 py-4 text-sm">
                        <!-- Bill To -->
                        <div>
                            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Bill To
                            </h3>
                            <p class="font-bold text-gray-900">{{ invoice.party?.name }}</p>
                            <div class="text-gray-600 text-xs mt-1 leading-relaxed">
                                <!-- Prioritize Snapshot Address -->
                                <template v-if="safeMeta.billing_address?.street || safeMeta.billing_address?.city">
                                    <p class="whitespace-pre-line">{{ safeMeta.billing_address.street }}</p>
                                    <p>
                                        {{ safeMeta.billing_address.city }}
                                        <span v-if="safeMeta.billing_address.state">, {{
                                            safeMeta.billing_address.state }}</span>
                                        <span v-if="safeMeta.billing_address.zip || safeMeta.billing_address.pincode"> -
                                            {{ safeMeta.billing_address.zip || safeMeta.billing_address.pincode
                                            }}</span>
                                    </p>
                                </template>
                                <template v-else>
                                    <p class="whitespace-pre-line">{{ invoice.party?.billing_address?.street }}</p>
                                    <p>
                                        {{ invoice.party?.billing_address?.city }}
                                        <span v-if="invoice.party?.billing_address?.state">, {{
                                            invoice.party.billing_address.state }}</span>
                                        <span
                                            v-if="invoice.party?.billing_address?.zip || invoice.party?.billing_address?.pincode">
                                            - {{
                                                invoice.party.billing_address.zip || invoice.party.billing_address.pincode
                                            }}</span>
                                    </p>
                                </template>
                                <p v-if="invoice.party?.gstin" class="mt-1 font-medium">GSTIN: {{
                                    invoice.party.gstin }}
                                </p>
                            </div>
                        </div>

                        <!-- Ship To (Conditional) -->
                        <div v-if="shouldShowShipping">
                            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Ship To
                            </h3>
                            <p class="font-bold text-gray-900">{{ invoice.party?.name }}</p>
                            <div class="text-gray-600 text-xs mt-1 leading-relaxed">
                                <template v-if="safeMeta.shipping_address?.street || safeMeta.shipping_address?.city">
                                    <p class="whitespace-pre-line">{{ safeMeta.shipping_address.street }}</p>
                                    <p>{{ safeMeta.shipping_address.city }} {{ safeMeta.shipping_address.state }} -
                                        {{
                                            safeMeta.shipping_address.zip || safeMeta.shipping_address.pincode }}</p>
                                </template>
                                <template v-else>
                                    <p class="whitespace-pre-line">{{ invoice.party?.shipping_address?.street }}</p>
                                    <p>{{ invoice.party?.shipping_address?.city }} {{
                                        invoice.party?.shipping_address?.state }} - {{
                                            invoice.party?.shipping_address?.zip || invoice.party?.shipping_address?.pincode
                                        }}</p>
                                </template>
                            </div>


                        </div>

                        <!-- Transport Details -->
                        <div v-if="displayOpts.show_eway_details" class="text-xs text-gray-500">
                            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Transport
                                Details</h3>
                            <div class="space-y-1">
                                <p><span class="w-16 inline-block font-medium">E-Way No:</span> {{ invoice.eway_bill_no
                                    || '-' }}
                                </p>
                                <p><span class="w-16 inline-block font-medium">Vehicle:</span> {{ invoice.vehicle_no ||
                                    '-' }}
                                </p>
                                <p><span class="w-16 inline-block font-medium">PO No:</span> {{ invoice.po_number ||
                                    '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Spacer if not page 1 (to simulate table continuation) -->
                <div v-if="pageIndex > 0" class="pt-2 text-[10px] text-gray-400 italic mb-2">
                    Continued from previous page...
                </div>

                <!-- ITEMS TABLE -->
                <div class="flex-grow">
                    <table class="w-full text-left table-fixed">
                        <thead class="bg-gray-50 text-[10px] uppercase text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-2 pl-2 w-10">#</th>
                                <th class="py-2 w-auto">Item</th>
                                <th v-if="displayOpts.show_hsn" class="py-2 w-16">HSN</th>
                                <th class="py-2 text-right w-16">Qty</th>
                                <th class="py-2 text-right w-20">MRP</th>
                                <th v-if="displayOpts.show_discount" class="py-2 text-right w-16">Disc</th>
                                <th class="py-2 text-right w-20">Sell Price</th>
                                <th v-if="displayOpts.show_gst_breakdown" class="py-2 text-right w-20">Tax</th>
                                <th class="py-2 text-right pr-2 w-24">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs">
                            <tr v-for="(item, idx) in page.items" :key="idx" class="border-b border-gray-100">
                                <td class="py-2 pl-2 text-gray-400 align-top">{{ item.globalIndex }}</td>
                                <td class="py-2 align-top">
                                    <p class="font-bold text-gray-900">{{ item.name }}</p>
                                    <p v-if="item.description && displayOpts.show_description"
                                        class="text-[9px] text-gray-500 whitespace-pre-line mt-0.5">{{
                                            item.description
                                        }}</p>
                                </td>
                                <td v-if="displayOpts.show_hsn" class="py-2 text-gray-500 align-top">{{
                                    item.hsn_code ||
                                    '-' }}</td>
                                <td class="py-2 text-right text-gray-600 align-top">{{ Number(item.quantity) }}</td>
                                <td class="py-2 text-right text-gray-600 align-top">{{
                                    formatCurrency(item.unit_price)
                                    }}</td>
                                <td v-if="displayOpts.show_discount" class="py-2 text-right text-gray-600 align-top">{{
                                    Number(item.discount) ? formatCurrency(item.discount) : '-' }}</td>
                                <td class="py-2 text-right text-gray-600 align-top">
                                    {{ formatCurrency(item.quantity ? (item.unit_price - (item.discount /
                                        item.quantity)) :
                                        item.unit_price) }}
                                </td>
                                <td v-if="displayOpts.show_gst_breakdown"
                                    class="py-2 text-right text-gray-600 align-top">
                                    {{ formatCurrency(item.tax_amount) }} <span
                                        class="text-[9px] text-gray-400 block">({{
                                            Number(item.tax_rate) }}%)</span>
                                </td>
                                <td class="py-2 text-right font-bold text-gray-900 pr-2 align-top">{{
                                    formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- LAST PAGE ONLY: TOTALS -->
                <div v-if="page.isLastPage && page.hasSpaceForTotals" class="mt-4 pt-4 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Amount in Words</p>
                            <p class="text-xs font-bold text-gray-900 capitalize mt-1">{{
                                amountInWords(finalTotals.rounded) }} Only</p>

                            <!-- Bank Details -->
                            <!-- Bank Details and QR Code Side-by-Side -->
                            <div v-if="displayOpts.show_qr_bank_details" class="mt-4 flex gap-4">
                                <!-- Bank Details -->
                                <div class="text-xs text-gray-600 flex-grow">
                                    <p class="font-bold mb-1">Payment Details:</p>
                                    <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-0.5">
                                        <template v-if="invoice.business?.bank_name">
                                            <span class="text-gray-500">Bank:</span> <span>{{ invoice.business.bank_name
                                                }}</span>
                                        </template>
                                        <template v-if="invoice.business?.account_number">
                                            <span class="text-gray-500">A/c:</span> <span>{{
                                                invoice.business.account_number
                                                }}</span>
                                        </template>
                                        <template v-if="invoice.business?.ifsc_code">
                                            <span class="text-gray-500">IFSC:</span> <span>{{ invoice.business.ifsc_code
                                                }}</span>
                                        </template>
                                        <template v-if="invoice.business?.meta?.upi_id">
                                            <span class="text-gray-500">UPI:</span> <span>{{
                                                invoice.business.meta.upi_id
                                                }}</span>
                                        </template>
                                    </div>
                                </div>
                                <!-- QR Code -->
                                <div v-if="qrCodeUrl" class="shrink-0">
                                    <img :src="qrCodeUrl"
                                        class="h-24 w-24 object-contain border border-gray-100 p-1 bg-white" />
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div v-if="invoice.terms" class="mt-4">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Terms & Conditions</p>
                                <p class="text-[10px] text-gray-500 whitespace-pre-line mt-1">{{ invoice.terms }}</p>
                            </div>


                        </div>

                        <div class="text-right text-sm space-y-1">
                            <div class="flex justify-between"><span>Subtotal:</span> <span>{{
                                formatCurrency(invoice.subtotal) }}</span>
                            </div>
                            <div v-if="displayOpts.show_discount && totalDiscount > 0"
                                class="flex justify-between text-red-600">
                                <span>Discount:</span>
                                <span>-{{ formatCurrency(totalDiscount) }}</span>
                            </div>

                            <!-- Tax Breakdown -->
                            <div v-if="taxBreakdown.taxType === 'IGST'"
                                class="flex justify-between text-gray-600 text-xs">
                                <span>IGST:</span> <span>{{ formatCurrency(taxBreakdown.igst) }}</span>
                            </div>
                            <template v-else>
                                <div class="flex justify-between text-gray-600 text-xs"><span>CGST:</span> <span>{{
                                    formatCurrency(taxBreakdown.cgst) }}</span></div>
                                <div class="flex justify-between text-gray-600 text-xs"><span>SGST:</span> <span>{{
                                    formatCurrency(taxBreakdown.sgst) }}</span></div>
                            </template>

                            <div v-if="finalTotals.roundOff !== 0" class="flex justify-between text-gray-600 text-xs">
                                <span>Round Off:</span>
                                <span>{{ formatCurrency(finalTotals.roundOff) }}</span>
                            </div>

                            <div class="flex justify-between font-bold text-lg border-t border-gray-100 pt-2 mt-2">
                                <span>Total:</span>
                                <span>{{ formatCurrency(finalTotals.rounded) }}</span>
                            </div>

                            <!-- Notes (Right Side) -->
                            <div v-if="invoice.notes" class="mt-8 pt-4 border-t border-gray-100 text-left">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Notes</p>
                                <p class="text-[10px] text-gray-500 whitespace-pre-line mt-1">{{ invoice.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- If this is a dedicated Totals page (no items, just totals) -->
                <div v-if="page.isTotalsOnly" class="mt-4 pt-4">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Amount in Words</p>
                            <p class="text-xs font-bold text-gray-900 capitalize mt-1">{{
                                amountInWords(finalTotals.rounded) }} Only</p>

                            <!-- Terms and Conditions -->
                            <div v-if="invoice.terms" class="mt-4">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Terms & Conditions</p>
                                <p class="text-[10px] text-gray-500 whitespace-pre-line mt-1">{{ invoice.terms }}</p>
                            </div>

                        </div>
                        <div class="text-right text-sm space-y-1">
                            <div class="flex justify-between"><span>Subtotal:</span> <span>{{
                                formatCurrency(invoice.subtotal) }}</span>
                            </div>
                            <div v-if="displayOpts.show_discount && totalDiscount > 0"
                                class="flex justify-between text-red-600">
                                <span>Discount:</span>
                                <span>-{{ formatCurrency(totalDiscount) }}</span>
                            </div>

                            <!-- Tax Breakdown -->
                            <div v-if="taxBreakdown.taxType === 'IGST'"
                                class="flex justify-between text-gray-600 text-xs">
                                <span>IGST:</span> <span>{{ formatCurrency(taxBreakdown.igst) }}</span>
                            </div>
                            <template v-else>
                                <div class="flex justify-between text-gray-600 text-xs"><span>CGST:</span> <span>{{
                                    formatCurrency(taxBreakdown.cgst) }}</span></div>
                                <div class="flex justify-between text-gray-600 text-xs"><span>SGST:</span> <span>{{
                                    formatCurrency(taxBreakdown.sgst) }}</span></div>
                            </template>

                            <div v-if="finalTotals.roundOff !== 0" class="flex justify-between text-gray-600 text-xs">
                                <span>Round Off:</span>
                                <span>{{ formatCurrency(finalTotals.roundOff) }}</span>
                            </div>

                            <div class="flex justify-between font-bold text-lg border-t border-gray-100 pt-2 mt-2">
                                <span>Total:</span>
                                <span>{{ formatCurrency(finalTotals.rounded) }}</span>
                            </div>

                            <!-- Notes (Right Side) -->
                            <div v-if="invoice.notes" class="mt-8 pt-4 border-t border-gray-100 text-left">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Notes</p>
                                <p class="text-[10px] text-gray-500 whitespace-pre-line mt-1">{{ invoice.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- FOOTER (Fixed Height) -->
            <div class="h-[30mm] px-[10mm] pb-[10mm] shrink-0 border-t border-gray-100 flex items-end justify-between">
                <div class="text-[10px] text-gray-400">
                    Powered by <span class="font-bold text-gray-900">Vedant Billing</span>
                </div>
                <div class="text-right">
                    <p
                        class="text-[10px] text-gray-500 uppercase border-t border-gray-300 pt-1 min-w-[150px] text-center">
                        Authorized Signatory</p>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { formatCurrency, formatDate, amountInWords } from '../../../utils/formatters';

const props = defineProps<{
    invoice: any,
    taxBreakdown: any,
    qrCodeUrl: string,
    copyType?: string
}>()

const copyLabel = computed(() => {
    switch (props.copyType) {
        case 'duplicate': return 'DUPLICATE FOR TRANSPORTER'
        case 'triplicate': return 'TRIPLICATE FOR SUPPLIER'
        default: return 'ORIGINAL FOR RECIPIENT'
    }
})

const MAX_ITEMS_FIRST = 15; // Items that fit on first page with addresses and totals
const MAX_ITEMS_STD = 20;   // More items on subsequent pages
const TOTALS_SPACE_ITEMS = 5; // Reserve space for totals section
const safeMeta = computed(() => props.invoice.meta || {})
const displayOpts = computed(() => {
    const opts = safeMeta.value.display_options || {}
    return {
        show_eway_details: opts.show_transport_details ?? opts.show_eway_details ?? false,
        show_hsn: opts.show_hsn ?? true,
        show_gst_breakdown: opts.show_gst_breakdown ?? true,
        show_discount: opts.show_discount ?? false,
        show_qr_bank_details: opts.show_qr_bank_details ?? true,
        show_shipping_address: opts.show_shipping_address ?? false,
        show_description: opts.show_description ?? true
    }
})

const finalTotals = computed(() => {
    const total = Number(props.invoice.grand_total) || 0;
    const rounded = Math.round(total);
    const roundOff = Number((rounded - total).toFixed(2));
    return {
        total,
        rounded,
        roundOff
    };
});

const totalDiscount = computed(() => {
    return (props.invoice.items || []).reduce((acc: number, item: any) => acc + (Number(item.discount) || 0), 0);
});

const shouldShowShipping = computed(() => {
    return displayOpts.value.show_shipping_address && props.invoice.party?.shipping_address?.street
});

const headerGridClass = computed(() => {
    let cols = 1; // Bill To is always there
    if (shouldShowShipping.value) cols++;
    if (displayOpts.value.show_eway_details) cols++;

    // If we have 3 columns, use grid-cols-3
    // If we have 2 columns, use grid-cols-2
    // If 1 (unlikely to look good but safe), grid-cols-1
    if (cols === 3) return 'grid-cols-3';
    if (cols === 2) return 'grid-cols-2';
    return 'grid-cols-1';
});

const pages = computed(() => {
    // 0. Pre-process items to attaching GLOBAL INDEX
    const allItems = (props.invoice.items || []).map((item: any, totalIndex: number) => ({
        ...item,
        globalIndex: totalIndex + 1
    }));

    const _pages = [];

    // 1. First Page Chunk
    const firstChunk = allItems.slice(0, MAX_ITEMS_FIRST);
    _pages.push({
        items: firstChunk,
        isLastPage: allItems.length <= MAX_ITEMS_FIRST,
        hasSpaceForTotals: (allItems.length <= (MAX_ITEMS_FIRST - TOTALS_SPACE_ITEMS)),
        isTotalsOnly: false
    });

    let remainingItems = allItems.slice(MAX_ITEMS_FIRST);

    // 2. Middle Pages Chunks
    while (remainingItems.length > 0) {
        const chunk = remainingItems.slice(0, MAX_ITEMS_STD);
        remainingItems = remainingItems.slice(MAX_ITEMS_STD);

        const isLast = remainingItems.length === 0;
        const hasSpace = chunk.length <= (MAX_ITEMS_STD - TOTALS_SPACE_ITEMS);

        _pages.push({
            items: chunk,
            isLastPage: isLast,
            hasSpaceForTotals: isLast && hasSpace,
            isTotalsOnly: false
        });
    }

    // 3. Check if we need a dedicated Totals Page
    const lastPage = _pages[_pages.length - 1];
    if (lastPage && lastPage.isLastPage && !lastPage.hasSpaceForTotals) {
        _pages.push({
            items: [], // No items, just totals
            isLastPage: true,
            hasSpaceForTotals: true,
            isTotalsOnly: true
        });
    }

    return _pages;
});

const isTaxDocument = computed(() => {
    // Only these types should show Tax Breakdowns and Tax Columns
    const taxTypes = ['invoice', 'tax_invoice', 'credit_note', 'debit_note'];
    return taxTypes.includes(props.invoice.type);
});

const complianceText = computed(() => {
    if (!isTaxDocument.value) {
        if (props.invoice.type === 'bill_of_supply') return ''; // Usually just title is enough, or specific composition text if needed
        if (props.invoice.type === 'delivery_challan') return 'NOT FOR SALE';
        if (['quote', 'proforma_invoice', 'estimate'].includes(props.invoice.type)) return 'THIS IS NOT A TAX INVOICE';
    }
    return '';
});

const documentTitle = computed(() => {
    switch (props.invoice.type) {
        case 'proforma_invoice': return 'PROFORMA INVOICE';
        case 'quote': return 'ESTIMATE';
        case 'credit_note': return 'CREDIT NOTE';
        case 'debit_note': return 'DEBIT NOTE';
        case 'delivery_challan': return 'DELIVERY CHALLAN';
        case 'bill_of_supply': return 'BILL OF SUPPLY';
        default: return 'TAX INVOICE';
    }
});


</script>

<style scoped>
/* Strict A4 Dimensions */
.a4-page {
    width: 210mm;
    height: 297mm;
    overflow: hidden;
    /* Strict clip */
}

@media print {
    .print-layout-container {
        padding: 0;
        margin: 0;
        background: white;
    }

    .a4-page {
        margin: 0;
        box-shadow: none;
        border: none;
        /* Force page break after each chunk */
        break-after: page;
        page-break-after: always;
    }

    /* Remove break from the very last page just in case, though usually browser handles empty last page fine */
    .a4-page:last-child {
        break-after: auto;
        page-break-after: auto;
    }
}
</style>
