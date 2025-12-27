<template>
    <div id="invoice-paper"
        class="bg-white shadow-lg ring-1 ring-gray-900/5 sm:rounded-xl p-8 print:shadow-none print:ring-0 font-sans text-gray-900 flex flex-col justify-between"
        style="height: 297mm; width: 210mm; margin: auto;">

        <!-- Clean Minimal Header -->
        <div class="flex justify-between items-start border-b border-gray-100 pb-4">
            <div class="flex gap-5">
                <div v-if="invoice.business?.meta?.logo_url"
                    class="h-16 w-16 bg-gray-50 rounded-lg flex items-center justify-center overflow-hidden border border-gray-100">
                    <img :src="invoice.business.meta.logo_url" class="h-full w-full object-contain" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ invoice.business?.name }}</h1>
                    <div class="mt-2 text-sm text-gray-500 space-y-0.5">
                        <p class="whitespace-pre-line">{{ invoice.business?.address }}</p>
                        <p v-if="invoice.business?.meta?.city || invoice.business?.meta?.state">
                            {{ [invoice.business?.meta?.city, invoice.business?.meta?.state,
                            invoice.business?.meta?.pincode].filter(Boolean).join(', ') }}
                        </p>
                        <p v-if="invoice.business?.gstin" class="mt-1 font-medium text-gray-700">GSTIN: {{
                            invoice.business.gstin }}</p>
                        <div class="flex gap-3 mt-1">
                            <p v-if="invoice.business?.meta?.mobile">Ph: {{ invoice.business.meta.mobile }}</p>
                            <p v-if="invoice.business?.website">{{ invoice.business.website }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <span
                    class="inline-block px-3 py-1 bg-gray-100 text-gray-600 rounded text-xs font-bold uppercase tracking-wide mb-2">{{
                        invoice.type === 'credit_note' ? 'CREDIT NOTE' : (invoice.type === 'quote' ? 'ESTIMATE' : 'INVOICE')
                    }}</span>
                <h2 class="text-xl font-mono font-bold text-gray-900">{{ invoice.invoice_number }}</h2>
                <div class="text-sm text-gray-500 mt-2 space-y-1">
                    <p>Date: <span class="font-medium text-gray-900">{{ formatDate(invoice.date) }}</span></p>
                    <p v-if="invoice.type !== 'credit_note'">Due: <span class="font-medium text-gray-900">{{
                        formatDate(invoice.due_date) }}</span></p>
                </div>
            </div>
        </div>

        <!-- Clients & Logistics -->
        <div class="grid grid-cols-2 gap-12 py-4">
            <!-- Bill To -->
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bill To</h3>
                <div class="text-sm">
                    <p class="font-bold text-gray-900 text-base mb-1">{{ invoice.party?.name }}</p>
                    <div class="text-gray-600 leading-relaxed">
                        <!-- Prioritize Snapshot Address -->
                        <template v-if="safeMeta.billing_address?.street || safeMeta.billing_address?.city">
                            <p>{{ safeMeta.billing_address.street }}</p>
                            <p>{{ safeMeta.billing_address.city }} {{ safeMeta.billing_address.state }} - {{
                                safeMeta.billing_address.zip }}</p>
                        </template>
                        <template v-else>
                            <p>{{ invoice.party?.billing_address?.street }}</p>
                            <p>{{ invoice.party?.billing_address?.city }} {{ invoice.party?.billing_address?.state }} -
                                {{ invoice.party?.billing_address?.zip }}</p>
                        </template>
                        <p v-if="invoice.party?.gstin" class="mt-2 font-medium text-gray-900">GSTIN: {{
                            invoice.party.gstin }}</p>
                    </div>
                </div>
            </div>

            <!-- Ship To (Conditional) -->
            <div v-if="shouldShowShipping">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Ship To</h3>
                <div class="text-sm text-gray-600 leading-relaxed">
                    <p class="font-medium text-gray-900 mb-1">{{ invoice.party?.name }}</p>
                    <template v-if="safeMeta.shipping_address?.street || safeMeta.shipping_address?.city">
                        <p>{{ safeMeta.shipping_address.street }}</p>
                        <p>{{ safeMeta.shipping_address.city }} {{ safeMeta.shipping_address.state }} - {{
                            safeMeta.shipping_address.zip }}</p>
                    </template>
                    <template v-else>
                        <p>{{ invoice.party?.shipping_address?.street }}</p>
                        <p>{{ invoice.party?.shipping_address?.city }} {{ invoice.party?.shipping_address?.state }} - {{
                            invoice.party?.shipping_address?.zip }}</p>
                    </template>
                </div>
            </div>

            <!-- Transport Details (If Shipping hidden or as extra info) -->
            <div v-else-if="displayOpts.show_eway_details" class="text-sm text-gray-600 bg-gray-50 p-2 rounded-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Transport Details</h3>
                <div class="space-y-1">
                    <p v-if="invoice.eway_bill_no"><span class="w-24 inline-block text-gray-500">E-Way No:</span> {{
                        invoice.eway_bill_no }}</p>
                    <p v-if="invoice.vehicle_no"><span class="w-24 inline-block text-gray-500">Vehicle:</span> {{
                        invoice.vehicle_no
                    }}</p>
                    <p v-if="invoice.po_number"><span class="w-24 inline-block text-gray-500">PO No:</span> {{
                        invoice.po_number }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Transport Details Row (If shipping is shown AND eway details enabled, show here) -->
        <div v-if="shouldShowShipping && displayOpts.show_eway_details"
            class="mb-4 p-2 bg-gray-50 rounded-lg border border-gray-100 grid grid-cols-2 md:grid-cols-4 print:grid-cols-4 gap-2 text-sm">
            <div v-if="invoice.challan_no">
                <span class="block text-xs text-gray-500 uppercase">Challan No</span>
                <span class="font-medium text-gray-900">{{ invoice.challan_no }}</span>
            </div>
            <div v-if="invoice.eway_bill_no">
                <span class="block text-xs text-gray-500 uppercase">E-Way Bill</span>
                <span class="font-medium text-gray-900">{{ invoice.eway_bill_no }}</span>
            </div>
            <div v-if="invoice.vehicle_no">
                <span class="block text-xs text-gray-500 uppercase">Vehicle</span>
                <span class="font-medium text-gray-900">{{ invoice.vehicle_no }}</span>
            </div>
            <div v-if="invoice.po_number">
                <span class="block text-xs text-gray-500 uppercase">PO Number</span>
                <span class="font-medium text-gray-900">{{ invoice.po_number }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider pl-4 pr-2">#
                        </th>
                        <th class="py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider px-2">Item
                        </th>
                        <th v-if="displayOpts.show_hsn"
                            class="py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">HSN</th>
                        <th class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Qty</th>
                        <th class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Rate</th>
                        <th v-if="displayOpts.show_discount"
                            class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Disc</th>
                        <th v-if="displayOpts.show_gst_breakdown"
                            class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Tax</th>
                        <th class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider pr-4">Amount
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="(item, index) in invoice.items" :key="item.id">
                        <td class="py-2 pl-4 pr-2 text-sm text-gray-500">{{ Number(index) + 1 }}</td>
                        <td class="py-2 px-2 text-sm font-medium text-gray-900">
                            <template v-if="item.name">
                                <div class="font-bold">{{ item.name }}</div>
                                <div v-if="item.description && displayOpts.show_description"
                                    class="text-[10px] text-gray-500 font-normal mt-0.5 whitespace-pre-line">{{
                                        item.description }}</div>
                            </template>
                            <template v-else>
                                {{ item.description }}
                            </template>
                        </td>
                        <td v-if="displayOpts.show_hsn" class="py-2 text-sm text-gray-500">{{ item.hsn_code || '-' }}
                        </td>
                        <td class="py-2 text-sm text-gray-500 text-right">{{ Number(item.quantity) }}</td>
                        <td class="py-2 text-sm text-gray-500 text-right">{{ formatCurrency(item.unit_price) }}</td>
                        <td v-if="displayOpts.show_discount" class="py-2 text-sm text-gray-500 text-right">{{
                            Number(item.discount) ? formatCurrency(item.discount) : '-' }}</td>
                        <td v-if="displayOpts.show_gst_breakdown" class="py-2 text-sm text-gray-500 text-right">
                            {{ formatCurrency(item.tax_amount) }} <span class="text-xs text-gray-400">({{
                                Number(item.tax_rate)
                            }}%)</span>
                        </td>
                        <td class="py-2 pr-4 text-sm font-bold text-gray-900 text-right">{{ formatCurrency(item.total)
                        }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-8 border-t border-gray-100 pt-4">

            <!-- Left: Bank/QR/Words -->
            <div class="space-y-8">
                <!-- Amount in Words -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Amount in Words</h4>
                    <p class="text-sm font-medium text-gray-900 capitalize">{{ amountInWords(invoice.grand_total) }}
                        Only</p>
                </div>

                <!-- Bank Details -->
                <!-- Bank & QR Details -->
                <div v-if="displayOpts.show_qr_bank_details && invoice.type !== 'credit_note' && (qrCodeUrl || invoice.business?.meta?.upi_id || invoice.business?.bank_name)"
                    class="">
                    <!-- Removed bg/border/p-4 -->
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Payment Details</h4>

                    <div class="flex gap-4 items-start">
                        <!-- QR Code -->
                        <div v-if="qrCodeUrl" class="shrink-0">
                            <img :src="qrCodeUrl" alt="Pay via UPI"
                                class="w-24 h-24 object-contain border border-gray-100" />
                            <p class="text-[10px] text-center font-bold mt-1 text-gray-500">Scan to Pay</p>
                        </div>

                        <!-- Details -->
                        <div class="text-xs space-y-1 text-gray-600">
                            <div v-if="invoice.business?.meta?.upi_id" class="mb-2">
                                <span class="text-[10px] text-gray-500 uppercase font-bold">UPI ID:</span>
                                <span
                                    class="font-mono font-bold text-gray-900 ml-1 bg-gray-50 px-1 py-0.5 rounded border border-gray-200">{{
                                        invoice.business.meta.upi_id }}</span>
                            </div>

                            <div v-if="invoice.business?.bank_name" class="space-y-0.5">
                                <p><span class="font-medium text-gray-500 w-14 inline-block">Bank:</span> <span
                                        class="font-bold text-gray-900">{{ invoice.business.bank_name }}</span></p>
                                <p><span class="font-medium text-gray-500 w-14 inline-block">A/c:</span> <span
                                        class="font-bold text-gray-900">{{ invoice.business.account_number }}</span></p>
                                <p><span class="font-medium text-gray-500 w-14 inline-block">IFSC:</span> <span
                                        class="font-bold text-gray-900">{{ invoice.business.ifsc_code }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Summary (Signature moved out) -->
            <div class="flex flex-col justify-between">
                <!-- Totals Breakdown -->
                <div class="space-y-2 text-sm border-b border-gray-100 pb-6 w-full">
                    <div class="flex justify-between text-gray-600">
                        <span>Taxable Amount</span>
                        <span>{{ formatCurrency(invoice.subtotal) }}</span>
                    </div>

                    <div v-if="taxBreakdown.taxType === 'IGST'" class="flex justify-between text-gray-600">
                        <span>IGST</span>
                        <span>{{ formatCurrency(taxBreakdown.igst) }}</span>
                    </div>
                    <template v-else>
                        <div class="flex justify-between text-gray-600">
                            <span>CGST</span>
                            <span>{{ formatCurrency(taxBreakdown.cgst) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>SGST</span>
                            <span>{{ formatCurrency(taxBreakdown.sgst) }}</span>
                        </div>
                    </template>

                    <div class="flex justify-between text-lg font-bold text-gray-900 pt-4">
                        <span>Grand Total</span>
                        <span>{{ formatCurrency(invoice.grand_total) }}</span>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">
                        POS: {{ taxBreakdown.posState }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes & Terms/Signature/Branding Footer -->
        <div class="mt-auto">
            <!-- Notes & Terms -->
            <div v-if="invoice.notes || invoice.terms"
                class="grid grid-cols-2 gap-8 border-t border-gray-100 pt-2 mt-4">
                <div>
                    <h4 v-if="invoice.notes" class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                        Notes
                    </h4>
                    <p v-if="invoice.notes" class="text-[10px] text-gray-500 leading-relaxed whitespace-pre-line">{{
                        invoice.notes }}</p>
                </div>
                <div>
                    <h4 v-if="invoice.terms" class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                        Terms &
                        Conditions</h4>
                    <p v-if="invoice.terms" class="text-[10px] text-gray-500 leading-relaxed whitespace-pre-line">{{
                        invoice.terms }}</p>
                </div>
            </div>

            <!-- Signature -->
            <div class="mt-8 text-right">
                <div class="inline-block text-center">
                    <p class="font-bold text-sm text-gray-900">{{ invoice.business?.name }}</p>
                    <div class="h-12"></div>
                    <p
                        class="text-[10px] text-gray-500 uppercase tracking-wide border-t border-gray-300 pt-2 inline-block min-w-[150px]">
                        Authorized Signatory</p>
                </div>
            </div>

            <!-- Mandatory Branding -->
            <div v-if="shouldShowBranding"
                class="mt-4 pt-2 border-t border-gray-100 flex justify-center items-center gap-1.5 print:opacity-100">
                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">Powered directly by</span>
                <span class="text-xs font-bold text-gray-900">BillingBook</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '../../../stores/auth';

const props = defineProps<{
    invoice: any,
    taxBreakdown: any,
    qrCodeUrl: string
}>()

const authStore = useAuthStore()
const shouldShowBranding = computed(() => {
    const slug = authStore.currentSubscription?.plan?.slug
    return !slug || ['free', 'starter'].includes(slug)
})


// Safe access to meta to prevent "not coming" issues if meta is null/undefined
const safeMeta = computed(() => {
    return props.invoice.meta || {}
})

// Safe display options with defaults
const displayOpts = computed(() => {
    const opts = safeMeta.value.display_options || {}
    return {
        show_eway_details: opts.show_eway_details ?? false,
        show_hsn: opts.show_hsn ?? true, // Default true
        show_gst_breakdown: opts.show_gst_breakdown ?? true, // Default true
        show_discount: opts.show_discount ?? false,
        show_qr_bank_details: opts.show_qr_bank_details ?? true,
        show_shipping_address: opts.show_shipping_address ?? false,
        show_description: opts.show_description ?? true
    }
})

const shouldShowShipping = computed(() => {
    const enabled = displayOpts.value.show_shipping_address;

    // Check if we have data in meta OR in party
    const metaShip = safeMeta.value.shipping_address;
    const partyShip = props.invoice.party?.shipping_address;

    const hasMeta = metaShip && (metaShip.street || metaShip.city);
    const hasParty = partyShip && (partyShip.street || partyShip.city);

    return enabled && (hasMeta || hasParty);
})



const formatCurrency = (value: number | string) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(Number(value))
}

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const amountInWords = (num: number) => {
    if (!num) return 'Zero';

    const a = ['', 'one ', 'two ', 'three ', 'four ', 'five ', 'six ', 'seven ', 'eight ', 'nine ', 'ten ', 'eleven ', 'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', 'seventeen ', 'eighteen ', 'nineteen '];
    const b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    const inWords = (nStr: any): string => {
        let n: any = nStr.toString();
        if (n.length > 9) return 'overflow';
        // @ts-ignore
        n = ('000000000' + n).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
        // @ts-ignore
        if (!n) return; var str = '';
        // @ts-ignore
        str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'crore ' : '';
        // @ts-ignore
        str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'lakh ' : '';
        // @ts-ignore
        str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'thousand ' : '';
        // @ts-ignore
        str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'hundred ' : '';
        // @ts-ignore
        str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) : '';
        return str;
    }

    const wholePart = Math.floor(num);
    const decimalPart = Math.round((num - wholePart) * 100);

    let result = inWords(wholePart);
    if (decimalPart > 0) {
        result += ' Rupees and ' + inWords(decimalPart) + ' Paise';
    } else {
        result += ' Rupees'; // Actually usually standard is "Rupees ... Only" if no paise
    }

    return result;
}
</script>

<style scoped>
@media print {
    @page {
        size: A4;
        margin: 0;
    }

    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #invoice-paper {
        box-shadow: none;
        margin: 0;
        width: 210mm;
        height: 297mm;
        max-height: 297mm;
        overflow: hidden;
        transform: scale(0.96);
        transform-origin: top center;
    }
}
</style>
