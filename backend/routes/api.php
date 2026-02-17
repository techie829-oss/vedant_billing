<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register'])->middleware('throttle:6,1');

// Social Login
Route::get('auth/google/redirect', [\App\Http\Controllers\Api\AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [\App\Http\Controllers\Api\AuthController::class, 'handleGoogleCallback']);

// Email Verification
Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Api\AuthController::class, 'verifyEmail'])->middleware(['assigned_signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/resend', [\App\Http\Controllers\Api\AuthController::class, 'resendVerificationEmail'])->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('businesses', \App\Http\Controllers\Api\BusinessController::class);
    Route::apiResource('businesses.members', \App\Http\Controllers\Api\BusinessMemberController::class)->parameters(['members' => 'user']);
    Route::post('/businesses/{business}/members/{user}/reset-password', [\App\Http\Controllers\Api\BusinessMemberController::class, 'resetPassword']);
    Route::post('/businesses/{business}/invoice-preferences', [\App\Http\Controllers\Api\BusinessController::class, 'updateInvoicePreferences']);


    // Core Business Modules
    Route::apiResource('parties', \App\Http\Controllers\Api\PartyController::class);
    Route::apiResource('products', \App\Http\Controllers\Api\ProductController::class);
    Route::post('products/{product}/adjust-stock', [\App\Http\Controllers\Api\ProductController::class, 'adjustStock']);
    Route::post('/products/scan-invoice', [\App\Http\Controllers\Api\ProductController::class, 'scanInvoice']);

    // Invoice Scan Management
    Route::get('/invoice-scans', [\App\Http\Controllers\Api\ProductController::class, 'getAllScans']);
    Route::get('/invoice-scans/{scanId}', [\App\Http\Controllers\Api\ProductController::class, 'getScanStatus']);
    Route::post('/invoice-scans/{scanId}/retry', [\App\Http\Controllers\Api\ProductController::class, 'retryScan']);
    Route::delete('/invoice-scans/{scanId}', [\App\Http\Controllers\Api\ProductController::class, 'deleteScan']);

    // Temp Products (Invoice OCR Review)
    Route::get('/temp-products', [\App\Http\Controllers\Api\TempProductController::class, 'index']);
    Route::post('/temp-products/{tempProduct}/match', [\App\Http\Controllers\Api\TempProductController::class, 'match']);
    Route::post('/temp-products/{tempProduct}/add-new', [\App\Http\Controllers\Api\TempProductController::class, 'addNew']);
    Route::delete('/temp-products/{tempProduct}', [\App\Http\Controllers\Api\TempProductController::class, 'destroy']);

    Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class);
    Route::post('/invoices/{invoice}/finalize', [\App\Http\Controllers\Api\InvoiceController::class, 'finalize']);
    Route::post('/invoices/{invoice}/duplicate', [\App\Http\Controllers\Api\InvoiceController::class, 'duplicate']);
    Route::post('/invoices/{invoice}/convert', [\App\Http\Controllers\Api\InvoiceController::class, 'convert']);
    Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Api\InvoiceController::class, 'download']);
    Route::post('/invoices/{invoice}/email', [\App\Http\Controllers\Api\InvoiceController::class, 'email']);

    Route::apiResource('tax-rates', \App\Http\Controllers\Api\TaxRateController::class);
    Route::get('/gst-states', [\App\Http\Controllers\Api\GstController::class, 'index']);
    Route::get('/gst-lookup/{gstin}', [\App\Http\Controllers\Api\GstController::class, 'lookup']);

    // Payments
    Route::post('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'store']);
    Route::post('/expenses/scan', [\App\Http\Controllers\Api\ExpenseController::class, 'scan']);
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class);
    Route::get('/export', [\App\Http\Controllers\Api\ExportController::class, 'download']);
    Route::get('/export/{type}', [\App\Http\Controllers\Api\ExportController::class, 'export']);
    Route::get('/cashbook', [\App\Http\Controllers\Api\CashbookController::class, 'index']);

    // Inventory
    Route::get('inventory', [\App\Http\Controllers\Api\InventoryController::class, 'index']);
    Route::post('inventory', [\App\Http\Controllers\Api\InventoryController::class, 'store']); // Use this instead of adjust-stock for full features

    // Plans
    Route::get('/plans', [\App\Http\Controllers\Api\PlanController::class, 'index']);

    // Subscriptions
    Route::get('/subscriptions', [\App\Http\Controllers\Api\SubscriptionController::class, 'index']);
    Route::get('/subscriptions/history', [\App\Http\Controllers\Api\SubscriptionController::class, 'history']);
    Route::post('/subscriptions', [\App\Http\Controllers\Api\SubscriptionController::class, 'store']);
    // Payment Verification Step (We need to add this to the controller)
    Route::post('/subscriptions/verify-payment', [\App\Http\Controllers\Api\SubscriptionController::class, 'verifyPayment']);
    Route::post('/subscriptions/initiate-payment', [\App\Http\Controllers\Api\SubscriptionController::class, 'initiatePayment']);
    Route::post('/subscriptions/activate-free', [\App\Http\Controllers\Api\SubscriptionController::class, 'activateFree']);

    // Webhook
    Route::post('/webhooks/razorpay', [\App\Http\Controllers\Api\WebhookController::class, 'handle']);

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [\App\Http\Controllers\Api\ReportController::class, 'sales']);
        Route::get('/outstanding', [\App\Http\Controllers\Api\ReportController::class, 'outstanding']);
        Route::get('/stock', [\App\Http\Controllers\Api\ReportController::class, 'stock']);
        Route::get('/profit-loss', [\App\Http\Controllers\Api\ReportController::class, 'profitLoss']);
        Route::get('/tax-summary', [\App\Http\Controllers\Api\ReportController::class, 'taxSummary']);
    });

    // Import
    Route::post('/import/tally', [\App\Http\Controllers\Api\ImportController::class, 'importTallyMasters']);


    // Uploads
    Route::post('/upload', [\App\Http\Controllers\Api\UploadController::class, 'store']);

    // Quick Notes
    Route::apiResource('quick-notes', \App\Http\Controllers\Api\QuickNoteController::class);

    // SaaS Subscriptions
    Route::get('/subscriptions', [\App\Http\Controllers\Api\SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [\App\Http\Controllers\Api\SubscriptionController::class, 'store']);
    // Payment Verification Step (We need to add this to the controller)
    Route::post('/subscriptions/verify-payment', [\App\Http\Controllers\Api\SubscriptionController::class, 'verifyPayment']);
    Route::post('/subscriptions/initiate-payment', [\App\Http\Controllers\Api\SubscriptionController::class, 'initiatePayment']);
});
