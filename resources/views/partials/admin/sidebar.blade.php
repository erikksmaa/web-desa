<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="admin-sidebar" aria-labelledby="admin-sidebar-label">
    <div class="offcanvas-header admin-sidebar-header">
        <div class="admin-brand-wrap">
            <span class="admin-brand-mark" aria-hidden="true"><x-admin.icon name="village" /></span>
            <div class="admin-brand-copy">
                <p class="admin-brand mb-0" id="admin-sidebar-label">{{ config('app.name') }}</p>
                <p class="admin-metadata mb-0">Pengelolaan konten</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Tutup menu"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <nav class="admin-navigation" aria-label="Navigasi admin">
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Utama</p>
                <x-admin.sidebar-link label="Dashboard" icon="dashboard" route="admin.dashboard" :patterns="['admin.dashboard']" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Konten</p>
                <x-admin.sidebar-link label="Berita" icon="news" route="admin.news.index" :patterns="['admin.news.*']" />
                <x-admin.sidebar-link label="Kategori Berita" icon="category" route="admin.news-categories.index" :patterns="['admin.news-categories.*']" />
                <x-admin.sidebar-link label="Pengumuman" icon="announcement" route="admin.announcements.index" :patterns="['admin.announcements.*']" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Informasi</p>
                <x-admin.sidebar-link label="Agenda" icon="calendar" route="admin.agendas.index" :patterns="['admin.agendas.*']" />
                <x-admin.sidebar-link label="Berkas" icon="document" route="admin.documents.index" :patterns="['admin.documents.*', 'admin.document-categories.*']" />
                <x-admin.sidebar-link label="Galeri" icon="gallery" route="admin.gallery.index" :patterns="['admin.gallery.*']" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Profil Desa</p>
                <x-admin.sidebar-link label="Informasi Desa" icon="village" route="admin.village-profile.edit" :patterns="['admin.village-profile.*']" />
                <x-admin.sidebar-link label="Perangkat Desa" icon="users" route="admin.village-officials.index" :patterns="['admin.village-officials.*']" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Tampilan</p>
                <x-admin.sidebar-link label="Banner" icon="image" route="admin.banners.index" :patterns="['admin.banners.*']" />
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-heading">Sistem</p>
                <x-admin.sidebar-link label="Pengguna" icon="user" />
            </div>
        </nav>
        <a class="admin-site-link mt-auto" href="{{ route('home') }}" title="Lihat website">
            <x-admin.icon name="external" /> <span class="admin-link-label">Lihat website</span>
        </a>
    </div>
</aside>
