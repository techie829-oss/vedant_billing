<template>
    <div id="invoice-paper"
        class="bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-sm p-0 print:shadow-none print:ring-0 text-sm font-sans flex flex-col"
        style="min-height: 297mm; width: 210mm; margin: auto;">

        <!-- Top Brand Bar -->
        <div class="h-2 w-full" :style="{ backgroundColor: invoice.business?.meta?.brand_color || '#1f2937' }"></div>

        <!-- Main Container with Border -->
        <div class="flex-grow flex flex-col p-8">
            <div class="border-2 border-gray-800 flex-grow flex flex-col">

                <!-- Header Row -->
                <div class="flex border-b-2 border-gray-800">
                    <div class="w-1/2 p-4 border-r-2 border-gray-800">
                        <div v-if="invoice.business?.meta?.logo_url" class="h-12 w-auto mb-3">
                            <img :src="invoice.business.meta.logo_url" class="h-full object-contain" />
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 uppercase leading-none">{{ invoice.business?.name }}
                        </h1>
                        <div class="text-xs text-gray-600 mt-2 space-y-0.5">
                            <p class="font-medium text-gray-900">{{ invoice.business?.address }}</p>
                            <p>{{ [invoice.business?.meta?.city, invoice.business?.meta?.state,
                            invoice.business?.meta?.pincode].filter(Boolean).join(', ') }}</p>
                            <p v-if="invoice.business?.gstin" class="font-bold mt-1">GSTIN: {{ invoice.business.gstin }}
                            </p>
                            <div class="flex flex-wrap gap-x-3 mt-1 text-[11px]">
                                <span v-if="invoice.business?.meta?.email">E: {{ invoice.business.meta.email }}</span>
                                <span v-if="invoice.business?.meta?.mobile">M: {{ invoice.business.meta.mobile }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-1/2 flex flex-col">
                        <div class="flex-grow p-4 bg-gray-50 flex flex-col justify-center items-end">
                            <h2
                                class="text-2xl font-black text-gray-900 uppercase tracking-widest border-b-2 border-gray-900 pb-1 mb-2">
                                {{ invoice.type === 'credit_note' ? 'CREDIT NOTE' : (invoice.type === 'quote' ?
                                    'ESTIMATE' : 'TAX INVOICE') }}</h2>
                            <div class="text-right space-y-1 w-full">
                                <div class="flex justify-between items-center gap-4">
                                    <span class="text-xs font-bold uppercase text-gray-500">Invoice No</span>
                                    <span class="font-mono font-bold text-lg">{{ invoice.invoice_number }}</span>
                                </div>
                                <div class="flex justify-between items-center gap-4">
                                    <span class="text-xs font-bold uppercase text-gray-500">Date</span>
                                    <span class="font-medium">{{ formatDate(invoice.date) }}</span>
                                </div>
                                <div v-if="invoice.due_date" class="flex justify-between items-center gap-4">
                                    <span class="text-xs font-bold uppercase text-gray-500">Due Date</span>
                                    <span class="font-medium text-red-600">{{ formatDate(invoice.due_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Row -->
                <div class="flex border-b-2 border-gray-800">
                    <!-- Bill To -->
                    <div class="w-1/2 p-4 border-r-2 border-gray-800">
                        <h3
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 border-b border-gray-300 pb-1 w-max">
                            Bill To</h3>
                        <p class="font-bold text-gray-900">{{ invoice.party?.name }}</p>
                        <div class="text-xs text-gray-600 mt-1 leading-relaxed">
                            <template
                                v-if="invoice.meta?.billing_address?.street || invoice.meta?.billing_address?.city">
                                <p>{{ invoice.meta.billing_address.street }}</p>
                                <p>{{ invoice.meta.billing_address.city }} {{ invoice.meta.billing_address.zip }}</p>
                                <p>{{ invoice.meta.billing_address.state }}</p>
                            </template>
                            <template v-else>
                                <p>{{ invoice.party?.billing_address?.street }}</p>
                                <p>{{ invoice.party?.billing_address?.city }} {{ invoice.party?.billing_address?.zip }}
                                </p>
                                <p>{{ invoice.party?.billing_address?.state }}</p>
                            </template>
                            <p v-if="invoice.party?.gstin" class="mt-2 font-bold text-gray-900">GSTIN: {{
                                invoice.party.gstin }}</p>
                        </div>
                    </div>
                    <!-- Ship To -->
                    <div class="w-1/2 p-4">
                        <div class="flex justify-between items-start mb-2 border-b border-gray-300 pb-1">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ship To</h3>
                            <div class="text-[10px] text-right">
                                <span class="block text-gray-400 uppercase">Place of Supply</span>
                                <span class="font-bold text-gray-900">{{ taxBreakdown.posState }}</span>
                            </div>
                        </div>

                        <template v-if="invoice.meta?.display_options?.show_shipping_address">
                            <div class="text-xs text-gray-600 leading-relaxed">
                                <template
                                    v-if="invoice.meta?.shipping_address?.street || invoice.meta?.shipping_address?.city">
                                    <p>{{ invoice.meta.shipping_address.street }}</p>
                                    <p>{{ invoice.meta.shipping_address.city }} {{ invoice.meta.shipping_address.zip }}
                                    </p>
                                    <p>{{ invoice.meta.shipping_address.state }}</p>
                                </template>
                                <template v-else-if="invoice.party?.shipping_address">
                                    <p>{{ invoice.party.shipping_address.street }}</p>
                                    <p>{{ invoice.party.shipping_address.city }} {{ invoice.party.shipping_address.zip
                                    }}</p>
                                    <p>{{ invoice.party.shipping_address.state }}</p>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Transport Row -->
                <div v-if="invoice.meta?.display_options?.show_eway_details"
                    class="flex border-b-2 border-gray-800 bg-gray-50 text-xs">
                    <div class="flex-1 p-2 border-r border-gray-300 text-center">
                        <span class="block text-[10px] uppercase text-gray-500 font-bold">Vehicle No</span>
                        <span class="font-mono font-bold">{{ invoice.vehicle_no || '-' }}</span>
                    </div>
                    <div class="flex-1 p-2 border-r border-gray-300 text-center">
                        <span class="block text-[10px] uppercase text-gray-500 font-bold">E-Way Bill No</span>
                        <span class="font-mono font-bold">{{ invoice.eway_bill_no || '-' }}</span>
                    </div>
                    <div class="flex-1 p-2 text-center">
                        <span class="block text-[10px] uppercase text-gray-500 font-bold">PO Number</span>
                        <span class="font-mono font-bold">{{ invoice.po_number || '-' }}</span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="flex-grow">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="py-2 px-3 text-center w-10 border-r border-gray-600">#</th>
                                <th class="py-2 px-3 text-left border-r border-gray-600">Items</th>
                                <th v-if="invoice.meta?.display_options?.show_hsn"
                                    class="py-2 px-3 text-center w-20 border-r border-gray-600">HSN</th>
                                <th class="py-2 px-3 text-right w-16 border-r border-gray-600">Qty</th>
                                <th class="py-2 px-3 text-right w-24 border-r border-gray-600">Rate</th>
                                <th v-if="invoice.meta?.display_options?.show_discount"
                                    class="py-2 px-3 text-right w-20 border-r border-gray-600">Disc.</th>

                                <template v-if="invoice.meta?.display_options?.show_gst_breakdown">
                                    <template v-if="taxBreakdown.taxType === 'IGST'">
                                        <th class="py-2 px-3 text-right w-24 border-r border-gray-600">IGST</th>
                                    </template>
                                    <template v-else>
                                        <th class="py-2 px-3 text-right w-20 border-r border-gray-600">CGST</th>
                                        <th class="py-2 px-3 text-right w-20 border-r border-gray-600">SGST</th>
                                    </template>
                                </template>

                                <th class="py-2 px-3 text-right w-28">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in invoice.items" :key="item.id" class="border-b border-gray-300">
                                <td class="py-3 px-3 text-center border-r border-gray-800">{{ Number(index) + 1 }}</td>
                                <td class="py-3 px-3 border-r border-gray-800">
                                    <template v-if="item.name">
                                        <div class="font-bold text-gray-900">{{ item.name }}</div>
                                        <div v-if="item.description && invoice.meta?.display_options?.show_description"
                                            class="text-[10px] text-gray-500 font-normal mt-0.5 whitespace-pre-line">{{
                                                item.description }}</div>
                                    </template>
                                    <template v-else>
                                        <p class="font-bold text-gray-900">{{ item.description }}</p>
                                    </template>
                                </td>
                                <td v-if="invoice.meta?.display_options?.show_hsn"
                                    class="py-3 px-3 text-center border-r border-gray-800">{{ item.hsn_code || '-' }}
                                </td>
                                <td class="py-3 px-3 text-right border-r border-gray-800">{{ Number(item.quantity) }}
                                </td>
                                <td class="py-3 px-3 text-right border-r border-gray-800">{{
                                    formatCurrency(item.unit_price) }}</td>
                                <td v-if="invoice.meta?.display_options?.show_discount"
                                    class="py-3 px-3 text-right text-red-600 border-r border-gray-800">{{
                                        Number(item.discount) ? '-' +
                                            Number(item.discount) : '-' }}</td>

                                <template v-if="invoice.meta?.display_options?.show_gst_breakdown">
                                    <template v-if="taxBreakdown.taxType === 'IGST'">
                                        <td class="py-3 px-3 text-right border-r border-gray-800 text-[10px]">
                                            <div>{{ Number(item.tax_rate) }}%</div>
                                            <div class="font-medium">{{ Number(item.tax_amount).toFixed(2) }}</div>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td class="py-3 px-3 text-right border-r border-gray-800 text-[10px]">
                                            <div>{{ Number(item.tax_rate) / 2 }}%</div>
                                            <div class="font-medium">{{ (Number(item.tax_amount) / 2).toFixed(2) }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-right border-r border-gray-800 text-[10px]">
                                            <div>{{ Number(item.tax_rate) / 2 }}%</div>
                                            <div class="font-medium">{{ (Number(item.tax_amount) / 2).toFixed(2) }}
                                            </div>
                                        </td>
                                    </template>
                                </template>

                                <td class="py-3 px-3 text-right font-bold border-gray-800">{{ formatCurrency(item.total)
                                }}</td>
                            </tr>
                            <!-- Fill Empty Rows to maintain height if needed, OR just let it flow -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer Area -->
                <div class="border-t-2 border-gray-800 flex">
                    <!-- Left: Notes, Bank -->
                    <div class="w-2/3 border-r-2 border-gray-800 flex flex-col">
                        <!-- Amount Words Header -->
                        <div class="p-2 bg-gray-100 border-b border-gray-800 text-xs font-bold uppercase text-gray-700">
                            Amount in Words
                        </div>
                        <div class="p-3 border-b border-gray-800 font-bold text-sm text-gray-900 capitalize">
                            {{ amountInWords(finalTotals.rounded) }} Only
                        </div>

                        <div class="flex-grow p-4 flex gap-6">
                            <!-- Bank -->
                            <div v-if="invoice.meta?.display_options?.show_qr_bank_details && (invoice.business?.bank_name || invoice.business?.account_number || invoice.business?.ifsc_code || invoice.business?.meta?.upi_id)"
                                class="flex-1">
                                <h4
                                    class="text-[10px] font-bold uppercase text-gray-500 border-b border-gray-300 pb-1 mb-2">
                                    Bank
                                    Details</h4>
                                <div class="text-xs space-y-1">
                                    <p v-if="invoice.business?.bank_name"><span class="font-bold">Bank:</span> {{
                                        invoice.business.bank_name }}</p>
                                    <p v-if="invoice.business?.account_number"><span class="font-bold">A/c No:</span> {{
                                        invoice.business.account_number }}</p>
                                    <p v-if="invoice.business?.ifsc_code"><span class="font-bold">IFSC:</span> {{
                                        invoice.business.ifsc_code }}</p>
                                    <p v-if="invoice.business?.meta?.upi_id"><span class="font-bold">UPI:</span> {{
                                        invoice.business.meta.upi_id }}</p>
                                </div>
                            </div>

                            <!-- QR Details -->
                            <div v-if="qrCodeUrl && invoice.meta?.display_options?.show_qr_bank_details"
                                class="shrink-0">
                                <img :src="qrCodeUrl" class="h-20 w-20 object-contain border border-gray-200 p-1" />
                            </div>
                        </div>

                        <!-- Terms/Notes -->
                        <div class="p-3 border-t border-gray-800 text-[10px] text-gray-600">
                            <div v-if="invoice.notes">
                                <span class="font-bold text-gray-900">Notes:</span> {{ invoice.notes }}
                            </div>
                            <div v-if="invoice.terms" class="mt-1">
                                <span class="font-bold text-gray-900">Terms:</span> {{ invoice.terms }}
                            </div>
                        </div>
                    </div>

                    <!-- Right: Totals -->
                    <div class="w-1/3 flex flex-col">
                        <div class="flex justify-between items-center p-2 border-b border-gray-300">
                            <span class="text-xs text-gray-600">Subtotal</span>
                            <span class="text-sm font-bold">{{ formatCurrency(invoice.subtotal) }}</span>
                        </div>
                        <div v-if="invoice.meta?.display_options?.show_discount && totalDiscount > 0"
                            class="flex justify-between items-center p-2 border-b border-gray-300 text-red-600">
                            <span class="text-xs">Discount</span>
                            <span class="text-sm">-{{ formatCurrency(totalDiscount) }}</span>
                        </div>

                        <!-- Tax Totals -->
                        <template v-if="invoice.meta?.display_options?.show_gst_breakdown">
                            <template v-if="taxBreakdown.taxType === 'IGST'">
                                <div class="flex justify-between items-center p-2 border-b border-gray-300">
                                    <span class="text-xs text-gray-600">IGST</span>
                                    <span class="text-sm">{{ formatCurrency(taxBreakdown.igst) }}</span>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex justify-between items-center p-2 border-b border-gray-300">
                                    <span class="text-xs text-gray-600">CGST</span>
                                    <span class="text-sm">{{ formatCurrency(taxBreakdown.cgst) }}</span>
                                </div>
                                <div class="flex justify-between items-center p-2 border-b border-gray-300">
                                    <span class="text-xs text-gray-600">SGST</span>
                                    <span class="text-sm">{{ formatCurrency(taxBreakdown.sgst) }}</span>
                                </div>
                            </template>
                        </template>

                        <template v-if="finalTotals.roundOff !== 0">
                            <div class="flex justify-between items-center p-2 border-b border-gray-300">
                                <span class="text-xs text-gray-600">Round Off</span>
                                <span class="text-sm">{{ formatCurrency(finalTotals.roundOff) }}</span>
                            </div>
                        </template>

                        <div class="flex justify-between items-center p-3 bg-gray-800 text-white mt-auto">
                            <span class="font-bold uppercase">Total</span>
                            <span class="font-bold text-xl">{{ formatCurrency(finalTotals.rounded) }}</span>
                        </div>

                        <div class="h-24 border-t border-gray-300 p-2 flex flex-col justify-end text-center">
                            <p class="text-[9px] font-bold">{{ invoice.business?.name }}</p>
                            <p class="text-[8px] text-gray-500 uppercase mt-0.5">Authorized Signatory</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Text -->
            <div class="text-center mt-2 text-[10px] text-gray-400">
                This is a computer generated invoice.
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    invoice: any
    taxBreakdown: { cgst: number, sgst: number, igst: number, taxType: string, posState: string },
    qrCodeUrl?: string
}>()

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

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(value)
}

const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-IN', {
        day: 'numeric', month: 'short', year: 'numeric'
    })
}

const amountInWords = (num: number) => {
    if (!num) return 'Zero'
    const a = ['', 'one ', 'two ', 'three ', 'four ', 'five ', 'six ', 'seven ', 'eight ', 'nine ', 'ten ', 'eleven ', 'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', 'seventeen ', 'eighteen ', 'nineteen '];
    const b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    const sNum = Math.floor(num).toString();
    if (sNum.length > 9) return 'overflow';

    const n = ('000000000' + sNum).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
    if (!n) return '';

    let str = '';
    str += (Number(n[1]) != 0) ? (a[Number(n[1])] || b[Number(n[1]![0])] + ' ' + a[Number(n[1]![1])]) + 'crore ' : '';
    str += (Number(n[2]) != 0) ? (a[Number(n[2])] || b[Number(n[2]![0])] + ' ' + a[Number(n[2]![1])]) + 'lakh ' : '';
    str += (Number(n[3]) != 0) ? (a[Number(n[3])] || b[Number(n[3]![0])] + ' ' + a[Number(n[3]![1])]) + 'thousand ' : '';
    str += (Number(n[4]) != 0) ? (a[Number(n[4])] || b[Number(n[4]![0])] + ' ' + a[Number(n[4]![1])]) + 'hundred ' : '';
    str += (Number(n[5]) != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[Number(n[5]![0])] + ' ' + a[Number(n[5]![1])]) + '' : '';
    return str;
}
</script>

<style scoped>
@media print {
    @page {
        size: A4;
        margin: 0;
    }

    #invoice-paper {
        box-shadow: none;
        margin: 0;
        width: 100%;
        height: auto;
    }
}
</style>
