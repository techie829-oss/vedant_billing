<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Party;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payment;

class ExportController extends Controller
{
    public function download(Request $request)
    {
        $businessId = $request->header('X-Business-Id');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        $business = Business::find($businessId);
        if (!$business) {
            return response()->json(['message' => 'Business not found'], 404);
        }

        $data = [
            'business' => $business,
            'customers' => Party::where('business_id', $businessId)->get(),
            'products' => Product::where('business_id', $businessId)->get(),
            'invoices' => Invoice::where('business_id', $businessId)->with('items')->get(),
            'expenses' => Expense::where('business_id', $businessId)->get(),
            'payments' => Payment::where('business_id', $businessId)->get(),
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->json($data);
    }

    public function export(Request $request, $type)
    {
        $businessId = $request->header('X-Business-Id');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $type . '_' . date('Y-m-d_His') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($businessId, $type) {
            $file = fopen('php://output', 'w');

            if ($type === 'invoices') {
                fputcsv($file, ['Invoice No', 'Date', 'Customer', 'Status', 'Grand Total', 'Balance', 'Created At']);
                Invoice::where('business_id', $businessId)
                    ->with('party')
                    ->chunk(100, function ($invoices) use ($file) {
                        foreach ($invoices as $inv) {
                            fputcsv($file, [
                                $inv->invoice_number,
                                $inv->date,
                                $inv->party->name ?? 'Unknown',
                                $inv->status,
                                $inv->grand_total,
                                $inv->grand_total - $inv->paid_amount,
                                $inv->created_at
                            ]);
                        }
                    });
            } elseif ($type === 'invoice_items') {
                fputcsv($file, ['Invoice No', 'Date', 'Customer', 'Item Name', 'HSN/SAC', 'Quantity', 'Unit Price', 'Tax Amount', 'Total Amount']);

                // Efficiently chunk through invoices with their items
                Invoice::where('business_id', $businessId)
                    ->with(['party', 'items'])
                    ->chunk(100, function ($invoices) use ($file) {
                        foreach ($invoices as $inv) {
                            foreach ($inv->items as $item) {
                                fputcsv($file, [
                                    $inv->invoice_number,
                                    $inv->date,
                                    $inv->party->name ?? 'Unknown',
                                    $item->name ?: $item->description,
                                    $item->hsn_code,
                                    $item->quantity,
                                    $item->unit_price,
                                    $item->tax_amount,
                                    $item->total
                                ]);
                            }
                        }
                    });
            } elseif ($type === 'parties') {
                fputcsv($file, ['Name', 'Phone', 'Type', 'GSTIN', 'Balance', 'Created At']);
                Party::where('business_id', $businessId)
                    ->chunk(100, function ($parties) use ($file) {
                        foreach ($parties as $party) {
                            fputcsv($file, [
                                $party->name,
                                $party->phone,
                                $party->type,
                                $party->gstin,
                                $party->balance,
                                $party->created_at
                            ]);
                        }
                    });
            } elseif ($type === 'expenses') {
                fputcsv($file, ['Date', 'Category', 'Amount', 'Description', 'Method', 'Created At']);
                Expense::where('business_id', $businessId)
                    ->chunk(100, function ($expenses) use ($file) {
                        foreach ($expenses as $exp) {
                            fputcsv($file, [
                                $exp->date,
                                $exp->category,
                                $exp->amount,
                                $exp->description,
                                $exp->payment_method,
                                $exp->created_at
                            ]);
                        }
                    });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
