<template>
    <div class="tally-container" :class="{ 'screen-preview': !isPrinting }">
        <div v-for="(page, pageIndex) in pages" :key="pageIndex" class="tally-page">

            <!-- ══ PAGE HEADER ══ -->
            <div class="tally-header">
                <!-- Row 1: Business Name + Document Type -->
                <div class="header-top">
                    <div class="biz-name-block">
                        <div class="biz-name">{{ invoice.business?.name }}</div>
                        <div class="biz-detail">{{ invoice.business?.address }}</div>
                        <div v-if="invoice.business?.meta?.city" class="biz-detail">
                            {{ invoice.business.meta.city }}<span v-if="invoice.business.meta.state">, {{
                                invoice.business.meta.state }}</span><span v-if="invoice.business.meta.pincode"> - {{
                                    invoice.business.meta.pincode }}</span>
                        </div>
                        <div v-if="invoice.business?.gstin" class="biz-gstin">GSTIN : {{ invoice.business.gstin }}</div>
                        <div v-if="invoice.business?.mobile" class="biz-detail">Ph: {{ invoice.business.mobile }}</div>
                    </div>
                    <div class="doc-info-block">
                        <div class="doc-title">{{ documentTitle }}</div>
                        <div v-if="copyType && isTaxDocument" class="doc-copy">({{ copyLabel }})</div>
                        <div v-if="complianceText" class="doc-compliance">{{ complianceText }}</div>
                        <table class="inv-meta-table">
                            <tr>
                                <td class="meta-label">Inv No</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val inv-number">{{ invoice.invoice_number }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Date</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ formatDate(invoice.date) }}</td>
                            </tr>
                            <tr v-if="invoice.type !== 'credit_note'">
                                <td class="meta-label">Due</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ formatDate(invoice.due_date) }}</td>
                            </tr>
                            <tr v-if="invoice.po_number">
                                <td class="meta-label">PO No</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ invoice.po_number }}</td>
                            </tr>
                            <tr v-if="pageIndex > 0">
                                <td class="meta-label">Page</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ pageIndex + 1 }} / {{ pages.length }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Row 2: Bill To / Ship To / Transport -->
                <div v-if="pageIndex === 0" class="header-party">
                    <!-- Bill To -->
                    <div class="party-cell">
                        <div class="party-label">BILL TO</div>
                        <div class="party-name">{{ invoice.party?.name }}</div>
                        <div class="party-addr">
                            <template v-if="safeMeta.billing_address?.street || safeMeta.billing_address?.city">
                                <span>{{ safeMeta.billing_address.street }}</span>
                                <span v-if="safeMeta.billing_address.city">, {{ safeMeta.billing_address.city }}</span>
                                <span v-if="safeMeta.billing_address.state">, {{ safeMeta.billing_address.state
                                    }}</span>
                                <span v-if="safeMeta.billing_address.zip || safeMeta.billing_address.pincode"> - {{
                                    safeMeta.billing_address.zip || safeMeta.billing_address.pincode }}</span>
                            </template>
                            <template v-else>
                                <span>{{ invoice.party?.billing_address?.street }}</span>
                                <span v-if="invoice.party?.billing_address?.city">, {{
                                    invoice.party.billing_address.city }}</span>
                                <span v-if="invoice.party?.billing_address?.state">, {{
                                    invoice.party.billing_address.state }}</span>
                            </template>
                        </div>
                        <div v-if="invoice.party?.gstin" class="party-gstin">GSTIN : {{ invoice.party.gstin }}</div>
                        <div v-if="invoice.party?.mobile" class="party-detail">Ph: {{ invoice.party.mobile }}</div>
                    </div>

                    <!-- Ship To (if enabled) -->
                    <div v-if="shouldShowShipping" class="party-cell party-cell-right">
                        <div class="party-label">SHIP TO</div>
                        <div class="party-name">{{ invoice.party?.name }}</div>
                        <div class="party-addr">
                            <template v-if="safeMeta.shipping_address?.street || safeMeta.shipping_address?.city">
                                <span>{{ safeMeta.shipping_address.street }}</span>
                                <span v-if="safeMeta.shipping_address.city">, {{ safeMeta.shipping_address.city
                                    }}</span>
                                <span v-if="safeMeta.shipping_address.state">, {{ safeMeta.shipping_address.state
                                    }}</span>
                            </template>
                            <template v-else>
                                <span>{{ invoice.party?.shipping_address?.street }}</span>
                                <span v-if="invoice.party?.shipping_address?.city">, {{
                                    invoice.party.shipping_address.city }}</span>
                            </template>
                        </div>
                    </div>

                    <!-- Transport (if enabled) -->
                    <div v-if="displayOpts.show_eway_details" class="party-cell party-cell-right">
                        <div class="party-label">TRANSPORT</div>
                        <div v-if="invoice.eway_bill_no" class="party-detail">E-Way : {{ invoice.eway_bill_no }}</div>
                        <div v-if="invoice.vehicle_no" class="party-detail">Vehicle : {{ invoice.vehicle_no }}</div>
                        <div v-if="invoice.challan_no" class="party-detail">Challan : {{ invoice.challan_no }}</div>
                    </div>
                </div>
            </div>

            <!-- ══ ITEMS TABLE ══ -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-sr">#</th>
                        <th class="col-item">ITEM / DESCRIPTION</th>
                        <th v-if="displayOpts.show_hsn" class="col-hsn">HSN</th>
                        <th class="col-qty">QTY</th>
                        <th class="col-rate">RATE</th>
                        <th v-if="displayOpts.show_discount" class="col-disc">DISC</th>
                        <th v-if="displayOpts.show_gst_breakdown" class="col-tax">GST AMT</th>
                        <th class="col-amt">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, idx) in page.items" :key="idx">
                        <td class="col-sr td-center">{{ item.globalIndex }}</td>
                        <td class="col-item">
                            <span class="item-name">{{ item.name }}</span>
                            <span v-if="item.description && displayOpts.show_description" class="item-desc">{{
                                item.description
                                }}</span>
                        </td>
                        <td v-if="displayOpts.show_hsn" class="col-hsn td-center">{{ item.hsn_code || '-' }}</td>
                        <td class="col-qty td-right">{{ Number(item.quantity) }}</td>
                        <td class="col-rate td-right">{{ formatCurrency(item.unit_price) }}</td>
                        <td v-if="displayOpts.show_discount" class="col-disc td-right">{{ Number(item.discount) ?
                            formatCurrency(item.discount) : '-' }}</td>
                        <td v-if="displayOpts.show_gst_breakdown" class="col-tax td-right">
                            <span class="tax-rate-badge">({{ Number(item.tax_rate) }}%)</span> {{
                                formatCurrency(item.tax_amount) }}
                        </td>
                        <td class="col-amt td-right item-total">{{ formatCurrency(item.total) }}</td>
                    </tr>
                    <!-- Fill empty rows to maintain table height visually -->

                </tbody>
            </table>

            <!-- Spacer — absorbs remaining space so item rows stay compact -->
            <div class="table-spacer"></div>

            <!-- ══ FOOTER / TOTALS ══ (last page only) -->

            <div v-if="page.isLastPage" class="tally-footer">
                <div class="footer-left">
                    <!-- Amount in Words -->
                    <div class="words-label">AMOUNT IN WORDS</div>
                    <div class="words-value">{{ amountInWords(finalTotals.rounded) }} Only</div>
                    <hr class="footer-divider" />

                    <!-- 2-col: Terms+Notes | Bank+QR -->
                    <div class="footer-sub-row">

                        <!-- Left: Terms & Notes -->
                        <div class="footer-sub-left">
                            <div v-if="invoice.terms" class="terms-block">
                                <div class="terms-label">Terms & Conditions:</div>
                                <div class="terms-text">{{ invoice.terms }}</div>
                            </div>
                            <div v-if="invoice.notes" class="terms-block">
                                <div class="terms-label">Notes:</div>
                                <div class="terms-text">{{ invoice.notes }}</div>
                            </div>
                            <div v-if="!invoice.terms && !invoice.notes" class="terms-text" style="color:#ccc">—</div>
                        </div>

                        <!-- Right: Bank + QR -->
                        <div v-if="displayOpts.show_qr_bank_details" class="footer-sub-right">
                            <div class="bank-block">
                                <div class="bank-label">Payment:</div>
                                <div v-if="invoice.business?.bank_name" class="bank-detail">Bank: {{
                                    invoice.business.bank_name }}
                                </div>
                                <div v-if="invoice.business?.account_number" class="bank-detail">A/c: {{
                                    invoice.business.account_number }}</div>
                                <div v-if="invoice.business?.ifsc_code" class="bank-detail">IFSC: {{
                                    invoice.business.ifsc_code }}
                                </div>
                                <div v-if="invoice.business?.meta?.upi_id" class="bank-detail">UPI: {{
                                    invoice.business.meta.upi_id
                                    }}</div>
                            </div>
                            <div v-if="qrCodeUrl" class="qr-block">
                                <img :src="qrCodeUrl" class="qr-img" />
                            </div>
                        </div>

                    </div>
                </div>


                <div class="footer-right">
                    <!-- Totals Table -->
                    <table class="totals-table">
                        <tr>
                            <td class="total-label">Subtotal</td>
                            <td class="total-value">{{ formatCurrency(invoice.subtotal) }}</td>
                        </tr>
                        <tr v-if="displayOpts.show_discount && totalDiscount > 0" class="discount-row">
                            <td class="total-label">Discount</td>
                            <td class="total-value">- {{ formatCurrency(totalDiscount) }}</td>
                        </tr>
                        <template v-if="taxBreakdown.taxType === 'IGST'">
                            <tr>
                                <td class="total-label">IGST</td>
                                <td class="total-value">{{ formatCurrency(taxBreakdown.igst) }}</td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="total-label">CGST</td>
                                <td class="total-value">{{ formatCurrency(taxBreakdown.cgst) }}</td>
                            </tr>
                            <tr>
                                <td class="total-label">SGST</td>
                                <td class="total-value">{{ formatCurrency(taxBreakdown.sgst) }}</td>
                            </tr>
                        </template>
                        <tr v-if="hasCess">
                            <td class="total-label">CESS</td>
                            <td class="total-value">{{ formatCurrency(invoice.cess_total) }}</td>
                        </tr>
                        <tr v-if="finalTotals.roundOff !== 0">
                            <td class="total-label">Round Off</td>
                            <td class="total-value">{{ formatCurrency(finalTotals.roundOff) }}</td>
                        </tr>
                        <tr class="grand-total-row">
                            <td class="total-label grand-label">TOTAL</td>
                            <td class="total-value grand-value">{{ formatCurrency(finalTotals.rounded) }}</td>
                        </tr>
                    </table>

                    <!-- Signature -->
                    <div class="signature-block">
                        <div class="sig-line-for">For {{ invoice.business?.name }}</div>
                        <div class="sig-space"></div>
                        <div class="sig-label">Authorised Signatory</div>
                    </div>
                </div>
            </div>

            <!-- Continuation notice -->
            <div v-if="!page.isLastPage" class="continued-notice">
                Continued on next page...
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

const isPrinting = typeof window !== 'undefined' && window.matchMedia?.('print').matches

const MAX_ITEMS_FIRST = 15;  // First page: 15 items (compact half-page)
const MAX_ITEMS_STD = 18;    // Subsequent pages: more (no party header)


const copyLabel = computed(() => {
    switch (props.copyType) {
        case 'duplicate': return 'DUPLICATE FOR TRANSPORTER'
        case 'triplicate': return 'TRIPLICATE FOR SUPPLIER'
        default: return 'ORIGINAL FOR RECIPIENT'
    }
})

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
        show_description: opts.show_description ?? false,
    }
})

const finalTotals = computed(() => {
    const total = Number(props.invoice.grand_total) || 0;
    const rounded = Math.round(total);
    const roundOff = Number((rounded - total).toFixed(2));
    return { total, rounded, roundOff };
});

const totalDiscount = computed(() =>
    (props.invoice.items || []).reduce((acc: number, item: any) => acc + (Number(item.discount) || 0), 0)
);

const shouldShowShipping = computed(() =>
    displayOpts.value.show_shipping_address &&
    (
        safeMeta.value.shipping_address?.street ||
        safeMeta.value.shipping_address?.city ||
        props.invoice.party?.shipping_address?.street
    )
);

const pages = computed(() => {
    const allItems = (props.invoice.items || []).map((item: any, i: number) => ({
        ...item, globalIndex: i + 1
    }));

    const _pages: any[] = [];
    const firstChunk = allItems.slice(0, MAX_ITEMS_FIRST);
    _pages.push({ items: firstChunk, isLastPage: allItems.length <= MAX_ITEMS_FIRST });

    let remaining = allItems.slice(MAX_ITEMS_FIRST);
    while (remaining.length > 0) {
        const chunk = remaining.slice(0, MAX_ITEMS_STD);
        remaining = remaining.slice(MAX_ITEMS_STD);
        _pages.push({ items: chunk, isLastPage: remaining.length === 0 });
    }
    return _pages;
});

const isTaxDocument = computed(() =>
    ['invoice', 'tax_invoice', 'credit_note', 'debit_note'].includes(props.invoice.type)
);
const hasCess = computed(() => Number(props.invoice.cess_total || 0) > 0);

const complianceText = computed(() => {
    if (!isTaxDocument.value) {
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
/* ══════════════════════════════════════════
   TALLY-STYLE HALF PAGE INVOICE
   Full A4 width (210mm) × Half height (148mm)
   Dense, bordered, no-frills — like Tally ERP
   ══════════════════════════════════════════ */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.tally-page {
    width: 210mm;
    min-height: 148mm;
    max-height: 148mm;
    overflow: hidden;
    background: white;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7.5pt;
    color: #000;
    border: 1.5px dashed #888;
    padding: 3mm;
    display: flex;
    flex-direction: column;
}

.screen-preview .tally-page {
    margin: 0 auto 24px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.18);
}

/* ── HEADER ── */
.tally-header {
    border-bottom: 1.5px solid #000;
}

.header-top {
    display: flex;
    justify-content: space-between;
    padding: 2px 4px;
    border-bottom: 1px solid #aaa;
    gap: 6px;
}

.biz-name-block {
    flex: 1;
}

.biz-name {
    font-size: 9.5pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    line-height: 1.1;
}

.biz-detail {
    font-size: 7pt;
    color: #333;
    line-height: 1.3;
}

.biz-gstin {
    font-size: 7.5pt;
    font-weight: 700;
    margin-top: 1px;
}

.doc-info-block {
    text-align: right;
    min-width: 130px;
}

.doc-title {
    font-size: 8pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #444;
}

.doc-copy {
    font-size: 7pt;
    font-weight: 700;
    color: #555;
}

.doc-compliance {
    font-size: 6.5pt;
    font-weight: 700;
    color: #666;
}

.inv-meta-table {
    margin-top: 2px;
    border-collapse: collapse;
    width: 100%;
}

.inv-meta-table td {
    font-size: 7pt;
    line-height: 1.4;
    padding: 0 2px;
}

.meta-label {
    color: #555;
    white-space: nowrap;
    text-align: left;
}

.meta-sep {
    color: #555;
    padding: 0 2px;
}

.meta-val {
    font-weight: 600;
    text-align: right;
    white-space: nowrap;
}

.inv-number {
    font-size: 8.5pt;
    font-weight: 700;
    color: #000;
}

/* ── PARTY INFO ROW ── */
.header-party {
    display: flex;
    padding: 1px 0;
}

.party-cell {
    flex: 1;
    padding: 1px 4px;
    line-height: 1.2;
}

.party-cell-right {
    border-left: 1px solid #bbb;
}

.party-label {
    font-size: 6pt;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 0;
}

.party-name {
    font-size: 8pt;
    font-weight: 700;
}

.party-addr {
    font-size: 7pt;
    color: #333;
}

.party-gstin {
    font-size: 7pt;
    font-weight: 700;
    margin-top: 1px;
}

.party-detail {
    font-size: 7pt;
    color: #444;
}

/* ── ITEMS TABLE ── */
.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table thead tr {
    background: #e8e8e8;
}

.items-table th {
    font-size: 6.5pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 1px 2px;
    border: 1px solid #999;
    text-align: center;
    white-space: nowrap;
    line-height: 1.1;
}

.th-sub {
    font-weight: 400;
    text-transform: none;
    font-size: 5.5pt;
}

.items-table td {
    font-size: 7.5pt;
    padding: 0.5px 2px;
    border: 1px solid #ccc;
    vertical-align: top;
    line-height: 1.2;
}

.empty-row td {
    height: 9px;
}

.td-right {
    text-align: right;
}

.td-center {
    text-align: center;
}

/* Column widths */
.col-sr {
    width: 18px;
}

.col-item {
    min-width: 80px;
}

.col-hsn {
    width: 52px;
}

.col-qty {
    width: 30px;
}

.col-rate {
    width: 42px;
}

.col-disc {
    width: 38px;
}

.col-tax {
    width: 64px;
    white-space: nowrap;
}

.col-amt {
    width: 52px;
}

.item-name {
    font-weight: 600;
    display: block;
}

.item-desc {
    font-size: 6.5pt;
    color: #555;
    display: block;
}

.item-total {
    font-weight: 700;
}

.tax-rate-badge {
    font-size: 6pt;
    color: #555;
}

/* ── FOOTER ── */
.table-spacer {
    flex: 1;
    min-height: 0;
}

.tally-footer {
    display: flex;
    border-top: 1.5px solid #000;
    min-height: 28mm;
}

.footer-left {
    flex: 1;
    padding: 2px 4px;
    border-right: 1px solid #aaa;
}

.words-label {
    font-size: 6.5pt;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
}

.words-value {
    font-size: 7.5pt;
    font-weight: 700;
    margin-bottom: 3px;
}

.footer-divider {
    border: none;
    border-top: 1px solid #bbb;
    margin: 2px 0 3px 0;
}

.footer-sub-row {
    display: flex;
    gap: 4px;
    flex: 1;
    align-items: flex-start;
}

.footer-sub-left {
    flex: 1;
}

.footer-sub-right {
    display: flex;
    gap: 3px;
    align-items: stretch;
    border-left: 1px solid #ddd;
    padding-left: 4px;
    min-width: 100px;
}

/* Legacy — keep for safety */
.bank-qr-row {
    display: flex;
    gap: 3px;
    margin-top: 1px;
}

.bank-block {
    flex: 1;
}

.bank-label {
    font-size: 7pt;
    font-weight: 700;
}

.bank-detail {
    font-size: 6.5pt;
    color: #333;
    line-height: 1.4;
}

.qr-block {
    display: flex;
    align-items: center;
    justify-content: center;
    align-self: stretch;
}

.qr-img {
    width: auto;
    height: 100%;
    min-height: 44px;
    max-height: 64px;
    object-fit: contain;
    display: block;
}

.terms-block {
    margin-top: 3px;
}

.terms-label {
    font-size: 6.5pt;
    font-weight: 700;
    color: #555;
}

.terms-text {
    font-size: 6.5pt;
    color: #444;
    line-height: 1.3;
}

/* Totals table */
.footer-right {
    width: 128px;
    padding: 2px 4px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.totals-table {
    width: 100%;
    border-collapse: collapse;
}

.totals-table td {
    font-size: 7pt;
    padding: 0.5px 2px;
    border-bottom: 1px solid #ddd;
    line-height: 1.2;
}

.total-label {
    color: #444;
}

.total-value {
    text-align: right;
    font-weight: 600;
    white-space: nowrap;
}

.discount-row .total-label,
.discount-row .total-value {
    color: #c00;
}

.grand-total-row td {
    border-top: 1.5px solid #000;
    border-bottom: 1.5px solid #000;
    padding: 2px;
}

.grand-label {
    font-weight: 700;
    font-size: 8pt;
}

.grand-value {
    font-weight: 700;
    font-size: 8.5pt;
    text-align: right;
}

/* Signature */
.signature-block {
    margin-top: 4px;
    text-align: right;
}

.sig-line-for {
    font-size: 6.5pt;
    font-weight: 700;
}

.sig-space {
    height: 16px;
}

.sig-label {
    font-size: 6.5pt;
    border-top: 1px solid #000;
    padding-top: 1px;
    text-align: center;
}

.continued-notice {
    font-size: 6.5pt;
    color: #888;
    font-style: italic;
    text-align: right;
    padding: 2px 5px;
    border-top: 1px solid #ddd;
}

/* ── PRINT ── */
@media print {
    @page {
        size: A4;
        margin: 0;
    }

    .tally-container {
        padding: 0;
        margin: 0;
        background: white;
    }

    .tally-page {
        margin: 0;
        box-shadow: none;
        border: none;
        break-after: page;
        page-break-after: always;
    }

    .tally-page:last-child {
        break-after: auto;
        page-break-after: auto;
    }
}
</style>
