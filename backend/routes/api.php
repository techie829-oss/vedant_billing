<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('businesses', \App\Http\Controllers\Api\BusinessController::class);
    Route::apiResource('businesses.members', \App\Http\Controllers\Api\BusinessMemberController::class)->shallow();

    // Core Business Modules
    Route::apiResource('parties', \App\Http\Controllers\Api\PartyController::class);
    Route::apiResource('products', \App\Http\Controllers\Api\ProductController::class);
    Route::post('products/{product}/adjust-stock', [\App\Http\Controllers\Api\ProductController::class, 'adjustStock']);

    Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class);
    Route::post('/invoices/{invoice}/finalize', [\App\Http\Controllers\Api\InvoiceController::class, 'finalize']);
    Route::post('/invoices/{invoice}/duplicate', [\App\Http\Controllers\Api\InvoiceController::class, 'duplicate']);
    Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Api\InvoiceController::class, 'download']);
    Route::post('/invoices/{invoice}/email', [\App\Http\Controllers\Api\InvoiceController::class, 'email']);

    Route::apiResource('tax-rates', \App\Http\Controllers\Api\TaxRateController::class);
    Route::get('/gst-states', [\App\Http\Controllers\Api\GstController::class, 'index']);

    // Payments
    Route::post('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'store']);
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class);
    Route::get('/export', [\App\Http\Controllers\Api\ExportController::class, 'download']);
    Route::get('/cashbook', [\App\Http\Controllers\Api\CashbookController::class, 'index']);

    // Plans
    Route::get('/plans', [\App\Http\Controllers\Api\PlanController::class, 'index']);

    // Subscriptions
    Route::get('/subscriptions', [\App\Http\Controllers\Api\SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [\App\Http\Controllers\Api\SubscriptionController::class, 'store']);

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [\App\Http\Controllers\Api\ReportController::class, 'sales']);
        Route::get('/outstanding', [\App\Http\Controllers\Api\ReportController::class, 'outstanding']);
        Route::get('/stock', [\App\Http\Controllers\Api\ReportController::class, 'stock']);
        Route::get('/profit-loss', [\App\Http\Controllers\Api\ReportController::class, 'profitLoss']);
    });

    // Uploads
    Route::post('/upload', [\App\Http\Controllers\Api\UploadController::class, 'store']);
});
