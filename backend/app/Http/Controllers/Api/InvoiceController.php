<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Events\InvoiceFinalized;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $query = Invoice::query()->with(['party']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by party
        if ($request->has('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Filter by type (invoice vs credit_note)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $invoices = $query->orderBy('date', 'desc')
            ->orderBy('invoice_number', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage (Draft).
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Invoice::class);

        $validated = $request->validate([
            'type' => 'nullable|in:tax_invoice,bill_of_supply,proforma_invoice,delivery_challan,credit_note,debit_note',
            'parent_id' => 'nullable|exists:invoices,id',
            'reason' => 'nullable|string',
            'party_id' => 'required|exists:parties,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.hsn_code' => 'nullable|string|max:20',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'challan_no' => 'nullable|string|max:50',
            'eway_bill_no' => 'nullable|string|max:50',
            'vehicle_no' => 'nullable|string|max:50',
            'po_number' => 'nullable|string|max:50',
            'meta' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $type = $validated['type'] ?? 'tax_invoice'; // Default to tax invoice
            $invoiceNumber = $this->getNextInvoiceNumber($type);

            $invoice = Invoice::create([
                'type' => $type,
                'parent_id' => $validated['parent_id'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'party_id' => $validated['party_id'],
                'invoice_number' => $invoiceNumber,
                'date' => $validated['date'],
                'due_date' => $validated['due_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'challan_no' => $validated['challan_no'] ?? null,
                'eway_bill_no' => $validated['eway_bill_no'] ?? null,
                'vehicle_no' => $validated['vehicle_no'] ?? null,
                'po_number' => $validated['po_number'] ?? null,
                'meta' => $validated['meta'] ?? null,
            ]);

            $this->syncItems($invoice, $validated['items']);

            return response()->json($invoice->load('items'), 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        return response()->json($invoice->load(['items', 'party', 'business', 'allocations.payment', 'parent', 'creditNotes']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        if (!$invoice->isEditable()) {
            return response()->json(['message' => 'Cannot edit a finalized invoice.'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:tax_invoice,bill_of_supply,proforma_invoice,delivery_challan,credit_note,debit_note',
            'parent_id' => 'nullable|exists:invoices,id',
            'reason' => 'nullable|string',
            'party_id' => 'sometimes|exists:parties,id',
            'date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:date',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.name' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.hsn_code' => 'nullable|string|max:20',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'challan_no' => 'nullable|string|max:50',
            'eway_bill_no' => 'nullable|string|max:50',
            'vehicle_no' => 'nullable|string|max:50',
            'po_number' => 'nullable|string|max:50',
            'meta' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $invoice) {
            // Check if type is changing
            if (isset($validated['type']) && $validated['type'] !== $invoice->type) {
                // Generate new number for new type
                $validated['invoice_number'] = $this->getNextInvoiceNumber($validated['type']);
            }

            $invoice->update($validated); // Updates basic fields if present

            if (isset($validated['items'])) {
                // Replace items approach (Delete all and re-create)
                $invoice->items()->delete();
                $this->syncItems($invoice, $validated['items']);
            }

            return response()->json($invoice->load('items'));
        });
    }

    /**
     * Finalize the invoice (Draft -> Sent).
     */
    public function finalize(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return response()->json(['message' => 'Invoice is not in draft status.'], 422);
        }

        // TODO: Validate stock availability etc if needed

        $invoice->update(['status' => 'sent']);

        // Dispatch event to create ledger entries
        InvoiceFinalized::dispatch($invoice);

        return response()->json($invoice);
    }

    /**
     * Helper to process items and calculate totals.
     */
    protected function syncItems(Invoice $invoice, array $itemsData)
    {
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($itemsData as $itemData) {
            $qty = $itemData['quantity'];
            $price = $itemData['unit_price'];
            $discount = $itemData['discount'] ?? 0;
            $taxRate = $itemData['tax_rate'] ?? 0;

            // Calculate tax on discounted amount
            $baseAmount = ($qty * $price) - $discount;
            $taxable = $baseAmount > 0 ? $baseAmount : 0;

            $taxAmount = $taxable * ($taxRate / 100);
            $total = $taxable + $taxAmount;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $itemData['product_id'] ?? null,
                'name' => $itemData['name'] ?? null,
                'description' => $itemData['description'] ?? '',
                'hsn_code' => $itemData['hsn_code'] ?? null,
                'quantity' => $qty,
                'unit_price' => $price,
                'discount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            $subtotal += $taxable;
            $taxTotal += $taxAmount;
        }

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            // 'discount_total' => ?? We should probably sum discounts but schema has it on invoice table?
            // For now, grand_total is net.
            'grand_total' => $subtotal + $taxTotal,
        ]);
    }

    /**
     * Duplicate an invoice (create a new draft copy).
     */
    /**
     * Duplicate an invoice (create a new draft copy).
     */
    public function duplicate(Invoice $invoice)
    {
        return DB::transaction(function () use ($invoice) {
            // Determine Prefix
            $type = $invoice->type ?? 'tax_invoice';
            $newInvoiceNumber = $this->getNextInvoiceNumber($type);

            // Create new invoice with same data but as draft
            $newInvoice = Invoice::create([
                'type' => $type,
                'party_id' => $invoice->party_id,
                'invoice_number' => $newInvoiceNumber,
                'date' => now()->format('Y-m-d'), // Today's date
                'due_date' => now()->addDays(30)->format('Y-m-d'), // 30 days from now
                'status' => 'draft',
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
                'challan_no' => null, // Clear transport details for new invoice
                'eway_bill_no' => null,
                'vehicle_no' => null,
                'po_number' => null,
                'meta' => $invoice->meta, // Copy display options and addresses
            ]);

            // Copy items using syncItems (which handles totals calculation)
            $itemsData = $invoice->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'hsn_code' => $item->hsn_code,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate' => $item->tax_rate,
                ];
            })->toArray();

            $this->syncItems($newInvoice, $itemsData);

            return response()->json($newInvoice->load(['items', 'party']), 201);
        });
    }

    /**
     * Convert a quote/estimate to an invoice.
     */
    public function convert(Invoice $invoice)
    {
        return DB::transaction(function () use ($invoice) {
            $type = 'tax_invoice';
            $newInvoiceNumber = $this->getNextInvoiceNumber($type);

            // Create new invoice linked to the quote
            $newInvoice = Invoice::create([
                'type' => $type,
                'parent_id' => $invoice->id, // Link to original quote
                'party_id' => $invoice->party_id,
                'invoice_number' => $newInvoiceNumber,
                'date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'status' => 'draft',
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
                'meta' => $invoice->meta,
            ]);

            // Copy items
            $itemsData = $invoice->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'hsn_code' => $item->hsn_code,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate' => $item->tax_rate,
                ];
            })->toArray();

            $this->syncItems($newInvoice, $itemsData);

            // Optionally update status of quote to accepted?
            // $invoice->update(['status' => 'accepted']);

            return response()->json($newInvoice->load(['items', 'party']), 201);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        if (!$invoice->isEditable()) {
            return response()->json(['message' => 'Cannot delete a finalized invoice.'], 403);
        }
        $invoice->delete();
        return response()->noContent();
    }

    /**
     * Generate and download PDF.
     */
    public function download(Invoice $invoice)
    {
        $invoice->load(['items', 'party', 'business']);

        // Amount in words
        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $amountInWords = ucfirst($f->format((float) $invoice->grand_total));

        // Tax Breakdown Logic (Simplified for PDF)
        // Check if Inter-state (IGST) or Intra-state (CGST+SGST)
        // Logic: If Customer State != Business State => IGST
        $businessState = $invoice->business->meta['state'] ?? '';
        $customerState = $invoice->party->billing_address['state'] ?? ($invoice->party->shipping_address['state'] ?? '');

        $isIgst = strtolower(trim($businessState)) !== strtolower(trim($customerState));
        $taxBreakdown = [
            'is_igst' => $isIgst,
            'taxval' => $invoice->tax_total
        ];

        // Determine View based on Business Meta
        $layout = $invoice->business->meta['invoice_layout'] ?? 'default';
        $view = match ($layout) {
            'professional' => 'pdf.professional',
            'grid_premium' => 'pdf.professional', // Fallback to prof for now or could add grid later
            default => 'pdf.default',
        };

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            'invoice' => $invoice,
            'business' => $invoice->business,
            'party' => $invoice->party,
            'amountInWords' => $amountInWords,
            'taxBreakdown' => $taxBreakdown,
            'brandColor' => $invoice->business->meta['brand_color'] ?? '#1f2937',
        ]);

        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Email the invoice PDF to the customer.
     */
    public function email(Invoice $invoice)
    {
        $invoice->load(['items', 'party', 'business']);

        if (!$invoice->party->email) {
            return response()->json(['message' => 'Customer does not have an email address.'], 422);
        }

        // Amount in words
        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $amountInWords = ucfirst($f->format((float) $invoice->grand_total));

        // Tax logic
        $businessState = $invoice->business->meta['state'] ?? '';
        $customerState = $invoice->party->billing_address['state'] ?? ($invoice->party->shipping_address['state'] ?? '');
        $isIgst = strtolower(trim($businessState)) !== strtolower(trim($customerState));
        $taxBreakdown = ['is_igst' => $isIgst, 'taxval' => $invoice->tax_total];

        // Determine View
        $layout = $invoice->business->meta['invoice_layout'] ?? 'default';
        $view = match ($layout) {
            'professional' => 'pdf.professional',
            'grid_premium' => 'pdf.professional',
            default => 'pdf.default',
        };

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            'invoice' => $invoice,
            'business' => $invoice->business,
            'party' => $invoice->party,
            'amountInWords' => $amountInWords,
            'taxBreakdown' => $taxBreakdown,
            'brandColor' => $invoice->business->meta['brand_color'] ?? '#1f2937',
        ]);

        // Send Email
        \Illuminate\Support\Facades\Mail::to($invoice->party->email)
            ->send(new \App\Mail\InvoiceEmail($invoice, $pdf->output()));

        return response()->json(['message' => 'Invoice sent successfully.']);
    }

    /**
     * Get invoice number prefix based on document type
     */
    protected function getInvoicePrefix(string $type): string
    {
        return match ($type) {
            'bill_of_supply' => 'BS/',
            'proforma_invoice' => 'PI/',
            'delivery_challan' => 'DC/',
            'credit_note' => 'CN/',
            'debit_note' => 'DN/',
            'tax_invoice' => 'INV/',
            default => 'INV/',
        };
    }

    /**
     * Get next invoice number for a given type
     */
    protected function getNextInvoiceNumber(string $type): string
    {
        $prefix = $this->getInvoicePrefix($type);
        $count = Invoice::where('type', $type)->count() + 1;
        return $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
