<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class PartyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Party::class);

        $query = Party::query();

        if ($request->has('type')) {
            $query->where('party_type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $parties = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($parties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Party::class);

        $validated = $request->validate([
            'party_type' => ['required', 'in:customer,vendor'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'billing_address' => ['nullable', 'array'],
            'shipping_address' => ['nullable', 'array'],
            'opening_balance' => ['nullable', 'numeric'],
        ]);

        $party = Party::create($validated);

        return response()->json($party, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Party $party)
    {
        Gate::authorize('view', $party);
        return response()->json($party);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Party $party)
    {
        Gate::authorize('update', $party);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'billing_address' => ['nullable', 'array'],
            'shipping_address' => ['nullable', 'array'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $party->update($validated);

        return response()->json($party);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Party $party)
    {
        Gate::authorize('delete', $party);

        $party->delete();

        return response()->noContent();
    }

    /**
     * Get Party Ledger (Statement)
     */
    public function ledger(Request $request, Party $party)
    {
        Gate::authorize('view', $party);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date', now()->toDateString());

        // 1. Get Invoices
        $invoicesQuery = \App\Models\Invoice::where('party_id', $party->id)
            ->where('status', '!=', 'draft');
        
        if ($startDate) $invoicesQuery->where('date', '>=', $startDate);
        if ($endDate) $invoicesQuery->where('date', '<=', $endDate);

        $invoices = $invoicesQuery->get()->map(function($inv) {
            $isSale = in_array($inv->type, ['tax_invoice', 'invoice', 'bill_of_supply']);
            return [
                'date' => $inv->date,
                'type' => $inv->type,
                'number' => $inv->invoice_number,
                'description' => ucfirst(str_replace('_', ' ', $inv->type)) . ' #' . $inv->invoice_number,
                'debit' => $isSale ? $inv->grand_total : 0,
                'credit' => ($inv->type === 'purchase_invoice' || $inv->type === 'credit_note') ? $inv->grand_total : 0,
                'reference_id' => $inv->id,
                'sort_key' => $inv->date . '_1_' . $inv->id
            ];
        });

        // 2. Get Payments
        $paymentsQuery = \App\Models\Payment::where('customer_id', $party->id)
            ->where('status', 'completed');

        if ($startDate) $paymentsQuery->where('date', '>=', $startDate);
        if ($endDate) $paymentsQuery->where('date', '<=', $endDate);

        $payments = $paymentsQuery->get()->map(function($pay) use ($party) {
            return [
                'date' => $pay->date,
                'type' => 'payment',
                'number' => $pay->reference,
                'description' => 'Payment Received (' . ucfirst($pay->method) . ')' . ($pay->reference ? ' Ref: ' . $pay->reference : ''),
                'debit' => 0, // Payment reduces what customer owes (Credit)
                'credit' => $pay->amount,
                'reference_id' => $pay->id,
                'sort_key' => $pay->date . '_2_' . $pay->id
            ];
        });

        // 3. Merge and Sort
        $transactions = $invoices->concat($payments)->sortBy('sort_key')->values();

        // 4. Calculate Running Balance
        $openingBalance = (float) ($party->opening_balance ?? 0);
        
        // If we have a start date, we need to calculate the balance before that date
        if ($startDate) {
            $prevInvoices = \App\Models\Invoice::where('party_id', $party->id)
                ->where('status', '!=', 'draft')
                ->where('date', '<', $startDate)
                ->get();
            
            foreach ($prevInvoices as $inv) {
                $isSale = in_array($inv->type, ['tax_invoice', 'invoice', 'bill_of_supply']);
                if ($isSale) $openingBalance += $inv->grand_total;
                if ($inv->type === 'purchase_invoice' || $inv->type === 'credit_note') $openingBalance -= $inv->grand_total;
            }

            $prevPayments = \App\Models\Payment::where('customer_id', $party->id)
                ->where('status', 'completed')
                ->where('date', '<', $startDate)
                ->sum('amount');
            
            $openingBalance -= $prevPayments;
        }

        $runningBalance = $openingBalance;
        $ledger = $transactions->map(function($tx) use (&$runningBalance) {
            $runningBalance += ($tx['debit'] - $tx['credit']);
            $tx['balance'] = $runningBalance;
            return $tx;
        });

        return response()->json([
            'party' => $party,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'ledger' => $ledger
        ]);
    }
}
