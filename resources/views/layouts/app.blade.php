<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('dark_mode') ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Clusterfiy') }} - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Ensure DaisyUI works with dark mode */
        .dark .bg-base-100 {
            background-color: #1e293b;
        }

        .dark .bg-base-200 {
            background-color: #0f172a;
        }

        .dark .text-base-content {
            color: #e2e8f0;
        }
    </style>
</head>

<body class="font-sans antialiased bg-base-200 text-base-content">
    <div class="flex min-h-screen">
        @include('partials.sidebar')
        <div class="flex-1 flex flex-col">
            @include('partials.navbar') <!-- keep simple top bar with dark toggle -->
            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error mb-4">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
