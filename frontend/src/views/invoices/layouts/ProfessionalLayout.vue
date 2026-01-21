<template>
    <div id="invoice-paper"
        class="bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-sm p-0 print:shadow-none print:ring-0 text-sm font-sans flex flex-col"
        style="min-height: 297mm; width: 210mm; margin: auto;">

        <!-- Top Colored Bar -->
        <div class="h-2 w-full" :style="{ backgroundColor: invoice.business?.meta?.brand_color || '#1f2937' }"></div>

        <!-- Header Section -->
        <div class="px-8 py-6 flex justify-between items-start border-b border-gray-100">
            <div class="flex flex-col gap-4 max-w-[60%]">
                <!-- Logo & Name -->
                <div class="flex items-center gap-4">
                    <div v-if="invoice.business?.meta?.logo_url" class="h-16 w-16 flex-shrink-0">
                        <img :src="invoice.business.meta.logo_url" class="h-full w-full object-contain" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-tight leading-none"
                            :style="{ color: invoice.business?.meta?.brand_color || '#111827' }">{{
                                invoice.business?.name }}</h1>
                        <p v-if="invoice.business?.gstin" class="text-xs font-semibold text-gray-500 mt-1">GSTIN: {{
                            invoice.business.gstin }}</p>
                    </div>
                </div>

                <!-- Business Details -->
                <div class="text-xs text-gray-500 leading-relaxed mt-2 pl-1 border-l-2 border-gray-100">
                    <p class="font-medium text-gray-900">{{ invoice.business?.address }}</p>
                    <p v-if="invoice.business?.meta?.city || invoice.business?.meta?.state">
                        {{ [invoice.business?.meta?.city, invoice.business?.meta?.state,
                        invoice.business?.meta?.pincode].filter(Boolean).join(', ') }}
                    </p>
                    <div class="flex gap-4 mt-1">
                        <p v-if="invoice.business?.meta?.mobile">M: {{ invoice.business.meta.mobile }}</p>
                        <p v-if="invoice.business?.website">W: {{ invoice.business.website }}</p>
                        <p v-if="invoice.business?.meta?.email">E: {{ invoice.business.meta.email }}</p>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <h2 class="text-4xl font-black text-gray-200 uppercase tracking-wide select-none">
                    {{ getDocumentTitle() }}
                </h2>
                <!-- Copy Type -->
                <p v-if="copyType && (invoice.type === 'invoice' || invoice.type === 'tax_invoice' || invoice.type === 'bill_of_supply')"
                    class="text-xs font-bold text-gray-500 uppercase mt-1">
                    ({{ copyLabel }})
                </p>
                <p v-if="complianceText"
                    class="text-xs font-bold text-gray-500 uppercase mt-1 border px-2 py-0.5 inline-block">
                    {{ complianceText }}
                </p>
                <div class="mt-4 space-y-1">
                    <div class="flex justify-end gap-3 items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase">{{ invoice.type === 'credit_note' ? 'CN'
                            : 'Invoice' }} No</span>
                        <span class="font-mono font-bold text-lg text-gray-900">{{ invoice.invoice_number }}</span>
                    </div>
                    <div class="flex justify-end gap-3 items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase">Date</span>
                        <span class="font-medium text-gray-900">{{ formatDate(invoice.date) }}</span>
                    </div>
                    <div v-if="invoice.due_date && invoice.type !== 'credit_note'"
                        class="flex justify-end gap-3 items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase">Due Date</span>
                        <span class="font-medium text-red-600">{{ formatDate(invoice.due_date) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client & Shipping Info (Grey Block) -->
        <div class="bg-gray-50 px-8 py-6 grid grid-cols-2 print:grid-cols-2 gap-12 border-b border-gray-100">
            <!-- Bill To -->
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bill To</h3>
                <p class="font-bold text-base text-gray-900">{{ invoice.party?.name }}</p>
                <div class="text-xs text-gray-600 mt-1 leading-relaxed">
                    <template v-if="invoice.meta?.billing_address?.street || invoice.meta?.billing_address?.city">
                        <p>{{ invoice.meta.billing_address.street }}</p>
                        <p>{{ invoice.meta.billing_address.city }} {{ invoice.meta.billing_address.zip }}</p>
                        <p>{{ invoice.meta.billing_address.state }}</p>
                    </template>
                    <template v-else>
                        <p>{{ invoice.party?.billing_address?.street }}</p>
                        <p>{{ invoice.party?.billing_address?.city }} {{ invoice.party?.billing_address?.zip }}</p>
                        <p>{{ invoice.party?.billing_address?.state }}</p>
                    </template>
                    <p v-if="invoice.party?.gstin" class="mt-2 text-gray-900"><span
                            class="font-semibold text-gray-500">GSTIN:</span> {{
                                invoice.party.gstin }}</p>
                </div>
            </div>

            <!-- Ship To / Transport -->
            <div class="relative pl-8 border-l border-gray-200">
                <template v-if="invoice.meta?.display_options?.show_shipping_address">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Ship To</h3>
                    <div class="text-xs text-gray-600 leading-relaxed mb-6">
                        <template v-if="invoice.meta?.shipping_address?.street || invoice.meta?.shipping_address?.city">
                            <p>{{ invoice.meta.shipping_address.street }}</p>
                            <p>{{ invoice.meta.shipping_address.city }} {{ invoice.meta.shipping_address.zip }}</p>
                            <p>{{ invoice.meta.shipping_address.state }}</p>
                        </template>
                        <template v-else-if="invoice.party?.shipping_address">
                            <p>{{ invoice.party.shipping_address.street }}</p>
                            <p>{{ invoice.party.shipping_address.city }} {{ invoice.party.shipping_address.zip }}</p>
                            <p>{{ invoice.party.shipping_address.state }}</p>
                        </template>
                    </div>
                </template>

                <template v-if="invoice.meta?.display_options?.show_eway_details">
                    <div class="grid grid-cols-2 print:grid-cols-2 gap-x-8 gap-y-4 mt-2">
                        <div v-if="invoice.vehicle_no">
                            <p class="text-[10px] uppercase text-gray-400 font-bold mb-0.5">Vehicle No</p>
                            <p class="text-xs font-mono font-medium text-gray-900">{{ invoice.vehicle_no }}</p>
                        </div>
                        <div v-if="invoice.eway_bill_no">
                            <p class="text-[10px] uppercase text-gray-400 font-bold mb-0.5">E-Way Bill</p>
                            <p class="text-xs font-mono font-medium text-gray-900">{{ invoice.eway_bill_no }}</p>
                        </div>
                        <div v-if="invoice.po_number">
                            <p class="text-[10px] uppercase text-gray-400 font-bold mb-0.5">PO No</p>
                            <p class="text-xs font-mono font-medium text-gray-900">{{ invoice.po_number }}</p>
                        </div>
                    </div>
                </template>
                <div class="absolute top-0 right-0">
                    <p class="text-[10px] text-gray-400 uppercase text-right mx-1">Place of Supply</p>
                    <p class="text-xs font-bold text-gray-900 text-right mx-1">{{ taxBreakdown.posState }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="flex-grow px-8 py-4">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-gray-500 uppercase border-b border-gray-200"
                        :style="{ color: invoice.business?.meta?.brand_color || '#6b7280' }">
                        <th class="py-3 text-left font-bold w-12 pl-2">#</th>
                        <th class="py-3 text-left font-bold pl-2">Item / Notes</th>
                        <th v-if="invoice.meta?.display_options?.show_hsn" class="py-3 text-center font-bold w-20">HSN
                        </th>
                        <th class="py-3 text-right font-bold w-20">Qty</th>
                        <th class="py-3 text-right font-bold w-24">Rate</th>
                        <th v-if="invoice.meta?.display_options?.show_discount" class="py-3 text-right font-bold w-20">
                            Disc</th>

                        <!-- Dynamic Tax Headers -->
                        <template v-if="invoice.meta?.display_options?.show_gst_breakdown && isTaxDocument">
                            <template v-if="taxBreakdown.taxType === 'IGST'">
                                <th class="py-3 text-right font-bold w-24">IGST</th>
                            </template>
                            <template v-else>
                                <th class="py-3 text-right font-bold w-20">CGST</th>
                                <th class="py-3 text-right font-bold w-20">SGST</th>
                            </template>
                        </template>

                        <th class="py-3 text-right font-bold w-28">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="(item, index) in invoice.items" :key="item.id">
                        <td class="py-3 px-2 text-gray-400 align-top">{{ Number(index) + 1 }}</td>
                        <td class="py-3 px-2 font-medium text-gray-900 align-top">
                            <template v-if="item.name">
                                <div class="font-bold">{{ item.name }}</div>
                                <div v-if="item.description && invoice.meta?.display_options?.show_description"
                                    class="text-[10px] text-gray-500 font-normal mt-0.5 whitespace-pre-line">{{
                                        item.description }}</div>
                            </template>
                            <template v-else>
                                {{ item.description }}
                            </template>
                        </td>
                        <td v-if="invoice.meta?.display_options?.show_hsn"
                            class="py-3 px-1 text-center text-gray-500 align-top">{{ item.hsn_code || '-' }}</td>
                        <td class="py-3 px-1 text-right text-gray-600 align-top">{{ Number(item.quantity) }}</td>
                        <td class="py-3 px-1 text-right text-gray-600 align-top">{{ formatCurrency(item.unit_price) }}
                        </td>
                        <td v-if="invoice.meta?.display_options?.show_discount"
                            class="py-3 px-1 text-right text-red-500 align-top">{{ Number(item.discount) ? '-' +
                                Number(item.discount) : '-' }}</td>

                        <!-- Dynamic Tax Columns -->
                        <template v-if="invoice.meta?.display_options?.show_gst_breakdown && isTaxDocument">
                            <template v-if="taxBreakdown.taxType === 'IGST'">
                                <td class="py-3 px-1 text-right text-gray-500 align-top">
                                    <div class="text-[10px]">{{ Number(item.tax_rate) }}%</div>
                                    <div>{{ Number(item.tax_amount).toFixed(2) }}</div>
                                </td>
                            </template>
                            <template v-else>
                                <td class="py-3 px-1 text-right text-gray-500 align-top">
                                    <div class="text-[10px]">{{ Number(item.tax_rate) / 2 }}%</div>
                                    <div>{{ (Number(item.tax_amount) / 2).toFixed(2) }}</div>
                                </td>
                                <td class="py-3 px-1 text-right text-gray-500 align-top">
                                    <div class="text-[10px]">{{ Number(item.tax_rate) / 2 }}%</div>
                                    <div>{{ (Number(item.tax_amount) / 2).toFixed(2) }}</div>
                                </td>
                            </template>
                        </template>

                        <td class="py-3 px-1 text-right font-bold text-gray-900 align-top">{{ formatCurrency(item.total)
                        }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Section -->
        <div class="mt-auto bg-gray-50 p-6 border-t border-gray-100">
            <div class="flex gap-10">
                <!-- Left Column: Payment & Terms -->
                <div class="flex-grow space-y-4">
                    <!-- Amount Words -->
                    <div class="border-l-2 border-gray-300 pl-3">
                        <p class="text-[10px] text-gray-500 uppercase font-bold">Amount In Words</p>
                        <p class="text-sm font-bold text-gray-900 capitalize">{{ amountInWords(finalTotals.rounded) }}
                            Only</p>
                    </div>

                    <!-- Payment Details (Simplified) -->
                    <div v-if="invoice.meta?.display_options?.show_qr_bank_details && invoice.type !== 'credit_note' && (qrCodeUrl || invoice.business?.meta?.upi_id || invoice.business?.bank_name)"
                        class="flex gap-4 items-start pt-2">
                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest py-1">Pay To:</h4>

                        <!-- QR Code -->
                        <div v-if="qrCodeUrl" class="shrink-0 group">
                            <img :src="qrCodeUrl" class="h-14 w-14 object-contain mix-blend-multiply" />
                        </div>

                        <!-- Details -->
                        <div class="text-[10px] space-y-0.5 text-gray-600 pt-0.5">
                            <div v-if="invoice.business?.meta?.upi_id" class="mb-1">
                                <span class="font-mono font-bold text-gray-900 bg-gray-100 px-1 rounded">{{
                                    invoice.business.meta.upi_id }}</span>
                            </div>

                            <div v-if="invoice.business?.bank_name">
                                <p><span class="text-gray-400 w-12 inline-block">Bank</span> <span
                                        class="font-medium text-gray-900">{{ invoice.business.bank_name }}</span></p>
                                <p><span class="text-gray-400 w-12 inline-block">A/c No</span> <span
                                        class="font-medium text-gray-900">{{ invoice.business.account_number }}</span>
                                </p>
                                <p><span class="text-gray-400 w-12 inline-block">IFSC</span> <span
                                        class="font-medium text-gray-900">{{ invoice.business.ifsc_code }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-1">
                        <div v-if="invoice.notes || invoice.business?.meta?.default_notes">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Notes</h4>
                            <p class="text-[10px] text-gray-500 whitespace-pre-line leading-relaxed max-w-sm">
                                {{ invoice.notes || invoice.business?.meta?.default_notes }}
                            </p>
                        </div>

                        <div v-if="invoice.terms || invoice.business?.meta?.default_terms">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Terms</h4>
                            <p class="text-[10px] text-gray-500 whitespace-pre-line leading-relaxed max-w-sm">
                                {{ invoice.terms || invoice.business?.meta?.default_terms }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Totals & Signature -->
                <div class="w-64 shrink-0 flex flex-col justify-between">
                    <!-- Totals -->
                    <div class="space-y-2 text-right text-xs">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span>{{ formatCurrency(invoice.subtotal) }}</span>
                        </div>
                        <div v-if="invoice.meta?.display_options?.show_discount && totalDiscount > 0"
                            class="flex justify-between text-red-500">
                            <span>Discount</span>
                            <span>-{{ formatCurrency(totalDiscount) }}</span>
                        </div>

                        <!-- Tax Breakdown -->
                        <div v-if="invoice.meta?.display_options?.show_gst_breakdown && isTaxDocument"
                            class="border-b border-gray-100 pb-2">
                            <template v-if="taxBreakdown.taxType === 'IGST'">
                                <div class="flex justify-between text-gray-500">
                                    <span>IGST</span>
                                    <span>{{ formatCurrency(taxBreakdown.igst) }}</span>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex justify-between text-gray-500">
                                    <span>CGST</span>
                                    <span>{{ formatCurrency(taxBreakdown.cgst) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-500">
                                    <span>SGST</span>
                                    <span>{{ formatCurrency(taxBreakdown.sgst) }}</span>
                                </div>
                            </template>
                        </div>

                        <!-- Round Off -->
                        <div v-if="finalTotals.roundOff !== 0" class="flex justify-between text-gray-500">
                            <span>Round Off</span>
                            <span>{{ formatCurrency(finalTotals.roundOff) }}</span>
                        </div>

                        <!-- Grand Total -->
                        <div class="flex justify-between items-center pt-2">
                            <span class="font-bold text-gray-900 text-lg">Total</span>
                            <span class="font-bold text-gray-900 text-lg">{{ formatCurrency(finalTotals.rounded)
                            }}</span>
                        </div>
                    </div>

                    <!-- Signature -->
                    <div class="mt-8 text-right">
                        <div class="h-12"></div>
                        <p class="font-bold text-xs text-gray-900">{{ invoice.business?.name }}</p>
                        <p class="text-[9px] text-gray-400 uppercase tracking-widest">Authorized Signatory</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mandatory Branding -->
        <div v-if="shouldShowBranding" class="mt-auto pt-4 pb-2 text-center opacity-70">
            <span class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">Powered directly by <span
                    class="text-gray-600 font-bold">Vedant Billing</span></span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '../../../stores/auth'
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

const authStore = useAuthStore()
const isTaxDocument = computed(() => {
    const taxTypes = ['invoice', 'tax_invoice', 'credit_note', 'debit_note'];
    return taxTypes.includes(props.invoice.type);
});

const complianceText = computed(() => {
    if (!isTaxDocument.value) {
        if (props.invoice.type === 'bill_of_supply') return '';
        if (props.invoice.type === 'delivery_challan') return 'NOT FOR SALE';
        if (['quote', 'proforma_invoice', 'estimate'].includes(props.invoice.type)) return 'THIS IS NOT A TAX INVOICE';
    }
    return '';
});

const shouldShowBranding = computed(() => {
    const slug = authStore.currentSubscription?.plan?.slug
    return !slug || ['free', 'starter'].includes(slug)
})

const getDocumentTitle = () => {
    switch (props.invoice.type) {
        case 'bill_of_supply': return 'BILL OF SUPPLY'
        case 'proforma_invoice': return 'PROFORMA INVOICE'
        case 'quote': return 'ESTIMATE'
        case 'delivery_challan': return 'DELIVERY CHALLAN'
        case 'credit_note': return 'CREDIT NOTE'
        case 'debit_note': return 'DEBIT NOTE'
        case 'tax_invoice': return 'TAX INVOICE'
        default: return 'INVOICE'
    }
}

</script>
