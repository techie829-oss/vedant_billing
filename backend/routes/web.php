<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Web\LandingController::class, 'index'])->name('home');



// Legal Pages
Route::get('privacy-policy', [\App\Http\Controllers\Web\LandingController::class, 'privacy'])->name('privacy');
Route::get('terms-and-conditions', [\App\Http\Controllers\Web\LandingController::class, 'terms'])->name('terms');
Route::get('sitemap.xml', [\App\Http\Controllers\Web\LandingController::class, 'sitemap'])->name('sitemap');

// Public Invoice View
Route::get('p/invoices/{id}', [\App\Http\Controllers\Api\PublicInvoiceController::class, 'show'])->name('public.invoice.show');



Route::prefix('internal')->name('internal.')->group(function () {
    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [\App\Http\Controllers\Internal\AuthController::class, 'create'])->name('login');
        Route::post('login', [\App\Http\Controllers\Internal\AuthController::class, 'store']);
    });

    // Authenticated Routes
    Route::middleware(['auth', 'internal.only'])->group(function () {
        Route::post('logout', [\App\Http\Controllers\Internal\AuthController::class, 'destroy'])->name('logout');

        Route::get('/', \App\Http\Controllers\Internal\DashboardController::class)->name('dashboard');

        // Tenant Management
        Route::resource('tenants', \App\Http\Controllers\Internal\TenantController::class);
        Route::patch('tenants/{id}/status', [\App\Http\Controllers\Internal\TenantController::class, 'updateStatus'])->name('tenants.update-status');
        Route::get('tenants/{business}/users/{user}/password', [\App\Http\Controllers\Internal\TenantController::class, 'editUserPassword'])->name('tenants.users.password.edit');
        Route::put('tenants/{business}/users/{user}/password', [\App\Http\Controllers\Internal\TenantController::class, 'updateUserPassword'])->name('tenants.users.password.update');

        // Tenant Feature Overrides
        Route::post('tenants/{business}/features', [\App\Http\Controllers\Internal\BusinessFeatureController::class, 'store'])->name('tenants.features.store');
        Route::delete('tenants/{business}/features/{feature}', [\App\Http\Controllers\Internal\BusinessFeatureController::class, 'destroy'])->name('tenants.features.destroy');

        // Internal User Management
        Route::resource('users', \App\Http\Controllers\Internal\UserController::class)->only(['index', 'create', 'store']);
        Route::get('users/{user}/password', [\App\Http\Controllers\Internal\UserController::class, 'editPassword'])->name('users.password.edit');
        Route::put('users/{user}/password', [\App\Http\Controllers\Internal\UserController::class, 'updatePassword'])->name('users.password.update');

        // Plan Management
        Route::resource('plans', \App\Http\Controllers\Internal\PlanController::class);

        // Feature Management
        Route::resource('features', \App\Http\Controllers\Internal\FeatureController::class);

        // Subscription Management
        Route::resource('subscriptions', \App\Http\Controllers\Internal\SubscriptionController::class)->only(['index', 'create', 'store']);
        Route::post('subscriptions/{id}/cancel', [\App\Http\Controllers\Internal\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    });
});
