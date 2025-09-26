<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'QCPL-IMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { font-family: 'Inter', system-ui, sans-serif; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white">
        <div class="min-h-screen flex">
            <!-- Left side - Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-50 to-blue-100 flex-col justify-center items-center p-12">
                <div class="text-center">
                    <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="Quezon City Public Library" class="w-32 h-32 mx-auto mb-8 object-contain">
                    <h1 class="text-3xl font-light text-gray-900 mb-4">QCPL-IMS</h1>
                    <p class="text-lg text-gray-600 mb-2">Quezon City Public Library</p>
                    <p class="text-sm text-gray-500">Inventory Management System</p>
                </div>
            </div>

            <!-- Right side - Form -->
            <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24">
                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <!-- Mobile logo -->
                    <div class="lg:hidden text-center mb-8">
                        <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="Quezon City Public Library" class="w-16 h-16 mx-auto mb-4 object-contain">
                        <h1 class="text-2xl font-semibold text-gray-900">QCPL-IMS</h1>
                        <p class="text-sm text-gray-500">Inventory Management System</p>
                    </div>

                    {{ $slot }}

                    <!-- Back to home link -->
                    <div class="mt-8 text-center">
                        <a href="/" wire:navigate class="text-sm text-gray-500 hover:text-blue-600 transition-colors">
                            ← Back to homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
