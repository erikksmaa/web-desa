@php
    $notifications = [];

    if ($errors->any()) {
        $isLogin = request()->routeIs('admin.login');
        $notifications[] = [
            'icon' => $isLogin ? 'error' : 'warning',
            'title' => $isLogin ? 'Login gagal' : 'Periksa kembali data',
            'text' => $isLogin ? $errors->first() : 'Terdapat '.$errors->count().' isian yang perlu diperbaiki.',
        ];
    }

    foreach (['success', 'error', 'warning', 'info'] as $type) {
        if (! session()->has($type)) continue;
        $message = (string) session($type);
        $title = match ($type) {
            'success' => str_contains(mb_strtolower($message), 'dihapus') ? 'Data berhasil dihapus' : (str_contains(mb_strtolower($message), 'masuk') ? 'Login berhasil' : 'Berhasil'),
            'error' => 'Terjadi kesalahan',
            'warning' => 'Perlu diperhatikan',
            default => 'Informasi',
        };
        $notifications[] = ['icon' => $type, 'title' => $title, 'text' => $message];
    }
@endphp

<script>window.adminFeedback = {{ Js::from($notifications) }};</script>
<noscript>@include('partials.flash-messages')</noscript>
