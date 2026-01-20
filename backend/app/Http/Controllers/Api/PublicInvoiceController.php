<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PublicInvoiceController extends Controller
{
    /**
     * Display the specified resource.
     * Public access via UUID.
     */
    public function show($id)
    {
        // Find invoice by UUID, ignoring global scopes (like business_id auth check)
        // Since UUIDs are unguessable, this serves as a secure link.
        $invoice = Invoice::withoutGlobalScopes()
            ->with(['items', 'items.product', 'party', 'business', 'allocations.payment']) // Eager load relationships needed for PDF
            ->where('id', $id)
            ->firstOrFail();

        // Optional: Sanitize sensitive business data if needed
        // For now, we return full invoice data as it's what is shown on the PDF anyway.

        // Return Blade View
        return view('public.invoice', compact('invoice'));
    }
}
