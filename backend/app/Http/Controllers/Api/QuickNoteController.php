<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuickNote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuickNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $businessId = $request->header('X-Business-ID');

        $notes = QuickNote::where('business_id', $businessId)
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:order_receipt,hisab',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|array',
            'total_amount' => 'required|numeric',
        ]);

        $businessId = $request->header('X-Business-ID');

        $note = QuickNote::create([
            'id' => Str::uuid(),
            'business_id' => $businessId,
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'title' => $validated['title'] ?? 'Note ' . now()->format('d M H:i'),
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'],
            'total_amount' => $validated['total_amount'],
        ]);

        return response()->json($note, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(QuickNote $quickNote)
    {
        // Simple auth check
        if ($quickNote->user_id !== request()->user()->id && $quickNote->business_id !== request()->header('X-Business-ID')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($quickNote);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuickNote $quickNote)
    {
        // Simple auth check since we don't have Policy generated yet
        if ($quickNote->user_id !== $request->user()->id && $quickNote->business_id !== $request->header('X-Business-ID')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:order_receipt,hisab',
            'title' => 'nullable|string|max:255',
            'content' => 'sometimes|array',
            'total_amount' => 'sometimes|numeric',
        ]);

        $quickNote->update($validated);

        return response()->json($quickNote);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, QuickNote $quickNote)
    {
        // Simple auth check
        if ($quickNote->user_id !== $request->user()->id && $quickNote->business_id !== $request->header('X-Business-ID')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quickNote->delete();

        return response()->noContent();
    }
}
