<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'Clusterfiy' }}</title>
<meta name="description" content="{{ $metaDescription ?? 'Clusterfiy multi-company team management.' }}">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:title" content="{{ $title ?? 'Clusterfiy' }}">
<meta property="og:description" content="{{ $metaDescription ?? 'Clusterfiy multi-company team management.' }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('images/og-default.png') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?? 'Clusterfiy' }}">
<meta name="twitter:description" content="{{ $metaDescription ?? 'Clusterfiy multi-company team management.' }}">
<meta name="twitter:image" content="{{ asset('images/og-default.png') }}">
@stack('structured-data')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body class="bg-white text-neutral-900 antialiased" style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;">
<header class="border-b border-neutral-200 bg-white">
<nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3" aria-label="Main">
<a href="{{ route('home') }}" class="flex items-center gap-2">
<img src="{{ asset('images/logo.png') }}" alt="Clusterfiy logo" width="28" height="28">
<span class="text-lg font-semibold">Clusterfiy</span>
</a>
<div class="flex items-center gap-4">
<a href="{{ route('pricing') }}">Pricing</a>
<a href="{{ route('terms') }}">Terms</a>
<a href="{{ route('privacy') }}">Privacy</a>
@auth
<a href="{{ route('dashboard') }}">Dashboard</a>
<a href="{{ route('notifications.index') }}">Notifications ({{ auth()->user()->unreadNotifications()->count() }})</a>
<form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button type="submit">Log out</button></form>
@else
<a href="{{ route('login') }}">Log in</a>
<a href="{{ route('register') }}">Register</a>
@endauth
</div>
</nav>
</header>
<main class="mx-auto max-w-6xl px-4 py-8">
@if(session('success'))<div role="alert" class="border border-neutral-300 bg-white p-3">{{ session('success') }}</div>@endif
@if(session('error'))<div role="alert" class="border border-neutral-300 bg-white p-3">{{ session('error') }}</div>@endif
@if($errors->any())<div role="alert" class="border border-neutral-300 bg-white p-3"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
{{ $slot ?? '' }}
@yield('content')
</main>
<footer class="border-t border-neutral-200 bg-white">
<div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-6">
<p>Clusterfiy. Multi-company team management.</p>
<div class="flex gap-4">
<a href="{{ route('terms') }}">Terms of Service</a>
<a href="{{ route('privacy') }}">Privacy Policy</a>
<a href="{{ route('sitemap') }}">Sitemap</a>
</div>
</div>
</footer>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide){lucide.createIcons();}});</script>
</body>
</html>
