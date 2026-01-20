<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Invoice {{ $invoice->invoice_number }} - {{ $invoice->business->name }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .animate-bounce-short {
            animation: bounce 1s 1;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(-25%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }

            50% {
                transform: none;
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 relative">
        <!-- Digital Copy Watermark (Background) -->
        <div
            class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] z-0 overflow-hidden">
            <span class="transform -rotate-45 text-6xl font-black text-gray-900 whitespace-nowrap">DIGITAL COPY</span>
        </div>

        <!-- Header -->
        <div class="relative bg-indigo-600 px-6 py-8 text-center overflow-hidden">
            <div class="absolute inset-0 bg-indigo-600 opacity-90"></div>
            <!-- Decorative Pattern -->
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>

            <div class="relative z-10">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm ring-4 ring-white/30 mb-4 animate-bounce-short">
                    <svg class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-1">Payment Request Verified</h2>
                <p class="text-indigo-100 text-sm">Issued by {{ $invoice->business->name }}</p>
            </div>
        </div>

        <!-- Content -->
        <div class="relative z-10 px-6 py-6 space-y-6">
            <!-- Amount -->
            <div class="text-center border-b border-gray-100 pb-6">
                <p class="text-sm text-gray-500 uppercase tracking-widest font-medium mb-1">Total Amount</p>
                <div class="text-4xl font-extrabold text-gray-900">
                    ₹{{ number_format($invoice->grand_total, 2) }}
                </div>

                @if ($invoice->status === 'paid')
                    <span
                        class="inline-flex mt-3 items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        PAID
                    </span>
                @else
                    <span
                        class="inline-flex mt-3 items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ strtoupper($invoice->status) }}
                    </span>
                @endif
            </div>

            <!-- QR Code (If Unpaid/Pending) -->
            @if ($invoice->status !== 'paid' && $invoice->status !== 'draft' && isset($invoice->business->meta['upi_id']))
                <div
                    class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300 flex flex-col items-center text-center">
                    <p class="text-xs font-bold text-gray-600 mb-3 uppercase">Scan to Pay via UPI</p>
                    <div id="qrcode" class="mb-2 mix-blend-multiply"></div>
                    <p class="text-[10px] text-gray-400">Accepted: GPay, PhonePe, Paytm, BHIM</p>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const upiId = "{{ $invoice->business->meta['upi_id'] }}";
                        const payName = "{{ str_replace(' ', '+', $invoice->business->name) }}";
                        const amount = "{{ round($invoice->grand_total) }}";
                        const upiUrl = `upi://pay?pa=${upiId}&pn=${payName}&am=${amount}&cu=INR`;

                        new QRCode(document.getElementById("qrcode"), {
                            text: upiUrl,
                            width: 128,
                            height: 128,
                            colorDark: "#4F46E5",
                            colorLight: "#F9FAFB",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    });
                </script>
            @endif

            <!-- Details Grid -->
            <div class="bg-gray-50 rounded-xl p-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Invoice Number</span>
                    <span class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Date</span>
                    <span class="font-medium text-gray-900">{{ $invoice->date->format('d M, Y') }}</span>
                </div>
                @if ($invoice->due_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Due Date</span>
                        <span class="font-medium text-gray-900">{{ $invoice->due_date->format('d M, Y') }}</span>
                    </div>
                @endif
                <div class="pt-2 border-t border-gray-200 mt-2"></div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Billed To</span>
                    <span class="font-semibold text-gray-900 text-right">{{ $invoice->party->name }}</span>
                </div>
            </div>

            <!-- Item Summary (Simplified) -->
            <div class="border-t border-gray-100 pt-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Item Summary</h4>
                <div class="space-y-3">
                    @foreach ($invoice->items as $item)
                        <div class="flex justify-between items-start text-sm">
                            <div class="pr-4">
                                <p class="font-medium text-gray-900">
                                    {{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ 0 + $item->quantity }} x
                                    ₹{{ number_format($item->unit_price, 2) }}</p>
                            </div>
                            <div class="font-medium text-gray-900 whitespace-nowrap">
                                ₹{{ number_format($item->total, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Subtotal/Tax Summary -->
                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-500 text-xs">
                        <span>Subtotal</span>
                        <span>₹{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500 text-xs">
                        <span>Tax</span>
                        <span>₹{{ number_format($invoice->tax_total, 2) }}</span>
                    </div>
                    @if ($invoice->discount_total > 0)
                        <div class="flex justify-between text-green-600 text-xs">
                            <span>Discount</span>
                            <span>-₹{{ number_format($invoice->discount_total, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Warning -->
            <div class="text-center space-y-4">
                <div class="bg-amber-50 text-amber-700 text-xs p-3 rounded-lg flex items-start text-left gap-2">
                    <svg class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>This is a digital copy for verification and payment purposes only. Please contact
                        <strong>{{ $invoice->business->name }}</strong> for the original tax invoice.
                    </p>
                </div>

                <p class="text-xs text-gray-400">
                    Secured by <a href="https://vedantbilling.com" class="hover:text-gray-600 transition-colors">Vedant
                        Billing</a>
                </p>
            </div>
        </div>
    </div>

</body>

</html>
