<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Website informasi desa.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <a class="visually-hidden-focusable p-3 bg-white" href="#main-content">Lewati ke konten utama</a>
    @include('partials.public.navbar')
    <main id="main-content" class="flex-grow-1" tabindex="-1">
        @yield('content')
    </main>
    @include('partials.public.footer')
    @stack('scripts')
</body>
</html>
