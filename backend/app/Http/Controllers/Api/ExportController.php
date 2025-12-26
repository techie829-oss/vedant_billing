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
}
