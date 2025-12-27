<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CashbookController extends Controller
{
    /**
     * Display a listing of the cashbook (ledger).
     */
    public function index(Request $request)
    {
        $businessId = $request->header('X-Business-Id');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        // Totals
        // Payment amount is in cents, need to divide by 100
        $totalIn = Payment::where('business_id', $businessId)->sum('amount') / 100;
        $expensesOut = Expense::where('business_id', $businessId)->sum('amount');
        $creditNotesOut = \App\Models\Invoice::where('business_id', $businessId)
            ->where('type', 'credit_note')
            ->where('status', '!=', 'draft')
            ->sum('grand_total');

        $totalOut = $expensesOut + $creditNotesOut;
        $balance = $totalIn - $totalOut;

        // Debug Logging
        \Illuminate\Support\Facades\Log::info("Cashbook Request for Business: {$businessId}");
        \Illuminate\Support\Facades\Log::info("Totals: In={$totalIn}, Out={$totalOut}, Bal={$balance}");

        // Union Query for Ledger
        // We select minimal common fields to display in a list
        // Type: 'IN' for Payments, 'OUT' for Expenses

        // Let's try a cleaner approach: attributes projection.
        // We will project: id, date, amount, type, title (Customer Name or Expense Category), description.

        $incomeQuery = Payment::query()
            ->where('payments.business_id', $businessId)
            ->leftJoin('parties', 'payments.customer_id', '=', 'parties.id')
            ->select([
                'payments.id',
                'payments.date',
                DB::raw('payments.amount / 100 as amount'), // Convert cents to units
                DB::raw("'IN' as type"),
                DB::raw("COALESCE(parties.name, 'Unknown Customer') as title"), // Customer Name
                'payments.notes as description',
                'payments.method as payment_method',
                'payments.created_at'
            ]);

        $expenseQuery = Expense::query()
            ->where('expenses.business_id', $businessId)
            ->select([
                'expenses.id',
                'expenses.date',
                'expenses.amount', // Already in units
                DB::raw("'OUT' as type"),
                'expenses.category as title', // Expense Category
                'expenses.description as description',
                'expenses.payment_method',
                'expenses.created_at'
            ]);

        $creditNoteQuery = DB::table('invoices')
            ->leftJoin('parties', 'invoices.party_id', '=', 'parties.id')
            ->where('invoices.business_id', $businessId)
            ->where('invoices.type', 'credit_note')
            ->where('invoices.status', '!=', 'draft') // Only final ones
            ->select([
                'invoices.id',
                'invoices.date',
                'invoices.grand_total as amount', // Already in units
                DB::raw("'OUT' as type"),
                DB::raw("COALESCE(parties.name, 'Unknown Party') as title"),
                DB::raw("CONCAT('Credit Note: ', invoices.invoice_number) as description"),
                DB::raw("'credit_note' as payment_method"),
                'invoices.created_at'
            ]);

        // Merge queries
        $unionQuery = $incomeQuery->union($expenseQuery)->union($creditNoteQuery);

        // Wrap in a subquery to allow global sorting and filtering on the union result
        // This handles bindings automatically compared to manual mergeBindings
        $query = DB::query()->fromSub($unionQuery, 'cashbook');

        // Filters
        if ($request->input('start_date')) {
            $query->where('date', '>=', $request->input('start_date'));
        }
        if ($request->input('end_date')) {
            $query->where('date', '<=', $request->input('end_date'));
        }

        // Sorting
        $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        $entries = $query->paginate(20);

        return response()->json([
            'summary' => [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'balance' => $balance
            ],
            'entries' => $entries
        ]);
    }
}
