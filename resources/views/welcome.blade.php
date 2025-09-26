<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>QCPL-IMS - Quezon City Public Library Inventory Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { font-family: 'Inter', system-ui, sans-serif; }
        </style>
    </head>
    <body class="antialiased font-sans bg-white">
        <div class="min-h-screen">
            <!-- Minimalist Header -->
            <header class="bg-white border-b border-gray-100">
                <div class="max-w-6xl mx-auto px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- QC Public Library Logo -->
                            <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="QC Public Library" class="w-12 h-12 object-contain">
                            <div>
                                <h1 class="text-xl font-semibold text-gray-900">QCPL-IMS</h1>
                                <p class="text-sm text-gray-500">Inventory Management</p>
                            </div>
                        </div>
                        @if (Route::has('login'))
                            <div class="flex items-center space-x-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Sign in</a>
                                    <a href="#request-access" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Request Access</a>
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Hero Section -->
            <main class="max-w-6xl mx-auto px-6 py-20">
                <div class="text-center mb-20">
                    <div class="mb-8">
                        <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="Quezon City Public Library" class="w-24 h-24 mx-auto mb-6 object-contain">
                    </div>
                    <h1 class="text-6xl font-light text-gray-900 mb-6 tracking-tight">
                        Inventory <span class="font-medium text-blue-600">Management</span>
                    </h1>
                    <p class="text-xl text-gray-500 mb-10 max-w-2xl mx-auto leading-relaxed">
                        Streamline your library operations with modern inventory management for the digital age.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105">
                                Go to Dashboard
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105">
                                Sign in
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                            <div class="inline-flex items-center px-8 py-4 bg-gray-100 text-gray-600 font-medium rounded-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Admin-only access
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid gap-12 md:grid-cols-3 mb-20">
                    <!-- Asset Management -->
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:bg-blue-100 transition-colors duration-300">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Asset Tracking</h3>
                        <p class="text-gray-500 leading-relaxed">
                            Track and manage library assets with comprehensive audit trails.
                        </p>
                    </div>

                    <!-- Supply Management -->
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:bg-green-100 transition-colors duration-300">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 1v6m6-6v6" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Supply Management</h3>
                        <p class="text-gray-500 leading-relaxed">
                            Monitor inventory levels and manage distribution across branches.
                        </p>
                    </div>

                    <!-- Analytics -->
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:bg-purple-100 transition-colors duration-300">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Analytics</h3>
                        <p class="text-gray-500 leading-relaxed">
                            Generate reports and insights for better decision-making.
                        </p>
                    </div>
                </div>
            </main>

            @guest
            <!-- Request Access Section -->
            <section id="request-access" class="bg-gray-50 py-20">
                <div class="max-w-2xl mx-auto px-6">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Request System Access</h2>
                        <p class="text-lg text-gray-600">Submit a request to join the QCPL Inventory Management System. Only government employees with official email addresses are eligible.</p>
                    </div>
                    
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <livewire:request-access-form />
                    </div>
                </div>
            </section>
            @endguest

            <!-- Minimalist Footer -->
            <footer class="border-t border-gray-100 py-12">
                <div class="max-w-6xl mx-auto px-6 text-center">
                    <div class="flex items-center justify-center mb-4">
                        <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="QC Public Library" class="w-8 h-8 object-contain mr-3">
                        <span class="text-sm font-medium text-gray-900">Quezon City Public Library</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-2">Modern inventory management for public libraries</p>
                    <p class="text-xs text-gray-400">© {{ date('Y') }} Quezon City Government. All rights reserved.</p>
                </div>
            </footer>
                </div>
            </div>
        </div>
    </body>
</html>
