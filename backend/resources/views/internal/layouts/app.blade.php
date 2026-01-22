<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Billing SaaS') }} - Internal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="font-sans text-text antialiased bg-background min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-surface border-r border-gray-200 min-h-screen flex-shrink-0 flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-xl font-bold text-primary">Billing SaaS</h1>
            <p class="text-xs text-text-muted uppercase tracking-wider mt-1">Platform Admin</p>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-1">
            <a href="{{ route('internal.dashboard') }}"
                class="flex items-center px-4 py-2 text-sm font-medium rounded-md group {{ request()->routeIs('internal.dashboard') ? 'bg-blue-50 text-primary' : 'text-text-muted hover:bg-gray-50 hover:text-text' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('internal.dashboard') ? 'text-primary' : 'text-gray-400 group-hover:text-gray-500' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('internal.tenants.index') }}"
                class="flex items-center px-4 py-2 text-sm font-medium rounded-md group {{ request()->routeIs('internal.tenants.*') ? 'bg-blue-50 text-primary' : 'text-text-muted hover:bg-gray-50 hover:text-text' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('internal.tenants.*') ? 'text-primary' : 'text-gray-400 group-hover:text-gray-500' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Tenants
            </a>

            <a href="{{ route('internal.subscriptions.index') }}"
                class="flex items-center px-4 py-2 text-sm font-medium rounded-md group {{ request()->routeIs('internal.subscriptions.*') ? 'bg-blue-50 text-primary' : 'text-text-muted hover:bg-gray-50 hover:text-text' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('internal.subscriptions.*') ? 'text-primary' : 'text-gray-400 group-hover:text-gray-500' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Subscriptions
            </a>

            <a href="{{ route('internal.plans.index') }}"
                class="flex items-center px-4 py-2 text-sm font-medium rounded-md group {{ request()->routeIs('internal.plans.*') ? 'bg-blue-50 text-primary' : 'text-text-muted hover:bg-gray-50 hover:text-text' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('internal.plans.*') ? 'text-primary' : 'text-gray-400 group-hover:text-gray-500' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Plans
            </a>

            <a href="{{ route('internal.features.index') }}"
                class="flex items-center px-4 py-2 text-sm font-medium rounded-md group {{ request()->routeIs('internal.features.*') ? 'bg-blue-50 text-primary' : 'text-text-muted hover:bg-gray-50 hover:text-text' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('internal.features.*') ? 'text-primary' : 'text-gray-400 group-hover:text-gray-500' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                Features
            </a>

            <a href="{{ route('internal.users.index') }}"
                class="flex items-center px-4 py-2 text-sm font-medium rounded-md group {{ request()->routeIs('internal.users.*') ? 'bg-blue-50 text-primary' : 'text-text-muted hover:bg-gray-50 hover:text-text' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('internal.users.*') ? 'text-primary' : 'text-gray-400 group-hover:text-gray-500' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Admin Users
            </a>

            <a href="{{ route('internal.leads.index') }}"
                class="flex items-center px-4 py-2 text-sm font-medium rounded-md group {{ request()->routeIs('internal.leads.*') ? 'bg-blue-50 text-primary' : 'text-text-muted hover:bg-gray-50 hover:text-text' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('internal.leads.*') ? 'text-primary' : 'text-gray-400 group-hover:text-gray-500' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Leads
            </a>
        </nav>

        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div
                        class="h-8 w-8 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold">
                        AD
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-text">Admin User</p>
                    <form method="POST" action="{{ route('internal.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-text-muted hover:text-danger flex items-center mt-1">
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-text">@yield('header')</h2>
            <div>
                <!-- Header Actions -->
                @yield('actions')
            </div>
        </header>

        @yield('content')
    </main>

</body>

</html>
