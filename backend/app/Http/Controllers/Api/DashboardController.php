<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard metrics.
     */
    public function index(Request $request)
    {
        // Scope to current business automatically handled by MultiTenancy/GlobalScopes usually,
        // but explicit check is safer if scopes aren't applied everywhere yet.
        // Assuming currentBusinessId() returns the ID from token/session.
        // Or if we use models, they should have the scope boot.

        $metrics = [
            'revenue' => Invoice::whereIn('status', ['paid', 'partial', 'sent', 'overdue']) // Revenue = Total Sales Value
                ->sum('grand_total'),

            // Outstanding = Total Due
            // Logic: Sum of (grand_total - paid_amount) for unpaid invoices
            'outstanding' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->selectRaw('SUM(grand_total - paid_amount) as outstanding')
                ->value('outstanding') ?? 0,

            'customers' => Party::where('party_type', 'customer')->count(),

            'products' => Product::count(),

            'total_expenses' => Expense::sum('amount'), // Total Expenses
        ];

        // Recent Activity
        $recentInvoices = Invoice::with('party')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->party->name ?? 'Unknown',
                    'date' => $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') : null,
                    'amount' => $invoice->grand_total,
                    'status' => $invoice->status,
                ];
            });

        // Chart Data (Last 6 Months Sales)
        $chartData = Invoice::selectRaw("TO_CHAR(date, 'YYYY-MM') as month, SUM(grand_total) as total")
            ->whereIn('status', ['paid', 'partial', 'sent', 'overdue'])
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Chart Data (Last 6 Months Sales) -> RENAMED to Sales Trend (Invoices)
        $salesChart = Invoice::selectRaw("TO_CHAR(date, 'YYYY-MM') as month, SUM(grand_total) as total")
            ->whereIn('status', ['paid', 'partial', 'sent', 'overdue'])
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Cashflow Chart (Income vs Expense) - Last 6 Months
        // Income = Payments received
        $incomeData = Payment::selectRaw("TO_CHAR(date, 'YYYY-MM') as month, SUM(amount) as total")
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $expenseData = Expense::selectRaw("TO_CHAR(date, 'YYYY-MM') as month, SUM(amount) as total")
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Merge months
        $months = $incomeData->keys()->merge($expenseData->keys())->unique()->sort()->values();
        $cashflowChart = $months->map(function ($month) use ($incomeData, $expenseData) {
            return [
                'month' => $month,
                'income' => $incomeData[$month] ?? 0,
                'expense' => $expenseData[$month] ?? 0,
                'profit' => ($incomeData[$month] ?? 0) - ($expenseData[$month] ?? 0),
            ];
        });

        return response()->json([
            'metrics' => $metrics,
            'recent_activity' => $recentInvoices,
            'sales_chart' => $salesChart,
            'cashflow_chart' => $cashflowChart
        ]);
    }
}
