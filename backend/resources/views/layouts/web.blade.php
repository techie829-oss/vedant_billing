<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Vedant Billing - Invoicing Made Simple')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="text-2xl font-bold">
                            <span class="text-indigo-600">Vedant</span>Billing
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}"
                        class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="{{ route('privacy') }}"
                        class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Privacy</a>
                    <a href="{{ route('terms') }}"
                        class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Terms</a>
                    <a href="{{ config('app.frontend_url') }}/login"
                        class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="{{ config('app.frontend_url') }}/register"
                        class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md text-sm font-medium">Get
                        Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="text-2xl font-bold mb-4">
                        <span class="text-indigo-400">Vedant</span>Billing
                    </div>
                    <p class="text-gray-400 mb-4">
                        Simple and powerful invoicing software for small businesses.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Product</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('home') }}#pricing" class="hover:text-white">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Legal</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Vedant Billing. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
