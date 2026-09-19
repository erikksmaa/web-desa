<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <script>
        try {
            if (window.matchMedia('(min-width: 992px)').matches && localStorage.getItem('adminSidebarCollapsed') === 'true') {
                document.documentElement.classList.add('admin-sidebar-collapsed');
            }
        } catch (error) {}
    </script>
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
                @yield('content')
            </main>
        </div>
    </div>
    <form method="POST" action="" class="d-none" data-admin-delete-form>
        @csrf
        @method('DELETE')
    </form>
    @include('partials.admin.feedback')
    @stack('scripts')
</body>
</html>
