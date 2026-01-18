<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="VedantBilling is the all-in-one invoicing and inventory management solution for growing businesses. Create professional invoices, track stock, and manage payments effortlessly.">
    <meta name="keywords"
        content="invoicing software, inventory management, billing system, small business accounting, gst billing, vedantbilling">
    <meta name="author" content="VedantBilling">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="VedantBilling - Smart Invoicing & Inventory for Growing Businesses">
    <meta property="og:description"
        content="Manage invoices, track inventory, and handle accounting with a platform designed for modern businesses. Fast, secure, and intuitive.">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="VedantBilling - Smart Invoicing & Inventory for Growing Businesses">
    <meta property="twitter:description"
        content="Manage invoices, track inventory, and handle accounting with a platform designed for modern businesses. Fast, secure, and intuitive.">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <title>VedantBilling - Smart Invoicing & Inventory for Growing Businesses</title>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 text-gray-900">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="#" class="flex items-center gap-2">
                        <!-- Logo Icon -->
                        <div
                            class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                            B</div>
                        <span
                            class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">VedantBilling</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Features</a>
                    <a href="#pricing"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Pricing</a>
                    <a href="#testimonials"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Reviews</a>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- PWA Login Link -->
                    <a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/login' }}"
                        class="text-sm font-medium text-gray-700 hover:text-gray-900">Login</a>

                    <a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                        class="px-5 py-2.5 rounded-full bg-blue-600 text-white text-sm font-medium shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all transform hover:-translate-y-0.5">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-gray-900 mb-6">
                Invoicing made <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">effortless.</span>
            </h1>
            <p class="mt-4 text-xl text-gray-500 max-w-2xl mx-auto mb-10">
                Manage invoices, track inventory, and handle accounting with a platform designed for modern businesses.
                Fast, secure, and intuitive.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                    class="px-8 py-4 rounded-full bg-blue-600 text-white text-lg font-semibold shadow-xl shadow-blue-600/20 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all transform hover:-translate-y-1">
                    Start Your Free Trial
                </a>
                <a href="#demo"
                    class="px-8 py-4 rounded-full bg-white text-gray-700 text-lg font-semibold border border-gray-200 shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all">
                    View Interactive Demo
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

    <!-- Features Section -->
    <div id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Features</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Everything you need to run your business
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    From inventory tracking to tax calculation, we've got you covered.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Feature 1 -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Smart Invoicing</h3>
                    <p class="text-gray-600">Create professional invoices in seconds. Automate recurring billing and
                        payment reminders.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Inventory Management</h3>
                    <p class="text-gray-600">Track stock levels in real-time. Get low stock alerts and manage multiple
                        warehouses.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="p-8 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Financial Reports</h3>
                    <p class="text-gray-600">Gain insights with detailed Profit & Loss, Sales, and Tax reports. Make
                        data-driven decisions.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div id="pricing" class="py-24 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Pricing</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Simple, transparent pricing
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    No hidden fees. Cancel anytime.
                </p>
            </div>

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

    <!-- FAQ Section (Detailed Addition) -->
    <div id="faq" class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900">Frequently Asked Questions</h2>
                <p class="mt-4 text-gray-500">Everything you need to know about VedantBilling.</p>
            </div>

            <div class="space-y-8">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Can I switch plans later?</h3>
                    <p class="mt-2 text-gray-500">Yes, you can upgrade or downgrade your plan at any time from your
                        dashboard. Changes take effect immediately.</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Is my data secure?</h3>
                    <p class="mt-2 text-gray-500">Absolutely. We use industry-standard encryption to protect your data.
                        Your financial information is our top priority.</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Do you offer a free trial?</h3>
                    <p class="mt-2 text-gray-500">Yes! The Starter plan is completely free forever. For paid plans, we
                        offer a 14-day risk-free trial.</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">What payment methods do you accept?</h3>
                    <p class="mt-2 text-gray-500">We accept major credit cards, debit cards, and UPI payments for
                        Indian customers.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-1">
                    <span class="text-2xl font-bold text-white">VedantBilling</span>
                    <p class="mt-4 text-gray-400 text-sm">Empowering growing businesses with smart financial tools.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Product</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ route('home') }}#features" class="hover:text-white">Features</a></li>
                        <li><a href="{{ route('home') }}#pricing" class="hover:text-white">Pricing</a></li>
                        <li><a href="{{ config('app.frontend_url') }}/login" class="hover:text-white">Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ config('app.frontend_url') }}/register" class="hover:text-white">Get
                                Started</a></li>
                        <li><a href="{{ config('app.frontend_url') }}/dashboard"
                                class="hover:text-white">Dashboard</a></li>
                        <li><a href="{{ route('home') }}" class="hover:text-white">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white">Terms</a></li>
                        <li><a href="{{ route('home') }}" class="hover:text-white">Security</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-gray-800 pt-8 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} VedantBilling Inc. All rights reserved.
            </div>
        </div>
    </footer>

</body>

</html>
