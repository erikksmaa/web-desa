<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="admin-sidebar" aria-labelledby="admin-sidebar-label">
    <div class="offcanvas-header border-bottom">
        <div>
            <p class="admin-brand mb-0">{{ config('app.name') }}</p>
            <p class="admin-metadata mb-0">Pengelolaan konten</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Tutup menu"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <nav class="admin-navigation" aria-label="Navigasi admin">
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Utama</p>
                <x-admin.sidebar-link label="Dashboard" route="admin.dashboard" :patterns="['admin.dashboard']" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Konten</p>
                <x-admin.sidebar-link label="Berita" />
                <x-admin.sidebar-link label="Pengumuman" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Informasi</p>
                <x-admin.sidebar-link label="Agenda" />
                <x-admin.sidebar-link label="Berkas" />
                <x-admin.sidebar-link label="Galeri" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Profil Desa</p>
                <x-admin.sidebar-link label="Informasi Desa" />
                <x-admin.sidebar-link label="Perangkat Desa" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Tampilan</p>
                <x-admin.sidebar-link label="Banner" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Sistem</p>
                <x-admin.sidebar-link label="Pengguna" />
            </div>
        </nav>
        <a class="admin-site-link mt-auto" href="{{ route('home') }}">Lihat website</a>
    </div>
</aside>
