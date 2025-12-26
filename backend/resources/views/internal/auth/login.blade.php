<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - Internal Admin | Billing SaaS</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-gray-900">
    <div class="flex min-h-full">
        <!-- Left Side: Login Form -->
        <div
            class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white z-10">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div>
                    {{-- <img class="h-10 w-auto" src="/logo.svg" alt="Company Schema"> --}}
                    <div
                        class="h-10 w-10 bg-primary rounded-lg flex items-center justify-center text-white font-bold text-xl">
                        B</div>
                    <h2 class="mt-8 text-2xl font-bold leading-9 tracking-tight text-gray-900">Sign in to your account
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Internal Platform Administration
                    </p>
                </div>

                <div class="mt-10">
                    <form action="{{ route('internal.login') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email
                                address</label>
                            <div class="mt-2">
                                <input id="email" name="email" type="email" autocomplete="email" required
                                    class="input-field block sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div>
                            <label for="password"
                                class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                            <div class="mt-2">
                                <input id="password" name="password" type="password" autocomplete="current-password"
                                    required class="input-field block sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember" name="remember" type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="remember" class="ml-3 block text-sm leading-6 text-gray-700">Remember
                                    me</label>
                            </div>

                            <div class="text-sm leading-6">
                                <a href="#" class="font-semibold text-primary hover:text-indigo-500">Forgot
                                    password?</a>
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                class="flex w-full justify-center rounded-md bg-primary px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors">Sign
                                in</button>
                        </div>
                    </form>

                    @if ($errors->any())
                        <div class="mt-6 border-l-4 border-red-400 bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        {{ $errors->first() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side: Decorative Image -->
        <div class="relative hidden w-0 flex-1 lg:block">
            <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-gray-900 to-gray-800">
                <div class="absolute inset-0 flex items-center justify-center opacity-20">
                    <!-- Abstract Pattern -->
                    <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
                    </svg>
                </div>
                <div
                    class="flex flex-col items-center justify-center h-full text-white px-12 text-center z-10 relative">
                    <h1 class="text-4xl font-bold mb-4">Internal Admin Portal</h1>
                    <p class="text-lg text-gray-300 max-w-lg">
                        Manage tenants, subscriptions, and financial data securely.
                        Valid access credentials required.
                    </p>
                </div>
            </div>
            {{-- <img class="absolute inset-0 h-full w-full object-cover"
                src="https://images.unsplash.com/photo-1496917756835-20cb06e75b4e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1908&q=80"
                alt=""> --}}
        </div>
    </div>
</body>

</html>