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
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', \App\Models\Payment::class);

        $validated = $request->validate([
            'party_id' => ['required_without:invoice_id', 'exists:parties,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'method' => ['required', 'string', 'in:cash,bank_transfer,check,upi,card,other'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'auto_allocate' => ['nullable', 'boolean'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.invoice_id' => ['required_with:allocations', 'exists:invoices,id'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
        ]);

        $businessId = auth()->user()->currentBusinessId();
        $amount = (float) $validated['amount'];
        $partyId = $validated['party_id'] ?? null;

        // If invoice_id provided (legacy/simple mode), resolve party_id
        if (isset($validated['invoice_id'])) {
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $partyId = $invoice->party_id;
        }

        return DB::transaction(function () use ($validated, $businessId, $amount, $partyId) {

            // 1. Create Payment Record
            $payment = Payment::create([
                'business_id' => $businessId,
                'customer_id' => $partyId,
                'amount' => $amount,
                'date' => $validated['date'],
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);

            $remainingToAllocate = $amount;

            // 2. Handle Allocations
            if (isset($validated['invoice_id'])) {
                // Scenario A: Single Invoice (Legacy)
                $this->allocateToInvoice($payment, $validated['invoice_id'], $amount);
            } 
            elseif (isset($validated['allocations'])) {
                // Scenario B: Manual Multi-Allocation
                foreach ($validated['allocations'] as $alloc) {
                    $allocAmt = min($remainingToAllocate, (float)$alloc['amount']);
                    if ($allocAmt <= 0) continue;
                    
                    $this->allocateToInvoice($payment, $alloc['invoice_id'], $allocAmt);
                    $remainingToAllocate -= $allocAmt;
                }
            } 
            elseif ($validated['auto_allocate'] ?? false) {
                // Scenario C: Auto-Allocate (FIFO)
                $pendingInvoices = Invoice::where('party_id', $partyId)
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->whereIn('type', ['tax_invoice', 'invoice', 'bill_of_supply'])
                    ->orderBy('date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($pendingInvoices as $inv) {
                    if ($remainingToAllocate <= 0) break;

                    $due = $inv->grand_total - $inv->paid_amount;
                    if ($due <= 0) continue;

                    $allocAmt = min($remainingToAllocate, $due);
                    $this->allocateToInvoice($payment, $inv->id, $allocAmt);
                    $remainingToAllocate -= $allocAmt;
                }
            }

            // 3. Dispatch Event for Ledger & Party Balance
            PaymentReceived::dispatch($payment);

            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment' => $payment->load('allocations.invoice'),
                'unallocated_amount' => $remainingToAllocate
            ], 201);
        });
    }

    protected function allocateToInvoice(Payment $payment, $invoiceId, $amount)
    {
        // 1. Create Allocation
        PaymentAllocation::create([
            'business_id' => $payment->business_id,
            'payment_id' => $payment->id,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
        ]);

        // 2. Update Invoice Status & Paid Amount
        $invoice = Invoice::lockForUpdate()->find($invoiceId);
        if ($invoice) {
            $totalPaid = PaymentAllocation::where('invoice_id', $invoiceId)->sum('amount');
            
            $status = 'partial';
            if ($totalPaid >= $invoice->grand_total) {
                $status = 'paid';
            }

            $invoice->update([
                'status' => $status,
                'paid_amount' => $totalPaid
            ]);
        }
    }
}
