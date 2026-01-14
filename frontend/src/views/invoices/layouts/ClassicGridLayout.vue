<template>
    <div class="print-layout-container bg-gray-100 print:bg-white pb-10 print:pb-0 px-4 print:px-0 pt-8 print:pt-0">

        <!-- Loop Explicit Pages -->
        <!-- Each page is a strict A4 sized block -->
        <div v-for="(page, pageIndex) in pages" :key="pageIndex"
            class="a4-page mx-auto bg-white shadow-lg print:shadow-none print:m-0 flex flex-col relative mb-8 print:mb-0 break-after-page font-sans text-black">

            <!-- OUTER BORDER CONTAINER (Strict 10mm margin simulation or just full border) -->
            <!-- We'll treat the A4 page as the canvas and draw a border box inside -->
            <div class="h-full flex flex-col border-2 border-black m-[5mm]">

                <!-- HEADER SECTION: "Tax Invoice" + Company Details -->
                <div class="shrink-0">
                    <div class="text-center font-bold text-base border-b border-black py-1">Tax Invoice</div>

                    <!-- Top Grid: Seller Info (Left) | Invoice Meta (Right) -->
                    <div class="grid grid-cols-2 border-b border-black">
                        <!-- Left: Seller Info -->
                        <div class="p-1.5 border-r border-black flex flex-col justify-between">
                            <div>
                                <h1 class="font-bold text-sm uppercase">{{ invoice.business?.name }}</h1>
                                <p class="text-[10px] mt-0.5">{{ invoice.business?.meta?.address_line_1 || 'Kewalpurwa Nagar' }}</p>
                                <p class="text-[10px]">City : {{ invoice.business?.meta?.city || 'Lakhimpur' }}</p>
                                <p class="text-[10px]">Pincode : {{ invoice.business?.meta?.pincode ||
                                    invoice.business?.zip || '-' }}</p>
                                <p class="text-[10px] font-bold mt-1">GSTIN/UIN: {{ invoice.business?.gstin }}</p>
                                <p class="text-[10px]">State Name: {{ invoice.business?.meta?.state ||
                                    invoice.business?.state }}</p>
                                <p class="text-[10px]">Email: {{ invoice.business?.meta?.email ||
                                    invoice.business?.email }}</p>
                            </div>
                            <p class="text-[10px] font-bold mt-1">Mobile: {{ invoice.business?.meta?.phone ||
                                invoice.business?.phone || invoice.business?.mobile }}</p>
                        </div>

                        <!-- Right: Invoice Details Grid -->
                        <div class="text-xs">
                            <div class="grid grid-cols-2 border-b border-black">
                                <div class="p-1 border-r border-black">
                                    <span class="block text-[10px] font-bold">Invoice No:</span>
                                    <span class="font-bold text-sm">{{ invoice.invoice_number }}</span>
                                </div>
                                <div class="p-1">
                                    <span class="block text-[10px] font-bold">Dated:</span>
                                    <span class="font-bold text-sm">{{ formatDate(invoice.date) }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 border-b border-black">
                                <div class="p-1 border-r border-black">
                                    <span class="block text-[10px] font-bold">Delivery Note</span>
                                    <span>{{ invoice.meta?.delivery_note || '' }}</span>
                                </div>
                                <div class="p-1">
                                    <span class="block text-[10px] font-bold">Mode/Terms of Payment</span>
                                    <span>{{ invoice.meta?.payment_terms || '' }}</span>
                                </div>
                            </div>
                            <!-- Row 3: Reference No. & Date -> OR E-Way Bill / Vehicle No in original? 
                                 Original Image: "Reference No. & Date" (Left) | "Other References" (Right) -->
                            <div class="grid grid-cols-2 border-b border-black">
                                <div class="p-1 border-r border-black">
                                    <span class="block text-[10px] font-bold">Eway Bill Number</span>
                                    <span>{{ invoice.meta?.eway_bill_number || '' }}</span>
                                </div>
                                <div class="p-1">
                                    <span class="block text-[10px] font-bold">Vehicle Number</span>
                                    <span>{{ invoice.meta?.vehicle_number || '' }}</span>
                                </div>
                            </div>
                            <!-- Row 4: Buyer's Order No. | Dated -->
                            <div class="grid grid-cols-2 border-b border-black">
                                <div class="p-1 border-r border-black">
                                    <span class="block text-[10px] font-bold">Buyer's Order No.</span>
                                    <span>{{ invoice.po_number || '' }}</span>
                                </div>
                                <div class="p-1">
                                    <span class="block text-[10px] font-bold">Dated</span>
                                    <span>{{ invoice.po_date ? formatDate(invoice.po_date) : '' }}</span>
                                </div>
                            </div>
                            <!-- Row 5: Dispatch Doc No. | Delivery Note Date -->
                            <div class="grid grid-cols-2 border-b border-black">
                                <div class="p-1 border-r border-black">
                                    <span class="block text-[10px] font-bold">Dispatch Doc No.</span>
                                    <span>{{ invoice.meta?.dispatch_doc_no || '' }}</span>
                                </div>
                                <div class="p-1">
                                    <span class="block text-[10px] font-bold">Delivery Note Date</span>
                                    <span>{{ invoice.meta?.delivery_note_date || '' }}</span>
                                </div>
                            </div>
                            <!-- Row 6: Dispatched through | Destination -->
                            <div class="grid grid-cols-2 border-b border-black">
                                <div class="p-1 border-r border-black">
                                    <span class="block text-[10px] font-bold">Dispatched through</span>
                                    <span>{{ invoice.meta?.dispatched_through || '' }}</span>
                                </div>
                                <div class="p-1">
                                    <span class="block text-[10px] font-bold">Destination</span>
                                    <span>{{ invoice.meta?.destination || invoice.party?.shipping_address?.city || ''
                                    }}</span>
                                </div>
                            </div>
                            <!-- Row 7: Terms of Delivery (Full Width) -->
                            <div class="grid grid-cols-2">
                                <div class="p-1 border-r border-black col-span-2">
                                    <span class="block text-[10px] font-bold">Terms of Delivery</span>
                                    <span>{{ invoice.meta?.terms_of_delivery || '' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer Details -->
                    <div class="grid border-b border-black"
                        :class="displayOpts.show_ship_to ? 'grid-cols-2' : 'grid-cols-1'">
                        <!-- Buyer (Bill To) -->
                        <div class="p-1.5 flex flex-col justify-between min-h-[25mm]"
                            :class="displayOpts.show_ship_to ? 'border-r border-black' : ''">
                            <div>
                                <span class="block text-[9px] font-bold uppercase mb-0.5">BUYER (BILL TO)</span>
                                <h2 class="font-bold text-sm uppercase">{{ invoice.party?.name }}</h2>
                                <p class="text-[10px] mt-0.5">{{ invoice.party?.billing_address?.street }}</p>
                                <p class="text-[10px]">City: {{ invoice.party?.billing_address?.city }}<span
                                        v-if="invoice.party?.billing_address?.zip"> - {{
                                            invoice.party?.billing_address?.zip }}</span></p>
                                <p class="text-[10px] font-bold mt-0.5">GSTIN/UIN: {{ invoice.party?.gstin }}</p>
                                <p class="text-[10px]">State Name: {{ invoice.party?.billing_address?.state }}</p>
                            </div>
                        </div>
                        <!-- Consignee (Ship To) - Conditional -->
                        <div v-if="displayOpts.show_ship_to" class="p-1.5 flex flex-col justify-between min-h-[25mm]">
                            <div>
                                <span class="block text-[9px] font-bold uppercase mb-0.5">CONSIGNEE (SHIP TO)</span>
                                <h2 class="font-bold text-sm uppercase">{{ invoice.party?.name }}</h2>
                                <p class="text-[10px] mt-0.5">{{ invoice.party?.shipping_address?.street
                                    || invoice.party?.billing_address?.street }}</p>
                                <p class="text-[10px]">City: {{ invoice.party?.shipping_address?.city ||
                                    invoice.party?.billing_address?.city }}</p>
                                <p class="text-[10px] font-bold mt-0.5">GSTIN/UIN: {{ invoice.party?.gstin }}</p>
                                <p class="text-[10px]">State Name: {{ invoice.party?.shipping_address?.state ||
                                    invoice.party?.billing_address?.state }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE HEADER -->
                <div
                    class="border-b border-black bg-gray-50 text-[10px] font-bold uppercase text-center grid grid-cols-[30px_1fr_60px_50px_60px_50px_70px_80px] divide-x divide-black shrink-0">
                    <div class="py-2">Sr.</div>
                    <div class="py-2">Description of Goods</div>
                    <div class="py-2">HSN/SAC</div>
                    <div class="py-2">Qty</div>
                    <div class="py-2">Rate</div>
                    <div class="py-2">Per</div>
                    <div class="py-2">Disc.</div>
                    <div class="py-2">Amount</div>
                </div>

                <!-- ITEMS (Flexible Height with proper borders) -->
                <div class="flex-grow relative text-[11px] border-b border-black">
                    <!-- Current Page Items -->
                    <div v-for="(item, idx) in page.items" :key="idx"
                        class="grid grid-cols-[30px_1fr_60px_50px_60px_50px_70px_80px] divide-x divide-black border-b border-black h-auto">
                        <div class="px-1 py-1 text-center align-top">{{ item.globalIndex }}</div>
                        <div class="px-2 py-1 align-top text-left font-bold">
                            {{ item.name }}
                            <div v-if="item.description" class="font-normal text-[9px] whitespace-pre-line mt-0.5">{{
                                item.description }}</div>
                        </div>
                        <div class="px-1 py-1 text-center align-top">{{ item.hsn_code || '-' }}</div>
                        <div class="px-1 py-1 text-center align-top font-bold">{{ Number(item.quantity) }}</div>
                        <div class="px-1 py-1 text-right align-top">{{ formatNumber(item.unit_price) }}</div>
                        <div class="px-1 py-1 text-center align-top">Nos</div>
                        <div class="px-1 py-1 text-right align-top">{{ Number(item.discount) > 0 ?
                            formatNumber(item.discount) : '-' }}</div>
                        <div class="px-1 py-1 text-right align-top font-bold">{{ formatNumber(item.total) }}</div>
                    </div>

                    <!-- Filling Empty Space with Vertical Lines (if no more items) -->
                    <div
                        class="absolute inset-0 top-0 pointer-events-none grid grid-cols-[30px_1fr_60px_50px_60px_50px_70px_80px] divide-x divide-black -z-10">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>

                <!-- FOOTER (Last Page Only logic handled inside content) -->
                <div class="shrink-0 text-xs">

                    <!-- Total Row - must match item grid columns for border continuation -->
                    <div
                        class="grid grid-cols-[30px_1fr_60px_50px_60px_50px_70px_80px] border-b border-black divide-x divide-black">
                        <div class="px-1 py-1"></div>
                        <div class="px-1 py-1"></div>
                        <div class="px-1 py-1"></div>
                        <div class="px-1 py-1"></div>
                        <div class="px-1 py-1"></div>
                        <div class="px-1 py-1"></div>
                        <div class="px-1 py-1 text-right font-bold uppercase">Total</div>
                        <div class="text-right px-2 py-1 font-bold">
                            {{ page.isLastPage ? formatNumber(invoice.grand_total) : formatNumber(page.pageTotal) }}
                        </div>
                    </div>

                    <!-- Only show Full Footer on Last Page -->
                    <template v-if="page.isLastPage">
                        <div class="p-1 px-2 border-b border-black">
                            <span class="font-bold text-[10px]">Amount Chargeable (in words):</span>
                            <span class="capitalize font-bold ml-2">Indian Rupees {{ amountInWords(invoice.grand_total)
                                }} Only</span>
                        </div>

                        <!-- Tax Summary Table -->
                        <div
                            class="grid grid-cols-[80px_100px_1fr_1fr_100px] border-b border-black divide-x divide-black text-center text-[10px]">
                            <!-- Header -->
                            <div class="font-bold bg-gray-50 py-1">HSN/SAC</div>
                            <div class="font-bold bg-gray-50 py-1">Taxable Value</div>
                            <div class="font-bold bg-gray-50 py-1 grid grid-cols-2 divide-x divide-black">
                                <div class="col-span-2 border-b border-black">Central Tax</div>
                                <div>Rate</div>
                                <div>Amount</div>
                            </div>
                            <div class="font-bold bg-gray-50 py-1 grid grid-cols-2 divide-x divide-black">
                                <div class="col-span-2 border-b border-black">State Tax</div>
                                <div>Rate</div>
                                <div>Amount</div>
                            </div>
                            <div class="font-bold bg-gray-50 py-1">Total Tax Amount</div>

                            <!-- Content (Group by HSN and Tax Rate) -->
                            <template v-for="(group, idx) in taxBreakdown.hsnGroups" :key="idx">
                                <div class="py-1">{{ group.hsn }}</div>
                                <div class="py-1 font-bold">{{ formatNumber(group.taxable) }}</div>
                                <div class="py-1 grid grid-cols-2 divide-x divide-black">
                                    <div>{{ group.cgst_rate ? group.cgst_rate + '%' : '-' }}</div>
                                    <div>{{ formatNumber(group.cgst_amount) }}</div>
                                </div>
                                <div class="py-1 grid grid-cols-2 divide-x divide-black">
                                    <div>{{ group.sgst_rate ? group.sgst_rate + '%' : '-' }}</div>
                                    <div>{{ formatNumber(group.sgst_amount) }}</div>
                                </div>
                                <div class="py-1 font-bold">{{ formatNumber(group.total_tax) }}</div>
                            </template>

                            <!-- Total Summary Row -->
                            <div class="py-1 font-bold">Total</div>
                            <div class="py-1 font-bold">{{ formatNumber(invoice.subtotal) }}</div>
                            <div class="py-1 grid grid-cols-2 divide-x divide-black">
                                <div>-
                                </div>
                                <div class="font-bold">{{ formatNumber(taxBreakdown.cgst) }}</div>
                            </div>
                            <div class="py-1 grid grid-cols-2 divide-x divide-black">
                                <div>-
                                </div>
                                <div class="font-bold">{{ formatNumber(taxBreakdown.sgst) }}</div>
                            </div>
                            <div class="py-1 font-bold">{{ formatNumber(taxBreakdown.cgst + taxBreakdown.sgst +
                                taxBreakdown.igst) }}</div>
                        </div>

                        <!-- Bank & Signatures -->
                        <div class="grid grid-cols-2 min-h-[30mm] divide-x divide-black">
                            <!-- Left: Bank Details + QR Code -->
                            <div class="p-2 flex flex-col justify-between">
                                <div v-if="displayOpts.show_qr_bank_details">
                                    <p class="font-bold underline mb-1">Bank Details</p>
                                    <p>Bank: <span class="font-bold">{{ invoice.business?.bank_name }}</span></p>
                                    <p>A/c No.: <span class="font-bold">{{ invoice.business?.account_number }}</span>
                                    </p>
                                    <p>IFSC: <span class="font-bold">{{ invoice.business?.ifsc_code }}</span></p>

                                    <!-- QR Code -->
                                    <div v-if="qrCodeUrl" class="mt-3 flex justify-center">
                                        <img :src="qrCodeUrl" alt="Payment QR Code" class="w-24 h-24" />
                                    </div>
                                </div>

                                <div class="mt-2 text-[9px]">
                                    <p class="font-bold">Declaration:</p>
                                    <p>We declare that this invoice shows the actual price of the goods described and
                                        that all particulars are true and correct.</p>
                                </div>
                            </div>

                            <!-- Right: Authorized Signatory -->
                            <div class="p-2 flex flex-col justify-between items-end text-right">
                                <div class="font-bold uppercase mb-8">For {{ invoice.business?.name }}</div>
                                <div class="mt-8 border-t border-black w-32"></div>
                                <div class="text-[10px]">Authorised Signatory</div>
                            </div>
                        </div>
                    </template>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    invoice: any,
    taxBreakdown: any,
    qrCodeUrl: string
}>()

const MAX_ITEMS_FIRST = 12; // Items fit on first page
const MAX_ITEMS_STD = 18;   // Items fit on subsequent pages
const TOTALS_SPACE_ITEMS = 6;

// Display Options
const safeMeta = computed(() => props.invoice.meta || {})
const displayOpts = computed(() => {
    const opts = safeMeta.value.display_options || {}
    return {
        show_qr_bank_details: opts.show_qr_bank_details ?? true, // Default true for Classic Grid
        show_hsn: opts.show_hsn ?? true,
        show_gst_breakdown: opts.show_gst_breakdown ?? true,
        show_ship_to: opts.show_ship_to ?? true // Default true - show consignee section
    }
})

const pages = computed(() => {
    // 0. Pre-process items
    const allItems = (props.invoice.items || []).map((item: any, totalIndex: number) => ({
        ...item,
        globalIndex: totalIndex + 1
    }));

    const _pages = [];

    // 1. First Page Chunk
    const firstChunk = allItems.slice(0, MAX_ITEMS_FIRST);
    _pages.push({
        items: firstChunk,
        pageTotal: firstChunk.reduce((sum: number, i: any) => sum + Number(i.total || 0), 0),
        isLastPage: allItems.length <= MAX_ITEMS_FIRST,
        hasSpaceForTotals: (allItems.length <= (MAX_ITEMS_FIRST - TOTALS_SPACE_ITEMS)),
    });

    let remainingItems = allItems.slice(MAX_ITEMS_FIRST);

    // 2. Middle Pages
    while (remainingItems.length > 0) {
        const chunk = remainingItems.slice(0, MAX_ITEMS_STD);
        remainingItems = remainingItems.slice(MAX_ITEMS_STD);

        const isLast = remainingItems.length === 0;

        _pages.push({
            items: chunk,
            pageTotal: chunk.reduce((sum: number, i: any) => sum + Number(i.total || 0), 0),
            isLastPage: isLast,
            hasSpaceForTotals: isLast && (chunk.length <= (MAX_ITEMS_STD - TOTALS_SPACE_ITEMS)),
        });
    }

    return _pages;
});

const formatNumber = (val: any) => new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(val))
const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-IN') : ''
const amountInWords = (num: number) => {
    if (!num) return 'Zero';
    const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
    const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    const inWords = (nStr: any): string => {
        let n: any = nStr.toString();
        if (n.length > 9) return 'overflow';
        // @ts-ignore
        n = ('000000000' + n).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
        // @ts-ignore
        if (!n) return; var str = '';
        // @ts-ignore
        str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'Crore ' : '';
        // @ts-ignore
        str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'Lakh ' : '';
        // @ts-ignore
        str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'Thousand ' : '';
        // @ts-ignore
        str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'Hundred ' : '';
        // @ts-ignore
        str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) : '';
        return str;
    }
    const wholePart = Math.floor(num);
    const decimalPart = Math.round((num - wholePart) * 100);
    let result = inWords(wholePart);
    if (decimalPart > 0) result += ' Rupees and ' + inWords(decimalPart) + ' Paise';
    else result += ' Rupees';
    return result;
}
</script>

<style scoped>
/* Strict A4 Dimensions */
.a4-page {
    width: 210mm;
    height: 297mm;
    overflow: hidden;
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
        break-after: page;
        page-break-after: always;
    }

    .a4-page:last-child {
        break-after: auto;
        page-break-after: auto;
    }
}
</style>
