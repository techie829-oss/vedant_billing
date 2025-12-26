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
        $totalIn = Payment::where('business_id', $businessId)->sum('amount');
        $expensesOut = Expense::where('business_id', $businessId)->sum('amount');
        $creditNotesOut = \App\Models\Invoice::where('business_id', $businessId)
            ->where('type', 'credit_note')
            ->where('status', '!=', 'draft')
            ->sum('grand_total');

        $totalOut = $expensesOut + $creditNotesOut;
        $balance = $totalIn - $totalOut;

        // Union Query for Ledger
        // We select minimal common fields to display in a list
        // Type: 'IN' for Payments, 'OUT' for Expenses

        // Let's try a cleaner approach: attributes projection.
        // We will project: id, date, amount, type, title (Customer Name or Expense Category), description.

        $incomeQuery = Payment::query()
            ->where('payments.business_id', $businessId)
            ->join('parties', 'payments.customer_id', '=', 'parties.id')
            ->select([
                'payments.id',
                'payments.date',
                'payments.amount',
                DB::raw("'IN' as type"),
                'parties.name as title', // Customer Name
                'payments.notes as description',
                'payments.payment_method',
                'payments.created_at'
            ]);

        $expenseQuery = Expense::query()
            ->where('expenses.business_id', $businessId)
            ->select([
                'expenses.id',
                'expenses.date',
                'expenses.amount',
                DB::raw("'OUT' as type"),
                'expenses.category as title', // Expense Category
                'expenses.description as description',
                'expenses.payment_method',
                'expenses.created_at'
            ]);

        $creditNoteQuery = DB::table('invoices')
            ->join('parties', 'invoices.party_id', '=', 'parties.id')
            ->where('invoices.business_id', $businessId)
            ->where('invoices.type', 'credit_note')
            ->where('invoices.status', '!=', 'draft') // Only final ones
            ->select([
                'invoices.id',
                'invoices.date',
                'invoices.grand_total as amount',
                DB::raw("'OUT' as type"),
                'parties.name as title',
                DB::raw("CONCAT('Credit Note: ', invoices.invoice_number) as description"),
                DB::raw("'credit_note' as payment_method"),
                'invoices.created_at'
            ]);

        // Merge queries
        $query = $incomeQuery->union($expenseQuery)->union($creditNoteQuery);

        // Filters
        if ($request->has('start_date')) {
            // This is tricky with union, usually applied before union or wrapped.
            // For simplicity in Laravel, standard Union allows subsequent ordering but where clauses might need to be on parts.
            // Let's apply basic filters to both parts if possible or wrapper.
            // wrapper approach:
            $query = DB::table(DB::raw("({$query->toSql()}) as cashbook"))
                ->mergeBindings($incomeQuery->getQuery())
                ->mergeBindings($expenseQuery->getQuery())
                ->mergeBindings($creditNoteQuery); // bindings needed

            if ($request->has('start_date')) {
                $query->where('date', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('date', '<=', $request->end_date);
            }
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
