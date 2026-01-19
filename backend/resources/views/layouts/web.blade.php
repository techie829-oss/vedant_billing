<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', 'VedantBilling is the all-in-one invoicing and inventory management solution for growing businesses. Create professional invoices, track stock, and manage payments effortlessly.')">
    <meta name="keywords" content="@yield('keywords', 'invoicing software, inventory management, billing system, small business accounting, gst billing, vedantbilling')">
    <meta name="author" content="VedantBilling">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'VedantBilling - Smart Invoicing & Inventory for Growing Businesses')">
    <meta property="og:description" content="@yield('description', 'Manage invoices, track inventory, and handle accounting with a platform designed for modern businesses. Fast, secure, and intuitive.')">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'VedantBilling - Smart Invoicing & Inventory for Growing Businesses')">
    <meta property="twitter:description" content="@yield('description', 'Manage invoices, track inventory, and handle accounting with a platform designed for modern businesses. Fast, secure, and intuitive.')">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <title>@yield('title', 'VedantBilling - Smart Invoicing & Inventory for Growing Businesses')</title>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Google Analytics (Optional) -->
    @if (config('services.google_analytics.id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ config('services.google_analytics.id') }}');
        </script>
    @endif

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>

    @stack('head')
</head>

<body class="antialiased bg-gray-50 text-gray-900">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <!-- Logo Icon -->
                        <div
                            class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                            B
                        </div>
                        <span
                            class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">VedantBilling</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}#features"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Features</a>
                    <a href="{{ route('home') }}#pricing"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Pricing</a>
                    <a href="{{ route('home') }}#testimonials"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Reviews</a>
                    <a href="{{ route('privacy') }}"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Privacy</a>
                    <a href="{{ route('terms') }}"
                        class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Terms</a>
                </div>

                <!-- CTA Buttons -->
                <div class="flex items-center space-x-4">
                    <a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/login' }}"
                        class="text-sm font-medium text-gray-700 hover:text-gray-900">Login</a>

                    <a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                        class="px-5 py-2.5 rounded-full bg-blue-600 text-white text-sm font-medium shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all transform hover:-translate-y-0.5">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-20">
        @yield('content')
    </main>

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
                        <li><a href="{{ route('home') }}#features" class="hover:text-white transition">Features</a>
                        </li>
                        <li><a href="{{ route('home') }}#pricing" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/login' }}"
                                class="hover:text-white transition">Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                                class="hover:text-white transition">Get
                                Started</a></li>
                        <li><a href="{{ rtrim(env('WEB_URL', 'https://app.vedantbilling.com'), '/') . '/dashboard' }}"
                                class="hover:text-white transition">Dashboard</a></li>
                        <li><a href="{{ route('home') }}#faq" class="hover:text-white transition">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition">Terms</a></li>
                        <li><a href="{{ route('home') }}#features" class="hover:text-white transition">Features</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-gray-800 pt-8 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} VedantBilling Inc. All rights reserved.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
