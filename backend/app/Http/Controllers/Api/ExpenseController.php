<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $businessId = $request->header('X-Business-Id');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        $query = Expense::where('business_id', $businessId);

        // Filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $expenses = $query->latest('date')->paginate(20);

        return response()->json($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $businessId = $request->header('X-Business-Id');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:50',
            // receipt_path handled via separate upload or if sent here? for now string.
        ]);

        $expense = Expense::create([
            'business_id' => $businessId,
            ...$validated
        ]);

        return response()->json($expense, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        // Add authorization check if needed (e.g. check business_id)
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date',
            'description' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:50',
        ]);

        $expense->update($validated);

        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->noContent();
    }
    /**
     * Scan a receipt image and return extracted data.
     */
    public function scan(Request $request, \App\Services\ReceiptScanningService $scanner)
    {
        $request->validate([
            'receipt' => 'required|image|max:10240', // Max 10MB
        ]);

        $file = $request->file('receipt');
        $result = $scanner->scan($file);

        if ($result['status'] === 'error') {
            return response()->json(['message' => $result['message']], 500);
        }

        return response()->json($result['data']);
    }
}
