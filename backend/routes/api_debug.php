<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-sync/{id}', function ($id) {
    try {
        \App\Jobs\ProcessInvoiceScan::dispatchSync($id, null);
        $scan = \App\Models\InvoiceScan::find($id);
        return response()->json(['status' => 'success', 'data' => $scan]);
    } catch (\Exception $e) {
        $scan = \App\Models\InvoiceScan::find($id);
        if ($scan) {
            $scan->status = 'failed';
            $scan->error_message = $e->getMessage();
            $scan->save();
        }
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});
