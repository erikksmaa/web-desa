<header class="admin-topbar bg-white border-bottom sticky-top">
    <div class="d-flex align-items-center gap-3 px-3 px-md-4 py-3">
        <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Buka menu admin">Menu</button>
        <p class="admin-topbar-context mb-0 me-auto">@yield('page-context', 'Panel Admin')</p>
        <div class="d-none d-sm-block text-end">
            <p class="mb-0 small fw-semibold">{{ auth()->user()->name }}</p>
            <p class="mb-0 admin-metadata">Administrator</p>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn btn-outline-secondary btn-sm" type="submit">Keluar</button>
        </form>
    </div>
</header>
