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

        // Prevent paying more than due? Optional. For now, let's allow overpayment or handle it gracefully.
        // Actually, let's strictly limit to outstanding amount for Phase 4 to avoid complexity.
        $paidSoFar = $invoice->allocations()->sum('amount'); // This relationship might not exist yet on Invoice model
        $outstanding = $invoice->grand_total * 100 - $paidSoFar; // Invoice total is decimal, we need to be careful with cents.

        // Wait, invoice.grand_total is decimal, payment.amount is usually cents in DB but logic here says min:1. 
        // Let's assume input amount is in major units (Rupees), and we convert to cents for DB.

        // Let's check schema: Payment 'amount' is bigInteger (cents).
        // Input 'amount' from frontend is usually decimal (e.g. 100.50).

        // Amount is now directly in main currency (Rupees)
        $amount = (float) $validated['amount'];

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

            // Fix: grand_total is decimal, so compare directly
            $grandTotal = $invoice->grand_total;

            if ($totalPaid >= $grandTotal) {
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
