<nav class="navbar navbar-expand-lg bg-white border-bottom" aria-label="Navigasi utama">
    <div class="container py-2">
        <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#public-navigation" aria-controls="public-navigation" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="public-navigation">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="{{ route('home') }}" aria-current="page">Beranda</a></li>
                @foreach (['Profil Desa', 'Berita', 'Pengumuman', 'Agenda', 'Berkas', 'Galeri'] as $label)
                    <li class="nav-item"><span class="nav-link disabled" aria-disabled="true">{{ $label }}<span class="visually-hidden"> — belum tersedia</span></span></li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
