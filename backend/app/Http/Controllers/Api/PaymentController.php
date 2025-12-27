<?php

namespace App\Http\Controllers\Api;

use App\Events\PaymentReceived;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
            'method' => ['required', 'string', 'in:cash,bank_transfer,check,upi,card,other'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // Amount is stored directly (Decimal)
        $amount = $validated['amount'];

        return DB::transaction(function () use ($validated, $invoice, $amount) {

            // 1. Create Payment
            $payment = Payment::create([
                'business_id' => $invoice->business_id,
                'customer_id' => $invoice->party_id,
                'amount' => $amount,
                'date' => $validated['date'],
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);

            // 2. Allocating to Invoice
            PaymentAllocation::create([
                'business_id' => $invoice->business_id,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
            ]);

            // 3. Update Invoice Status
            // Re-calculate total paid for this invoice
            $totalPaid = PaymentAllocation::where('invoice_id', $invoice->id)->sum('amount');

            if ($totalPaid >= $invoice->grand_total) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_amount' => $totalPaid
                ]);
            } elseif ($totalPaid > 0) {
                $invoice->update([
                    'status' => $invoice->status === 'paid' ? 'paid' : 'partial',
                    'paid_amount' => $totalPaid
                ]);
            } else {
                $invoice->update(['paid_amount' => 0]);
            }

            // 4. Dispatch Event for Ledger
            PaymentReceived::dispatch($payment);

            return response()->json($payment, 201);
        });
    }
}
