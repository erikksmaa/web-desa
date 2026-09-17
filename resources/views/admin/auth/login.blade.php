<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Admin — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-body d-flex align-items-center justify-content-center min-vh-100 p-3">
    <main class="login-panel admin-card bg-white border rounded-3 p-4 p-sm-5">
        <p class="small text-muted mb-2">{{ config('app.name') }}</p>
        <h1 class="h3 mb-4">Masuk Admin</h1>
        @include('partials.flash-messages')
        <x-admin.validation-summary />
        <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
            @csrf
            <x-admin.form.input name="email" label="Email" type="email" :value="old('email')" maxlength="255" autocomplete="username" required autofocus />
            <x-admin.form.input name="password" label="Kata sandi" type="password" maxlength="1024" autocomplete="current-password" required />
            <button class="btn btn-primary w-100" type="submit">Masuk</button>
        </form>
        <a class="d-inline-block mt-4 small" href="{{ route('home') }}">Kembali ke beranda</a>
    </main>
</body>
</html>
