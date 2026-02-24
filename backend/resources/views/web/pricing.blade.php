@extends('layouts.web')

@section('title', 'Pricing Plans - Affordable GST Billing Software | VedantBilling')
@section('description',
    'Simple, transparent pricing for Indian businesses. Start with our Free Plan or upgrade for
    advanced GST features. No hidden fees.')
@section('keywords', 'billing software pricing, free gst software, invoicing plans, small business software cost')

@section('content')
    <div class="bg-gray-50">
        <!-- Header from Home Page -->
        <div class="py-16 bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Pricing</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Simple & Affordable Pricing
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Choose a plan that fits your business size. No hidden charges.
                </p>
            </div>
        </div>

        <!-- Pricing Section -->
        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                    @foreach ($plans as $plan)
                        <div
                            class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 border flex flex-col {{ $plan->slug === 'pro' ? 'border-2 border-blue-600 relative overflow-hidden' : 'border-gray-100' }}">
                            @if ($plan->slug === 'pro')
                                <div
                                    class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                                    POPULAR</div>
                            @endif

                            <h3 class="text-lg font-medium text-gray-900">{{ $plan->name }}</h3>
                            <p class="mt-4 text-sm text-gray-500">{{ $plan->description }}</p>
                            <p class="mt-8">
                                <span class="text-4xl font-extrabold text-gray-900">
                                    {{ $plan->price > 0 ? '₹' . number_format($plan->price) : 'Free' }}
                                </span>
                                @if ($plan->price > 0)
                                    <span
                                        class="text-base font-medium text-gray-500">/{{ $plan->interval === 'yearly' ? 'yr' : 'mo' }}</span>
                                @endif
                            </p>

                            <ul class="mt-8 space-y-4 flex-1">
                                @foreach ($plan->features as $feature)
                                    <li class="flex items-start text-sm text-gray-600">
                                        @if ($feature->type === 'boolean' && $feature->pivot->limit === 0)
                                            <svg class="w-5 h-5 text-gray-400 mr-2 shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif

                                        <span>
                                            {{ $feature->name }}:
                                            <strong class="text-gray-900">
                                                @if ($feature->type === 'boolean')
                                                    {{ $feature->pivot->limit === 1 ? 'Yes' : 'No' }}
                                                @else
                                                    @if ($feature->pivot->limit < 0)
                                                        Unlimited
                                                    @else
                                                        {{ $feature->pivot->limit }}
                                                    @endif
                                                @endif
                                            </strong>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/register?plan=' . $plan->slug }}"
                                class="mt-8 block w-full py-3 px-6 rounded-xl text-center font-medium transition {{ $plan->slug === 'pro' ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-600/30' : 'border border-blue-600 text-blue-600 hover:bg-blue-50' }}">
                                {{ $plan->price === 0 ? 'Get Started' : 'Start Free Trial' }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section Duplicate -->
    <div class="py-24 bg-white border-t border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Frequently Asked Questions</h2>
            </div>
            <div class="space-y-4">
                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">Is VedantBilling really free?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        Yes! Our Starter plan is completely free forever for small businesses.
                    </p>
                </details>

                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">Can I upgrade later?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        Absolutely. You can upgrade or downgrade your plan at any time from your dashboard.
                    </p>
                </details>

                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">What payment methods do you accept?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        We accept all major credit cards, debit cards, and UPI payments.
                    </p>
                </details>
            </div>
        </div>
    </div>
@endsection
