@extends('layouts.web')
@section('title', 'GST Billing Software for Indian Businesses | VedantBilling')
@section('description',
    'Cloud-based GST billing & invoicing software for SMEs — generate GST invoices, track inventory,
    export reports & stay compliant. Try free!')
@section('keywords',
    'GST billing software India, invoicing software India, billing software for small business,
    inventory management software India, invoice verification software')

@section('content')
    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-gray-900 mb-6">
                Simple <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">GST
                    Billing</span> & Invoicing Software
                <br class="hidden md:block" />for Indian Businesses
            </h1>
            <p class="mt-4 text-xl text-gray-500 max-w-3xl mx-auto mb-8">
                Create professional GST invoices, manage inventory, track stock movement, and run your business smoothly
                with VedantBilling – a secure cloud billing solution built for growing Indian businesses.
            </p>

            <!-- Trust Badges -->
            <div class="flex flex-wrap justify-center gap-4 md:gap-8 mb-10 text-sm font-medium text-gray-600">
                <span class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg> GST Ready</span>
                <span class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg> Secure Cloud Platform</span>
                <span class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg> Made for Indian Businesses</span>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                    class="px-8 py-4 rounded-full bg-blue-600 text-white text-lg font-semibold shadow-xl shadow-blue-600/20 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all transform hover:-translate-y-1">
                    Start Free Trial
                </a>
                <a href="#features"
                    class="px-8 py-4 rounded-full bg-white text-gray-700 text-lg font-semibold border border-gray-200 shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all">
                    View Features
                </a>
            </div>
        </div>

        <!-- Background Elements -->
        <div class="absolute top-0 left-1/2 w-full -translate-x-1/2 h-full z-0 pointer-events-none">
            <div
                class="absolute top-20 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob">
            </div>
            <div
                class="absolute top-40 right-10 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000">
            </div>
            <div
                class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000">
            </div>
        </div>
    </div>

    <!-- UI Mockup / Dashboard Preview -->
    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 mb-20">
        <div
            class="bg-gray-900 rounded-2xl shadow-2xl border border-gray-800 p-2 md:p-4 overflow-hidden transform rotate-x-12 perspective-1000">
            <!-- Mockup Header -->
            <div class="flex items-center gap-2 mb-4 px-2">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
            </div>
            <!-- App Screenshot -->
            <div
                class="bg-gray-800 rounded-lg aspect-video w-full overflow-hidden border border-gray-700/50 relative group">
                <img src="{{ asset('images/VedantBilling.png') }}" alt="VedantBilling Dashboard"
                    class="w-full h-full object-cover object-top transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/40 to-transparent pointer-events-none">
                </div>
            </div>
        </div>
    </div>

    <!-- Core Features Section -->
    <div id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Core Features</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    All‑in‑One Billing, Inventory & Business Management
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    From creating tax invoices to tracking inventory, we've got you covered.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Smart GST Invoicing -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Smart GST Invoicing</h3>
                    <p class="text-gray-600">Create GST‑compliant tax invoices in seconds. Auto-calculate taxes, apply
                        discounts, and generate professional PDFs instantly.</p>
                </div>

                <!-- Complete Document Management -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Document Management</h3>
                    <p class="text-gray-600">Manage Tax Invoices, Bill of Supply, Quotations, Credit Notes, and Delivery
                        Challans from a single dashboard.</p>
                </div>

                <!-- Inventory & Stock -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Inventory & Stock</h3>
                    <p class="text-gray-600">Track stock in real-time. View transaction history, get low-stock alerts, and
                        maintain accurate HSN codes.</p>
                </div>

                <!-- Customer Management -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center text-yellow-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Customer Management</h3>
                    <p class="text-gray-600">Securely store customer and supplier details (GSTIN, Address) for quick access
                        while billing.</p>
                </div>

                <!-- Expense Tracking -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-red-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Expense Tracking</h3>
                    <p class="text-gray-600">Record and categorize daily expenses. Monitor spending easy to keep your
                        accounts organized.</p>
                </div>

                <!-- Reports & Dashboard -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Reports & Dashboard</h3>
                    <p class="text-gray-600">Gain insights with Sales, P&L, and GST Tax Summary reports. Export data to
                        Excel/CSV anytime.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Features Section -->
    <div class="py-24 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Advanced Features</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Built for Speed & Trust
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-bold text-gray-900">Invoice Verification System</h4>
                        <p class="mt-2 text-gray-600">Every invoice includes a secure online verification link. Helping
                            your clients verify authenticity instantly.</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-bold text-gray-900">Scan Invoices (OCR)</h4>
                        <p class="mt-2 text-gray-600">Scan physical purchase bills using your camera. Auto-extract details
                            to save time on data entry.</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-bold text-gray-900">Offline Mode (PWA)</h4>
                        <p class="mt-2 text-gray-600">Create invoices even without internet. Data syncs automatically once
                            you are back online.</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-bold text-gray-900">Tally Import</h4>
                        <p class="mt-2 text-gray-600">Import your existing master data directly from Tally XML. Get started
                            in minutes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div id="pricing" class="py-24 bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Pricing</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Simple & Affordable Pricing
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Choose a plan that fits your business size. No hidden charges.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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

    <!-- Trust & Security Section -->
    <div id="why-us" class="py-24 bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Why Choose Us</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Secure, Reliable & Built for Business Growth
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Small and medium businesses across India trust VedantBilling.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2 md:gap-6 text-center">
                <!-- Security -->
                <div
                    class="p-4 md:p-8 bg-gray-50 rounded-2xl border border-gray-100 hover:border-blue-100 hover:bg-blue-50 transition-all duration-300 group">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-white text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 md:mb-6 shadow-sm group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xs md:text-lg font-bold text-gray-900 mb-2 md:mb-3 group-hover:text-blue-700">
                        Enterprise Security</h3>
                    <p class="hidden md:block text-gray-600 text-sm leading-relaxed">Secure authentication and data
                        isolation.</p>
                </div>

                <!-- Support -->
                <div
                    class="p-4 md:p-8 bg-gray-50 rounded-2xl border border-gray-100 hover:border-green-100 hover:bg-green-50 transition-all duration-300 group">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-white text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 md:mb-6 shadow-sm group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs md:text-lg font-bold text-gray-900 mb-2 md:mb-3 group-hover:text-green-700">24/7
                        Reliability</h3>
                    <p class="hidden md:block text-gray-600 text-sm leading-relaxed">Modern cloud infrastructure, always
                        online.</p>
                </div>

                <!-- Compliance -->
                <div
                    class="p-4 md:p-8 bg-gray-50 rounded-2xl border border-gray-100 hover:border-purple-100 hover:bg-purple-50 transition-all duration-300 group">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-white text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 md:mb-6 shadow-sm group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs md:text-lg font-bold text-gray-900 mb-2 md:mb-3 group-hover:text-purple-700">GST
                        Compliant</h3>
                    <p class="hidden md:block text-gray-600 text-sm leading-relaxed">Automatic tax updates and government
                        compliance.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="py-24 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Testimonials</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Trusted by Indian Businesses
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            ★★★★★
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"VedantBilling transformed our invoicing process. What used to take hours
                        now takes minutes. Absolutely love it!"</p>
                    <div class="font-bold text-gray-900">Santosh Kumar</div>
                    <div class="text-sm text-gray-500">Owner, R/S Chitra Enterprises</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            ★★★★★
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"The GST compliance features are a lifesaver. Perfect for Indian small
                        businesses. Highly recommended!"</p>
                    <div class="font-bold text-gray-900">Vinit Kumar</div>
                    <div class="text-sm text-gray-500">Owner, Kripal Overseas</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            ★★★★★
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"Professional invoices in seconds. The inventory tracking is excellent
                        too. Best investment for my business."</p>
                    <div class="font-bold text-gray-900">Vijay Kumar</div>
                    <div class="text-sm text-gray-500">Owner, Shiva Traders</div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div id="faq" class="py-24 bg-white border-t border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">FAQ</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Frequently Asked Questions
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Common questions about VedantBilling.
                </p>
            </div>

            <div class="space-y-4">
                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">Is VedantBilling GST compliant?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        Yes. VedantBilling supports CGST, SGST, IGST, HSN codes, GST‑ready
                        invoice formats and tax summary reports for easy GST filing.
                    </p>
                </details>

                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">Can I use VedantBilling for free?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        Yes. We offer a free plan for small businesses. You can also try paid
                        plans free for 14 days before upgrading.
                    </p>
                </details>

                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">Is my data secure?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        Yes. Your data is protected with secure login, role‑based access and
                        separate data storage for each business.
                    </p>
                </details>

                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">Can I verify invoices online?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        Yes. Every invoice includes a secure verification link that allows
                        customers and auditors to verify invoice authenticity online.
                    </p>
                </details>

                <details
                    class="group border border-gray-200 rounded-xl bg-white [&_summary::-webkit-details-marker]:hidden shadow-sm">
                    <summary
                        class="flex justify-between items-center font-medium cursor-pointer list-none px-6 py-5 text-gray-900 bg-gray-50 hover:bg-gray-100 transition-colors rounded-xl group-open:rounded-b-none group-open:bg-gray-100">
                        <span class="text-lg">How can I pay for my subscription?</span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="h-5 w-5 text-gray-500" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </summary>
                    <p
                        class="px-6 py-5 text-gray-600 bg-white rounded-b-xl border-t border-gray-100 leading-relaxed text-base">
                        You can pay for your VedantBilling subscription using credit cards, debit
                        cards and UPI through secure online payment integration.
                    </p>
                </details>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "VedantBilling",
      "headline": "GST Billing Software for Indian Businesses",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "INR"
      },
      "description": "Cloud-based GST billing & invoicing software for SMEs in India. Generate GST invoices, track inventory, export reports & stay compliant. Try free!",
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "124"
      },
      "featureList": "GST Invoicing, Inventory Tracking, Delivery Challan, Bill of Supply, Financial Reports, Stock History, Low Stock Alerts"
    }
    </script>
@endsection
