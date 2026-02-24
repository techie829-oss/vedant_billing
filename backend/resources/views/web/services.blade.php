@extends('layouts.web')

@section('title', 'Features & Services - GST Invoicing & Inventory | VedantBilling')
@section('description',
    'Explore our comprehensive features: GST Invoicing, Inventory Management, Financial Reports, and
    Party Management designed for Indian SMEs.')
@section('keywords', 'gst filing, inventory tracking, invoice generation, business reports')

@section('content')
    <div class="bg-white">
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900 pb-32 overflow-hidden">
            <!-- Decorative background elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-blue-500 opacity-20 blur-[120px] rounded-full">
                </div>
                <div
                    class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-purple-500 opacity-20 blur-[120px] rounded-full">
                </div>
            </div>
            <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
                <h1
                    class="text-4xl font-extrabold tracking-tight text-white md:text-5xl lg:text-6xl text-center drop-shadow-lg">
                    Solutions for Modern Businesses
                </h1>
                <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100 text-center font-medium drop-shadow">
                    From GST compliant invoicing to real-time inventory tracking, VedantBilling gives you the tools to
                    manage your entire business in one place.
                </p>
            </div>
        </div>

        <!-- Feature 1: Invoicing -->
        <div class="relative py-16 sm:py-24 lg:py-32 bg-white overflow-hidden">
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative lg:grid lg:grid-cols-2 lg:gap-8 items-center">
                    <div class="relative">
                        <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">
                            Smart GST Invoicing
                        </h3>
                        <p class="mt-3 text-lg text-gray-500">
                            Create beautiful, compliant invoices in seconds. Our system handles all GST calculations
                            automatically, ensuring you stay compliant with the latest government regulations.
                        </p>

                        <dl class="mt-10 space-y-10">
                            <div class="relative">
                                <dt>
                                    <div
                                        class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                        <!-- Heroicon name: outline/document-text -->
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Customizable Templates</p>
                                </dt>
                                <dd class="mt-2 ml-16 text-base text-gray-500">
                                    Choose from professional templates and add your logo, signature, and brand colors to
                                    look professional.
                                </dd>
                            </div>

                            <div class="relative">
                                <dt>
                                    <div
                                        class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                        <!-- Heroicon name: outline/share -->
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                    </div>
                                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">WhatsApp & Email Sharing
                                    </p>
                                </dt>
                                <dd class="mt-2 ml-16 text-base text-gray-500">
                                    Send invoices directly to your clients via WhatsApp or Email with a single click.
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-10 -mx-4 relative lg:mt-0" aria-hidden="true">
                        <svg class="absolute left-1/2 transform -translate-x-1/2 translate-y-16 lg:hidden" width="784"
                            height="404" fill="none" viewBox="0 0 784 404">
                            <defs>
                                <pattern id="ca9667ae-9f92-4be7-abcb-9e3d727f2941" x="0" y="0" width="20" height="20"
                                    patternUnits="userSpaceOnUse">
                                    <rect x="0" y="0" width="4" height="4" class="text-gray-200"
                                        fill="currentColor" />
                                </pattern>
                            </defs>
                            <rect width="784" height="404" fill="url(#ca9667ae-9f92-4be7-abcb-9e3d727f2941)" />
                        </svg>
                        <div
                            class="relative mx-auto w-full max-w-lg rounded-2xl shadow-2xl lg:max-w-none border border-gray-100 overflow-hidden bg-white p-2 sm:p-4">
                            <!-- Invoice Settings Image passed by User -->
                            <img src="{{ asset('images/invoice-templates.png') }}" alt="Invoice Templates Selection"
                                class="w-full h-auto object-cover rounded-xl hover:scale-105 transition-transform duration-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature 2: Inventory -->
        <div class="relative py-16 sm:py-24 lg:py-32 bg-gray-50 overflow-hidden">
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative lg:grid lg:grid-cols-2 lg:gap-8 items-center">
                    <div class="mt-10 -mx-4 relative lg:mt-0 lg:order-1" aria-hidden="true">
                        <!-- Abstract Inventory UI Representation -->
                        <div
                            class="relative mx-auto w-full max-w-lg rounded-2xl shadow-2xl lg:max-w-none border border-gray-100 overflow-hidden bg-white p-2 sm:p-4">
                            <!-- Add Stock Image passed by User -->
                            <img src="{{ asset('images/add-stock.png') }}" alt="Add Stock Modal"
                                class="w-full h-auto object-cover rounded-xl hover:scale-105 transition-transform duration-500">
                        </div>
                    </div>

                    <div class="relative lg:order-2">
                        <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">
                            Real-time Inventory Control
                        </h3>
                        <p class="mt-3 text-lg text-gray-500">
                            Never run out of stock again. Track every item, manage stock adjustments, and get low-stock
                            alerts instantly.
                        </p>

                        <dl class="mt-10 space-y-10">
                            <div class="relative">
                                <dt>
                                    <div
                                        class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z">
                                            </path>
                                        </svg>
                                    </div>
                                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Stock History</p>
                                </dt>
                                <dd class="mt-2 ml-16 text-base text-gray-500">
                                    View detailed logs of every stock entry and exit. Know exactly where your inventory is
                                    going.
                                </dd>
                            </div>
                            <div class="relative">
                                <dt>
                                    <div
                                        class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </div>
                                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Low Stock Alerts</p>
                                </dt>
                                <dd class="mt-2 ml-16 text-base text-gray-500">
                                    Get automatic notifications when products fall below your set threshold. Reorder in
                                    time.
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature 3: Reports -->
        <div class="relative py-16 sm:py-24 lg:py-32 bg-white overflow-hidden">
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative lg:grid lg:grid-cols-2 lg:gap-8 items-center">
                    <div class="relative">
                        <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">
                            Powerful Business Reports
                        </h3>
                        <p class="mt-3 text-lg text-gray-500">
                            Make data-driven decisions. Our reports give you a 360-degree view of your business performance,
                            tax liabilities, and sales trends.
                        </p>

                        <dl class="mt-10 space-y-10">
                            <div class="relative">
                                <dt>
                                    <div
                                        class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">GSTR Reports</p>
                                </dt>
                                <dd class="mt-2 ml-16 text-base text-gray-500">
                                    Generate GSTR-1, GSTR-2, and GSTR-3B compatible reports in Excel format for easy filing.
                                </dd>
                            </div>
                            <div class="relative">
                                <dt>
                                    <div
                                        class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    </div>
                                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Profit & Loss</p>
                                </dt>
                                <dd class="mt-2 ml-16 text-base text-gray-500">
                                    Understand your net profit after expenses. Track daily, weekly, and monthly growth.
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-10 -mx-4 relative lg:mt-0" aria-hidden="true">
                        <div
                            class="relative mx-auto w-full max-w-lg rounded-2xl shadow-2xl lg:max-w-none border border-gray-100 overflow-hidden bg-white p-2 sm:p-4">
                            <!-- Edit Purchase Invoice Image passed by User -->
                            <img src="{{ asset('images/edit-purchase.png') }}" alt="Edit Purchase Invoice Layout"
                                class="w-full h-auto object-cover rounded-xl hover:scale-105 transition-transform duration-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-blue-600 relative overflow-hidden">
            <div class="absolute inset-0">
                <div
                    class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-70">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-72 h-72 bg-blue-700 rounded-full mix-blend-multiply filter blur-3xl opacity-70">
                </div>
            </div>
            <div
                class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:py-20 lg:px-8 lg:flex lg:items-center lg:justify-between relative z-10">
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    <span class="block">Ready to simplify your billing?</span>
                    <span class="block text-blue-200 mt-2 text-2xl font-medium">Start your free trial today. No credit card
                        required.</span>
                </h2>
                <div class="mt-8 flex gap-4 lg:mt-0 lg:flex-shrink-0">
                    <a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-lg font-medium rounded-xl text-blue-600 bg-white hover:bg-blue-50 hover:shadow-lg transition-all shadow-md">
                        Get started for free
                    </a>
                    <a href="{{ route('pricing') }}"
                        class="inline-flex items-center justify-center px-6 py-3 text-lg font-medium rounded-xl text-white bg-blue-700 hover:bg-blue-800 transition-all border border-blue-500">
                        View Pricing
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
