<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Invoice Listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\InvoiceFinalized::class,
            \App\Listeners\UpdateStockForInvoice::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\InvoiceFinalized::class,
            \App\Listeners\CreateLedgerEntriesForInvoice::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\InvoiceFinalized::class,
            \App\Listeners\RecalculatePartyBalance::class
        );

        // Payment Listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PaymentReceived::class,
            \App\Listeners\RecordPaymentToLedger::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PaymentReceived::class,
            \App\Listeners\RecalculatePartyBalance::class
        );

        if (config('app.env') === 'production' && env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            if (config('app.url')) {
                \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            }
        }
    }
}
