<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-body">
    <a class="visually-hidden-focusable skip-link" href="#main-content">Lewati ke konten utama</a>
    <div class="admin-shell d-lg-flex min-vh-100">
        @include('partials.admin.sidebar')
        <div class="admin-content-shell flex-grow-1">
            @include('partials.admin.topbar')
            <main id="main-content" class="admin-main p-3 p-md-4" tabindex="-1">
                @include('partials.flash-messages')
                @yield('content')
            </main>
        </div>
    </div>
    <x-admin.confirmation-modal />
    @stack('scripts')
</body>
</html>
