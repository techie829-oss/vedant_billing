<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Ledger;

class ReportController extends Controller
{
    /**
     * Profit & Loss Report
     * Incomes vs Expenses
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $businessId = $request->header('X-Business-Id');

        // 1. Calculate Income (Sales Revenue)
        $invoices = Invoice::where('business_id', $businessId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['sent', 'paid', 'partial', 'overdue'])
            ->with(['items.product'])
            ->get();

        $totalRevenue = $invoices->sum('subtotal'); // Pre-tax revenue

        // 2. Calculate COGS (Direct Expenses)
        $cogs = 0;
        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $cost = $item->product ? ($item->product->purchase_price * $item->quantity) : 0;
                $cogs += $cost;
            }
        }

        // 3. Calculate Operational Expenses (Indirect Expenses)
        $expenses = \App\Models\Expense::where('business_id', $businessId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        $totalOperatingExpenses = $expenses->sum('total');

        $incomeAccounts = [
            [
                'id' => 'sales_revenue',
                'name' => 'Sales Revenue',
                'amount' => $totalRevenue
            ]
        ];

        $expenseAccounts = [
            [
                'id' => 'cogs',
                'name' => 'Cost of Goods Sold',
                'amount' => $cogs
            ]
        ];

        foreach ($expenses as $exp) {
            $expenseAccounts[] = [
                'id' => 'opex_' . md5($exp->category),
                'name' => $exp->category,
                'amount' => (float) $exp->total
            ];
        }

        $grossProfit = $totalRevenue - $cogs;
        $netProfit = $grossProfit - $totalOperatingExpenses;

        return response()->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'income' => [
                'total' => $totalRevenue,
                'accounts' => $incomeAccounts
            ],
            'expenses' => [
                'total' => $cogs + $totalOperatingExpenses,
                'accounts' => $expenseAccounts,
                'cogs' => $cogs,
                'operating' => $totalOperatingExpenses
            ],
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit
        ]);
    }

    /**
     * Tax Summary Report (GST)
     */
    public function taxSummary(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $businessId = $request->header('X-Business-Id');

        $summary = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.business_id', $businessId)
            ->whereBetween('invoices.date', [$startDate, $endDate])
            ->whereIn('invoices.status', ['sent', 'paid', 'partial', 'overdue'])
            ->select(
                'invoice_items.tax_rate',
                DB::raw('SUM(invoice_items.quantity * invoice_items.unit_price) as taxable_value'),
                DB::raw('SUM(invoice_items.tax_amount) as total_tax')
            )
            ->groupBy('invoice_items.tax_rate')
            ->orderBy('invoice_items.tax_rate')
            ->get();

        $data = $summary->map(function ($row) {
            return [
                'rate' => $row->tax_rate,
                'taxable_value' => $row->taxable_value,
                'total_tax' => $row->total_tax,
                'cgst_share' => $row->total_tax / 2,
                'sgst_share' => $row->total_tax / 2,
            ];
        });

        return response()->json([
            'period' => ['start' => $startDate, 'end' => $endDate],
            'summary' => $data,
            'totals' => [
                'taxable' => $data->sum('taxable_value'),
                'tax' => $data->sum('total_tax')
            ]
        ]);
    }
    /**
     * Sales Report
     * Returns list of invoices within a date range, with totals.
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $customerId = $request->input('customer_id');

        $query = Invoice::with('party')
            ->whereIn('status', ['sent', 'paid', 'partial', 'overdue'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($customerId) {
            $query->where('party_id', $customerId);
        }

        $invoices = $query->orderBy('date', 'desc')->get();

        // Calculate totals
        $totals = [
            'count' => $invoices->count(),
            'total_amount' => $invoices->sum('grand_total'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'balance_due' => $invoices->sum(function ($inv) {
                return $inv->grand_total - $inv->paid_amount;
            }),
            'tax_collected' => $invoices->sum('tax_total'),
        ];

        return response()->json([
            'data' => $invoices,
            'totals' => $totals,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]);
    }

    /**
     * Outstanding Report (Debtors)
     * Returns customers with positive balance due.
     */
    public function outstanding(Request $request)
    {
        // Strategy: Get parties with 'customer' type, and sum up their due invoices.
        // Doing this via Invoice aggregation is usually faster for "who owes what".

        $minBalance = $request->input('min_balance', 1); // Ignore trivial amounts < 1

        $debtors = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
            ->select('party_id', DB::raw('SUM(grand_total - paid_amount) as total_due'), DB::raw('COUNT(id) as invoice_count'))
            ->groupBy('party_id')
            ->havingRaw('SUM(grand_total - paid_amount) >= ?', [$minBalance])
            ->with([
                'party' => function ($q) {
                    $q->select('id', 'name', 'phone', 'email');
                }
            ])
            ->orderBy('total_due', 'desc')
            ->get();

        // Flatten the structure for easier frontend consumption
        $report = $debtors->map(function ($item) {
            return [
                'customer_id' => $item->party_id,
                'customer_name' => $item->party->name ?? 'Unknown',
                'customer_contact' => $item->party->phone ?? $item->party->email ?? '',
                'invoice_count' => $item->invoice_count,
                'total_due' => $item->total_due
            ];
        });

        return response()->json([
            'data' => $report,
            'total_outstanding' => $report->sum('total_due')
        ]);
    }

    /**
     * Stock Report
     * Returns inventory list with current stock and valuation.
     */
    public function stock(Request $request)
    {
        $lowStockOnly = $request->boolean('low_stock_only');

        $query = Product::where('type', 'goods');

        if ($lowStockOnly) {
            // Assuming low stock is < 10 for now, or just <= 0 if strict
            // Let's stick to simple "Available" vs "Out of stock" logic for filtering?
            // Or maybe just generic low stock threshold. Let's say 5.
            $query->where('current_stock', '<=', 5);
        }

        $products = $query->orderBy('name', 'asc')->get();

        $valuation = $products->sum(function ($p) {
            return $p->current_stock * $p->purchase_price;
        });

        return response()->json([
            'data' => $products,
            'total_items' => $products->count(),
            'total_stock_qty' => $products->sum('current_stock'),
            'total_valuation' => $valuation
        ]);
    }
}
