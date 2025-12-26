<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #111827; /* Gray-900 */
            margin: 0;
            padding: 0;
        }
        table { width: 100%; border-collapse: collapse; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-xs { font-size: 11px; }
        .text-sm { font-size: 12px; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-400 { color: #9ca3af; }
        .text-red-500 { color: #ef4444; }

        /* Margins & Padding */
        .mb-4 { margin-bottom: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-4 { margin-top: 16px; }
        .p-8 { padding: 32px; }

        /* Header */
        .header {
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .logo-box {
            height: 60px; width: 60px;
            background-color: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            text-align: center;
            vertical-align: middle;
            display: inline-block;
        }
        .logo-img { max-height: 90%; max-width: 90%; margin-top: 2px; }
        
        .company-name { font-size: 20px; font-weight: bold; }
        .company-address { font-size: 12px; color: #6b7280; margin-top: 4px; white-space: pre-line; }

        /* Invoice Meta */
        .invoice-badge {
            display: inline-block;
            background-color: #f3f4f6;
            color: #4b5563;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .invoice-number { font-family: monospace; font-size: 20px; font-weight: bold; }

        /* Addresses (Grid-like) */
        .addresses-table td { vertical-align: top; padding-bottom: 20px; }
        .address-box p { margin: 2px 0; }

        /* Items Table */
        .items-table { margin-bottom: 24px; }
        .items-table th {
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #9ca3af;
            text-transform: uppercase;
            padding: 10px 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table td {
            padding: 10px 4px;
            border-bottom: 1px solid #f9fafb;
            font-size: 12px;
            vertical-align: top;
        }

        /* Footer / Totals */
        .totals-table td { padding: 6px 0; }
        .grand-total-row td {
            padding-top: 16px;
            font-size: 16px; 
            font-weight: bold;
        }

        /* Branding Footer */
        .branding-footer {
            margin-top: 40px;
            border-top: 1px solid #f3f4f6;
            padding-top: 12px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

    </style>
</head>
<body class="p-8">

    <!-- Header -->
    <table class="header">
        <tr>
            <td style="vertical-align: top;">
                @if(isset($business['meta']['logo']))
                    <div class="logo-box" style="float: left; margin-right: 16px;">
                        <img src="{{ $business['meta']['logo'] }}" class="logo-img">
                    </div>
                @endif
                <div style="overflow: hidden;">
                    <div class="company-name">{{ $business->name }}</div>
                    <div class="company-address">
                        {{ $business->address }}
                        @if($business->gstin)<div style="margin-top: 2px; color: #374151; font-weight: 500;">GSTIN: {{ $business->gstin }}</div>@endif
                        <div style="margin-top: 4px;">
                            @if($business->mobile) Ph: {{ $business->mobile }} @endif
                            @if($business->website) | {{ $business->website }} @endif
                        </div>
                    </div>
                </div>
            </td>
            <td class="text-right" style="vertical-align: top; width: 40%;">
                <div class="invoice-badge">Invoice</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 8px; font-size: 12px; color: #6b7280;">
                    <div>Date: <span class="font-medium text-gray-900">{{ $invoice->date->format('d M, Y') }}</span></div>
                    @if($invoice->due_date)
                    <div>Due: <span class="font-medium text-gray-900">{{ $invoice->due_date->format('d M, Y') }}</span></div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Addresses -->
    <table class="addresses-table">
        <tr>
            <!-- Bill To -->
            <td style="width: 50%; padding-right: 24px;">
                <div class="text-xs font-bold text-gray-400 uppercase mb-2">Bill To</div>
                <div class="font-bold text-sm mb-1">{{ $party->name }}</div>
                <div class="text-sm text-gray-600 address-box">
                     @php $billing = $party->billing_address ?? []; @endphp
                        @if($billing)
                        <p>{{ $billing['street'] ?? '' }}</p>
                        <p>{{ $billing['city'] ?? '' }} {{ $billing['zip'] ?? '' }}</p>
                        <p>{{ $billing['state'] ?? '' }}</p>
                    @endif
                    @if($party->gstin)
                        <p class="mt-2 font-medium text-gray-900">GSTIN: {{ $party->gstin }}</p>
                    @endif
                </div>
            </td>
            
            <!-- Ship To (or Transport) -->
            <td style="width: 50%; padding-left: 24px;">
                <div class="text-xs font-bold text-gray-400 uppercase mb-2">Ship To</div>
                @php 
                    $shipping = $party->shipping_address ?? $party->billing_address ?? [];
                    // Check if actually set
                    $hasShipping = isset($party->shipping_address) && ($party->shipping_address['street'] ?? false);
                @endphp
                
                @if($hasShipping)
                     <div class="font-bold text-sm mb-1">{{ $party->name }}</div>
                     <div class="text-sm text-gray-600 address-box">
                        <p>{{ $shipping['street'] ?? '' }}</p>
                        <p>{{ $shipping['city'] ?? '' }} {{ $shipping['zip'] ?? '' }}</p>
                        <p>{{ $shipping['state'] ?? '' }}</p>
                    </div>
                @else
                    <!-- Fallback to Transport Details if no specific shipping address shown -->
                    <div style="background-color: #f9fafb; padding: 12px; border-radius: 6px;">
                        <div class="text-xs font-bold text-gray-400 uppercase mb-2">Details</div>
                        <table style="font-size: 12px;">
                            @if($invoice->vehicle_no)
                            <tr><td class="text-gray-500 w-half">Vehicle:</td><td>{{ $invoice->vehicle_no }}</td></tr>
                            @endif
                            @if($invoice->eway_bill_no)
                            <tr><td class="text-gray-500 w-half">E-Way:</td><td>{{ $invoice->eway_bill_no }}</td></tr>
                            @endif
                            @if($invoice->po_number)
                            <tr><td class="text-gray-500 w-half">PO No:</td><td>{{ $invoice->po_number }}</td></tr>
                            @endif
                        </table>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; padding-left: 8px;">#</th>
                <th style="width: 40%;">Item</th>
                <th style="width: 10%;">HSN</th>
                <th class="text-right" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 12%;">Rate</th>
                @if($invoice->items->sum('discount') > 0)
                <th class="text-right" style="width: 10%;">Disc</th>
                @endif
                <th class="text-right" style="width: 15%; padding-right: 8px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td style="padding-left: 8px; color: #6b7280;">{{ $index + 1 }}</td>
                <td class="font-bold text-gray-700">{{ $item->description }}</td>
                <td class="text-gray-500">{{ $item->hsn_code ?? '-' }}</td>
                <td class="text-right text-gray-500">{{ $item->quantity + 0 }}</td>
                <td class="text-right text-gray-500">{{ number_format($item->unit_price, 2) }}</td>
                @if($invoice->items->sum('discount') > 0)
                <td class="text-right text-gray-500">{{ $item->discount > 0 ? number_format($item->discount, 2) : '-' }}</td>
                @endif
                <td class="text-right font-bold text-gray-900" style="padding-right: 8px;">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer Grid -->
    <table>
        <tr>
            <!-- Left: Bank & Words -->
            <td style="width: 55%; vertical-align: top; padding-right: 32px;">
                <div style="margin-bottom: 24px;">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">Amount in Words</div>
                    <div class="text-sm font-medium capitalize">{{ $amountInWords ?? 'Zero' }} Only</div>
                </div>

                @if($business->bank_name)
                <div style="margin-bottom: 24px;">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-2">Payment Details</div>
                    <div style="display: inline-block; vertical-align: top;">
                        <table style="font-size: 12px; color: #4b5563;">
                            <tr><td style="padding-right: 12px;">Bank:</td><td class="font-bold text-gray-900">{{ $business->bank_name }}</td></tr>
                            <tr><td>A/c:</td><td class="font-bold text-gray-900">{{ $business->account_number }}</td></tr>
                            <tr><td>IFSC:</td><td class="font-bold text-gray-900">{{ $business->ifsc_code }}</td></tr>
                        </table>
                    </div>
                </div>
                @endif
            </td>

            <!-- Right: Totals -->
            <td style="width: 45%; vertical-align: top;">
                <table class="totals-table text-right text-sm">
                    <tr>
                        <td class="text-gray-600">Taxable Amount</td>
                        <td class="font-medium">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    
                    @if($invoice->discount_total > 0)
                    <tr>
                        <td class="text-red-500">Discount</td>
                        <td class="text-red-500">-{{ number_format($invoice->discount_total, 2) }}</td>
                    </tr>
                    @endif

                    @if($invoice->tax_total > 0)
                         @if(isset($taxBreakdown['is_igst']) && $taxBreakdown['is_igst'])
                            <tr>
                                <td class="text-gray-600">IGST</td>
                                <td class="font-medium">{{ number_format($invoice->tax_total, 2) }}</td>
                            </tr>
                         @else
                            <tr>
                                <td class="text-gray-600">CGST</td>
                                <td class="font-medium">{{ number_format($invoice->tax_total / 2, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-gray-600">SGST</td>
                                <td class="font-medium">{{ number_format($invoice->tax_total / 2, 2) }}</td>
                            </tr>
                         @endif
                    @endif

                    <tr class="grand-total-row">
                        <td style="border-top: 1px solid #f3f4f6; padding-top: 16px;">Grand Total</td>
                        <td style="border-top: 1px solid #f3f4f6; padding-top: 16px;">{{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                </table>
                
                <div style="margin-top: 40px; text-align: right;">
                    <div class="font-bold text-sm">{{ $business->name }}</div>
                    <div style="height: 48px;"></div>
                    <div class="text-xs text-gray-400 uppercase border-t border-gray-300 pt-2 inline-block">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Branding -->
    <div class="branding-footer">
        Powered directly by <span style="font-weight: bold; color: #111827;">BillingBook</span>
    </div>

</body>
</html>
