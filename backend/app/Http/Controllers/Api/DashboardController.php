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
            'revenue' => Invoice::whereIn('status', ['paid', 'partial', 'sent', 'overdue'])
                ->whereNotIn('type', ['purchase_invoice'])
                ->sum('grand_total'),

            'outstanding' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->whereNotIn('type', ['purchase_invoice'])
                ->selectRaw('SUM(grand_total - paid_amount) as outstanding')
                ->value('outstanding') ?? 0,

            'customers' => Party::where('party_type', 'customer')->count(),
            'vendors' => Party::where('party_type', 'vendor')->count(),

            'products' => Product::count(),

            'total_expenses' => Expense::sum('amount'),

            'total_purchases' => Invoice::where('type', 'purchase_invoice')
                ->whereIn('status', ['draft', 'sent', 'paid', 'partial'])
                ->sum('grand_total'),

            'payable_to_vendors' => Invoice::where('type', 'purchase_invoice')
                ->whereIn('status', ['sent', 'partial', 'draft'])
                ->selectRaw('SUM(grand_total - COALESCE(paid_amount, 0)) as payable')
                ->value('payable') ?? 0,
        ];

        // Recent Sales Invoices
        $recentInvoices = Invoice::with('party')
            ->whereNotIn('type', ['purchase_invoice'])
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

        // Recent Purchase Invoices
        $recentPurchases = Invoice::with('party')
            ->where('type', 'purchase_invoice')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'vendor_name' => $invoice->party->name ?? 'Unknown',
                    'date' => $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') : null,
                    'amount' => $invoice->grand_total,
                    'status' => $invoice->status,
                ];
            });

        // Sales Chart (Last 6 Months)
        $salesChart = Invoice::selectRaw("TO_CHAR(date, 'YYYY-MM') as month, SUM(grand_total) as total")
            ->whereIn('status', ['paid', 'partial', 'sent', 'overdue'])
            ->whereNotIn('type', ['purchase_invoice'])
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Cashflow Chart (Income vs Expense) - Last 6 Months
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

        $months = $incomeData->keys()->merge($expenseData->keys())->unique()->sort()->values();
        $cashflowChart = $months->map(function ($month) use ($incomeData, $expenseData) {
            return [
                'month' => $month,
                'income' => $incomeData[$month] ?? 0,
                'expense' => $expenseData[$month] ?? 0,
                'profit' => ($incomeData[$month] ?? 0) - ($expenseData[$month] ?? 0),
            ];
        });

        // Low Stock Products
        $lowStockProducts = Product::where('current_stock', '<=', 10)
            ->where('type', 'goods')
            ->where('status', 'active')
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'metrics' => $metrics,
            'recent_activity' => $recentInvoices,
            'recent_purchases' => $recentPurchases,
            'low_stock_products' => $lowStockProducts,
            'sales_chart' => $salesChart,
            'cashflow_chart' => $cashflowChart
        ]);
    }
}
