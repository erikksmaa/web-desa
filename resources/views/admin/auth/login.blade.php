<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Admin — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-body admin-login-body d-flex align-items-center justify-content-center min-vh-100 p-3">
    <main class="login-panel admin-card bg-white p-4 p-sm-5">
        <div class="admin-login-brand mb-4">
            <span class="admin-brand-mark" aria-hidden="true"><x-admin.icon name="village" /></span>
            <div><p class="fw-semibold mb-0">{{ config('app.name') }}</p><p class="admin-metadata mb-0">Panel administrasi</p></div>
        </div>
        <p class="admin-topbar-eyebrow mb-1">Selamat datang kembali</p>
        <h1 class="h2 mb-2">Masuk Admin</h1>
        <p class="text-muted mb-4">Gunakan akun administrator untuk mengelola informasi desa.</p>
        <x-admin.validation-summary />
        <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
            @csrf
            <x-admin.form.input name="email" label="Email" type="email" :value="old('email')" maxlength="255" autocomplete="username" required autofocus />
            <x-admin.form.input name="password" label="Kata sandi" type="password" maxlength="1024" autocomplete="current-password" required />
            <button class="btn btn-primary w-100" type="submit">Masuk</button>
        </form>
        <a class="d-inline-block mt-4 small" href="{{ route('home') }}">Kembali ke beranda</a>
    </main>
    @include('partials.admin.feedback')
</body>
</html>
