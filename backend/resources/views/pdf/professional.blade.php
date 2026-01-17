<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #1f2937;
            /* Gray-900 */
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .w-full {
            width: 100%;
        }

        .w-half {
            width: 50%;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .text-xs {
            font-size: 11px;
        }

        .text-sm {
            font-size: 12px;
        }

        .text-gray-500 {
            color: #6b7280;
        }

        .text-gray-400 {
            color: #9ca3af;
        }

        .text-red-500 {
            color: #ef4444;
        }

        /* Layout Helpers */
        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .p-4 {
            padding: 16px;
        }

        .px-8 {
            padding-left: 32px;
            padding-right: 32px;
        }

        .py-4 {
            padding-top: 16px;
            padding-bottom: 16px;
        }

        .py-2 {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        /* Top Color Bar */
        .top-bar {
            height: 8px;
            width: 100%;
            background-color: {{ $brandColor }};
        }

        /* Header */
        .header-section {
            padding: 24px 32px;
            border-bottom: 1px solid #f3f4f6;
        }

        .logo-img {
            max-height: 60px;
            max-width: 150px;
            object-fit: contain;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
            color: {{ $brandColor }};
        }

        /* Big INVOICE Title */
        .invoice-title-large {
            font-size: 32px;
            font-weight: 900;
            color: #d1d5db;
            /* Gray-300 */
            letter-spacing: 2px;
            line-height: 1;
        }

        /* Grey Info Block */
        .info-block {
            background-color: #f9fafb;
            padding: 24px 32px;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-label {
            font-size: 10px;
            font-weight: bold;
            color: #9ca3af;
            /* Gray-400 */
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: bold;
            color: #111827;
        }

        .info-address {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.4;
            margin-top: 4px;
        }

        /* Items Table */
        .items-header th {
            text-transform: uppercase;
            font-size: 11px;
            color: #6b7280;
            font-weight: bold;
            padding: 12px 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-row td {
            padding: 12px 4px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        /* Totals */
        .totals-block {
            padding: 24px 32px;
            background-color: #f9fafb;
            border-top: 1px solid #f3f4f6;
        }

        .amount-words-label {
            font-size: 10px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <!-- Top Bar -->
    <div class="top-bar"></div>

    <!-- Header -->
    <table class="header-section">
        <tr>
            <td style="vertical-align: top;">
                @if (isset($business['meta']['logo']))
                    <img src="{{ $business['meta']['logo'] }}" class="logo-img mb-4">
                @endif
                <div class="company-name">{{ $business->name }}</div>
                <div class="text-xs text-gray-500">
                    <div>{{ $business->address }}</div>
                    @if ($business->gstin)
                        <div>GSTIN: {{ $business->gstin }}</div>
                    @endif
                    @if ($business->mobile)
                        <div>M: {{ $business->mobile }}</div>
                    @endif
                    @if ($business->email)
                        <div>E: {{ $business->email }}</div>
                    @endif
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 40%;">
                <div class="invoice-title-large">INVOICE</div>
                <table style="width: 100%; margin-top: 16px;">
                    <tr>
                        <td class="text-right text-gray-400 text-xs font-bold uppercase" style="padding-right: 12px;">
                            Invoice No</td>
                        <td class="text-right font-bold text-sm">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-right text-gray-400 text-xs font-bold uppercase" style="padding-right: 12px;">
                            Date</td>
                        <td class="text-right font-bold text-sm">{{ $invoice->date->format('d M, Y') }}</td>
                    </tr>
                    @if ($invoice->due_date)
                        <tr>
                            <td class="text-right text-gray-400 text-xs font-bold uppercase"
                                style="padding-right: 12px;">Due Date</td>
                            <td class="text-right font-bold text-sm text-red-500">
                                {{ $invoice->due_date->format('d M, Y') }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Grey Info Block (Bill To / Ship To) -->
    <table class="info-block">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                <div class="info-label">Bill To</div>
                <div class="info-value">{{ $party->name }}</div>
                <div class="info-address">
                    @php $billing = $party->billing_address ?? []; @endphp
                    @if ($billing)
                        {{ $billing['street'] ?? '' }}<br>
                        {{ $billing['city'] ?? '' }} {{ $billing['zip'] ?? '' }}<br>
                        {{ $billing['state'] ?? '' }}
                    @endif
                    @if ($party->gstin)
                        <div class="mt-2"><span class="font-bold text-gray-400">GSTIN:</span> {{ $party->gstin }}
                        </div>
                    @endif
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px; border-left: 1px solid #e5e7eb;">
                <div class="info-label">Ship To</div>
                @php
                    $shipping = $party->shipping_address ?? ($party->billing_address ?? []);
                    // Logic to show shipping if explicit, consistent with frontend logic roughly
                @endphp
                <div class="info-value">{{ $party->name }}</div>
                <div class="info-address">
                    @if ($shipping)
                        {{ $shipping['street'] ?? '' }}<br>
                        {{ $shipping['city'] ?? '' }} {{ $shipping['zip'] ?? '' }}<br>
                        {{ $shipping['state'] ?? '' }}
                    @endif
                </div>

                @if ($invoice->vehicle_no || $invoice->eway_bill_no)
                    <div style="margin-top: 12px;">
                        <table style="width: 100%;">
                            @if ($invoice->vehicle_no)
                                <tr>
                                    <td class="text-xs text-gray-400 font-bold uppercase" style="width: 40%;">Vehicle
                                    </td>
                                    <td class="text-xs font-mono">{{ $invoice->vehicle_no }}</td>
                                </tr>
                            @endif
                            @if ($invoice->eway_bill_no)
                                <tr>
                                    <td class="text-xs text-gray-400 font-bold uppercase">E-Way</td>
                                    <td class="text-xs font-mono">{{ $invoice->eway_bill_no }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <div class="px-8 py-4">
        <table class="w-full">
            <thead>
                <tr class="items-header">
                    <th class="text-left" style="width: 5%;">#</th>
                    <th class="text-left" style="width: 35%;">Description</th>
                    <th class="text-center" style="width: 10%;">HSN</th>
                    <th class="text-right" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 12%;">Rate</th>
                    @if ($invoice->items->sum('discount') > 0)
                        <th class="text-right" style="width: 10%;">Disc</th>
                    @endif
                    <!-- Simplified Tax Header for PDF (Full breakdown is tricky in limited space) -->
                    <!-- Mimicking ProfessionalLayout dynamic headers -->
                    @if (isset($taxBreakdown['is_igst']) && $taxBreakdown['is_igst'])
                        <th class="text-right" style="width: 10%;">IGST</th>
                    @else
                        <th class="text-right" style="width: 8%;">CGST</th>
                        <th class="text-right" style="width: 8%;">SGST</th>
                    @endif
                    <th class="text-right" style="width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $index => $item)
                    <tr class="items-row">
                        <td class="text-gray-400">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->description }}</td>
                        <td class="text-center text-gray-500">{{ $item->hsn_code ?? '-' }}</td>
                        <td class="text-right text-gray-500">{{ $item->quantity + 0 }}</td>
                        <td class="text-right text-gray-500">{{ number_format($item->unit_price, 2) }}</td>
                        @if ($invoice->items->sum('discount') > 0)
                            <td class="text-right text-red-500">
                                {{ $item->discount > 0 ? '-' . number_format($item->discount, 2) : '-' }}</td>
                        @endif

                        @if (isset($taxBreakdown['is_igst']) && $taxBreakdown['is_igst'])
                            <td class="text-right text-gray-500">
                                <div style="font-size: 9px;">{{ $item->tax_rate + 0 }}%</div>
                                <div>{{ number_format($item->tax_amount, 2) }}</div>
                            </td>
                        @else
                            <td class="text-right text-gray-500">
                                <div style="font-size: 9px;">{{ $item->tax_rate / 2 + 0 }}%</div>
                                <div>{{ number_format($item->tax_amount / 2, 2) }}</div>
                            </td>
                            <td class="text-right text-gray-500">
                                <div style="font-size: 9px;">{{ $item->tax_rate / 2 + 0 }}%</div>
                                <div>{{ number_format($item->tax_amount / 2, 2) }}</div>
                            </td>
                        @endif

                        <td class="text-right font-bold">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer / Totals Section -->
    <div class="totals-block">
        <table>
            <tr>
                <!-- Left Column (Words, Notes, Bank) -->
                <td style="width: 60%; vertical-align: top; padding-right: 20px;">
                    <!-- Amount Words -->
                    <div style="border-left: 2px solid #d1d5db; padding-left: 12px; margin-bottom: 20px;">
                        <div class="amount-words-label">Amount In Words</div>
                        <div class="font-bold text-sm">{{ $amountInWords ?? 'Zero' }} Only</div>
                    </div>

                    <!-- Bank Details -->
                    @if ($business->bank_name && $business->account_number)
                        <div class="mb-4">
                            <div class="info-label">Bank Details</div>
                            <div class="text-xs text-gray-500">
                                <div>Bank: <span class="text-gray-900 font-bold">{{ $business->bank_name }}</span>
                                </div>
                                <div>A/c No: <span
                                        class="text-gray-900 font-bold">{{ $business->account_number }}</span></div>
                                <div>IFSC: <span class="text-gray-900 font-bold">{{ $business->ifsc_code }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Terms -->
                    @if ($invoice->terms)
                        <div>
                            <div class="info-label">Terms & Conditions</div>
                            <div class="text-xs text-gray-500" style="white-space: pre-line;">{{ $invoice->terms }}
                            </div>
                        </div>
                    @endif
                </td>

                <!-- Right Column (Totals) -->
                <td style="width: 40%; vertical-align: top;">
                    <table class="w-full text-right text-xs">
                        <tr>
                            <td class="text-gray-500 py-2">Subtotal</td>
                            <td class="font-bold py-2">{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if ($invoice->discount_total > 0)
                            <tr>
                                <td class="text-red-500 py-2">Discount</td>
                                <td class="text-red-500 py-2">-{{ number_format($invoice->discount_total, 2) }}</td>
                            </tr>
                        @endif

                        @if ($invoice->tax_total > 0)
                            @if (isset($taxBreakdown['is_igst']) && $taxBreakdown['is_igst'])
                                <tr>
                                    <td class="text-gray-500 py-2">IGST</td>
                                    <td class="font-bold py-2">{{ number_format($invoice->tax_total, 2) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-gray-500 py-2">CGST</td>
                                    <td class="font-bold py-2">{{ number_format($invoice->tax_total / 2, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-500 py-2">SGST</td>
                                    <td class="font-bold py-2">{{ number_format($invoice->tax_total / 2, 2) }}</td>
                                </tr>
                            @endif
                        @endif

                        <tr style="border-top: 1px solid #e5e7eb;">
                            <td class="font-bold text-sm py-2" style="color: #111827; padding-top: 12px;">Total</td>
                            <td class="font-bold text-sm py-2" style="color: #111827; padding-top: 12px;">
                                {{ number_format($invoice->grand_total, 2) }}</td>
                        </tr>
                    </table>

                    <div style="margin-top: 40px; text-align: right;">
                        <div class="font-bold text-xs">{{ $business->name }}</div>
                        <div class="info-label" style="font-size: 8px;">Authorized Signatory</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Branding Footer -->
    <div class="text-center p-4" style="border-top: 1px solid #f3f4f6;">
        <span class="info-label" style="font-size: 9px; opacity: 0.7;">Powered by VedantBilling</span>
    </div>

</body>

</html>
