<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5XP9RVBQ91"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-5XP9RVBQ91');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'VedantBilling - GST Billing & Invoicing Software')</title>
    <meta name="description" content="@yield('description', 'Free GST billing software for small businesses in India. manage inventory, create invoices, and handle accounting with ease.')">
    <meta name="keywords" content="@yield('keywords', 'gst billing software, free invoicing app, inventory management, accounting software india')">

    <link rel="canonical" href="{{ url()->current() }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('manifest.json') }}" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="@yield('title', 'VedantBilling - GST Billing & Invoicing Software')" />
    <meta property="og:description" content="@yield('description', 'Free GST billing software for small businesses in India. manage inventory, create invoices, and handle accounting with ease.')" />
    <meta property="og:image" content="{{ asset('images/VedantBilling.png') }}" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="{{ url()->current() }}" />
    <meta property="twitter:title" content="@yield('title', 'VedantBilling - GST Billing & Invoicing Software')" />
    <meta property="twitter:description" content="@yield('description', 'Free GST billing software for small businesses in India. manage inventory, create invoices, and handle accounting with ease.')" />
    <meta property="twitter:image" content="{{ asset('images/VedantBilling.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-100 fixed w-full z-50 top-0 left-0" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="bg-blue-600 p-2 rounded-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <!-- Solid text color to avoid blur issues -->
                        <span class="text-2xl font-bold text-gray-900">
                            VedantBilling
                        </span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex items-center">
                    <a href="{{ route('home') }}"
                        class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Home</a>
                    <a href="{{ route('services') }}"
                        class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Features</a>
                    <a href="{{ route('pricing') }}"
                        class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Pricing</a>
                    <a href="{{ route('home') }}#faq"
                        class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">FAQ</a>
                </div>

                <!-- Auth Buttons -->
                <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                    <a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/login' }}"
                        class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Log
                        in</a>
                    <a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                        class="px-5 py-2.5 rounded-full bg-blue-600 text-white text-sm font-medium shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all transform hover:-translate-y-0.5">Get
                        Started</a>
                </div>

                <!-- Hamburger -->
                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div :class="{ 'block': open, 'hidden': !open }"
            class="hidden sm:hidden bg-white border-t border-gray-100 shadow-lg">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}"
                    class="block pl-3 pr-4 py-3 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-blue-800 hover:bg-blue-50 hover:border-blue-300 transition duration-150 ease-in-out">Home</a>
                <a href="{{ route('services') }}"
                    class="block pl-3 pr-4 py-3 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-blue-800 hover:bg-blue-50 hover:border-blue-300 transition duration-150 ease-in-out">Features</a>
                <a href="{{ route('pricing') }}"
                    class="block pl-3 pr-4 py-3 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-blue-800 hover:bg-blue-50 hover:border-blue-300 transition duration-150 ease-in-out">Pricing</a>
                <a href="{{ route('home') }}#faq"
                    class="block pl-3 pr-4 py-3 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-blue-800 hover:bg-blue-50 hover:border-blue-300 transition duration-150 ease-in-out">FAQ</a>
            </div>
            <div class="pt-4 pb-4 border-t border-gray-200">
                <div class="mt-3 space-y-3 px-4">
                    <a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/login' }}"
                        class="block w-full text-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Log
                        in</a>
                    <a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                        class="block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg">Get
                        Started</a>
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
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-1">
                    <span class="text-2xl font-bold text-blue-600">VedantBilling</span>
                    <p class="mt-4 text-gray-500 text-sm">
                        Simplifying GST billing and inventory for Indian businesses. Secure, reliable, and easy to use.
                    </p>
                    <div class="flex space-x-6 mt-6">
                        <a href="https://facebook.com" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Facebook</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="https://twitter.com" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Twitter</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Product -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 tracking-wider uppercase">Product</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="{{ route('services') }}"
                                class="text-base text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="{{ route('pricing') }}"
                                class="text-base text-gray-400 hover:text-white transition">Pricing</a></li>
                        <li><a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/login' }}"
                                class="text-base text-gray-400 hover:text-white transition">Login</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 tracking-wider uppercase">Support</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="{{ rtrim(config('app.frontend_url', 'https://app.vedantbilling.com'), '/') . '/register' }}"
                                class="text-base text-gray-400 hover:text-white transition">Get Started</a></li>
                        <li><a href="{{ route('home') }}#faq"
                                class="text-base text-gray-400 hover:text-white transition">FAQ</a></li>
                        <li><a href="{{ route('contact') }}"
                                class="text-base text-gray-400 hover:text-white transition">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 tracking-wider uppercase">Legal</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="{{ route('privacy') }}"
                                class="text-base text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}"
                                class="text-base text-gray-400 hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-gray-800 pt-8">
                <p class="text-base text-gray-400 xl:text-center">&copy; {{ date('Y') }} VedantBilling Inc. All
                    rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
