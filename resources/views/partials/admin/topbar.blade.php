<header class="admin-topbar sticky-top">
    <div class="d-flex align-items-center gap-3 px-3 px-md-4 py-3">
        <button class="admin-icon-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Buka menu admin"><x-admin.icon name="menu" /></button>
        <button class="admin-icon-button d-none d-lg-inline-flex" type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="true" aria-label="Ciutkan menu admin"><x-admin.icon name="panel" /></button>
        <div class="me-auto">
            <p class="admin-topbar-eyebrow mb-0">Panel administrasi</p>
            <p class="admin-topbar-context mb-0">@yield('page-context', 'Panel Admin')</p>
        </div>
        <div class="admin-user d-none d-sm-flex align-items-center gap-2">
            <span class="admin-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="text-start">
            <p class="mb-0 small fw-semibold">{{ auth()->user()->name }}</p>
            <p class="mb-0 admin-metadata">Administrator</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}" data-logout-form>
            @csrf
            <button class="btn btn-outline-secondary btn-sm admin-logout-button" type="submit"><x-admin.icon name="logout" /> <span class="d-none d-md-inline">Keluar</span></button>
        </form>
    </div>
</header>
