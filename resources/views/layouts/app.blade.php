<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-background text-foreground overflow-x-hidden" style="font-family: 'Inter', system-ui, sans-serif;">
        <div class="grid min-h-screen w-full md:grid-cols:[220px_1fr] lg:grid-cols:[280px_1fr] md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]">
            {{-- Sidebar --}}
            <div class="hidden border-r bg-muted/40 md:block sticky top-0 h-screen overflow-hidden">
                @includeWhen(View::exists('livewire.admin._sidebar'), 'livewire.admin._sidebar')
            </div>
            
            {{-- Main content area --}}
            <div class="flex flex-col h-screen overflow-hidden">
                <livewire:layout.navigation />
                
                {{-- Breadcrumbs --}}
                <div class="border-b bg-muted/40">
                    <livewire:layout.breadcrumbs />
                </div>
                
                {{-- Page content --}}
                <main class="flex flex-1 flex-col gap-4 p-4 lg:gap-6 lg:p-6 container overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
