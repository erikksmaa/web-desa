<nav class="navbar navbar-expand-xl navbar-light public-navbar" aria-label="Navigasi utama">
    <div class="container public-navbar__inner">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            @if ($logo = $siteSettings->image('site.logo'))<img class="public-logo" src="{{ $logo }}" alt="Logo {{ $siteSettings->get('site.name', config('app.name')) }}">@else<span class="public-brand-emblem" aria-hidden="true">D</span>@endif
            <span class="public-brand-copy"><span class="public-site-name">{{ $siteSettings->get('site.name', config('app.name')) }}</span><span class="public-brand-description">Portal informasi resmi desa</span></span>
        </a>
        <button class="navbar-toggler public-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#public-navigation" aria-controls="public-navigation" aria-expanded="false" aria-label="Buka navigasi"><span class="public-menu-label">Menu</span><span class="navbar-toggler-icon" aria-hidden="true"></span></button>
        <div class="collapse navbar-collapse" id="public-navigation">
            <ul class="navbar-nav ms-auto align-items-xl-center">
                <li class="nav-item"><a @class(['nav-link','active'=>request()->routeIs('home')]) href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Beranda</a></li>
                <li class="nav-item dropdown">
                    <button @class(['nav-link dropdown-toggle','active'=>request()->routeIs('profile.*')]) id="profile-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">Profil Desa</button>
                    <ul class="dropdown-menu" aria-labelledby="profile-menu">
                        @foreach (['index'=>'Visi, Misi & Sejarah','organization'=>'SOTK','officials'=>'Perangkat Desa'] as $action=>$label)
                        <li><a @class(['dropdown-item','active'=>request()->routeIs('profile.'.$action)]) href="{{ route('profile.'.$action) }}" @if(request()->routeIs('profile.'.$action)) aria-current="page" @endif>{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </li>
                @foreach (['news'=>'Berita','announcements'=>'Pengumuman','agendas'=>'Agenda','documents'=>'Berkas','gallery'=>'Galeri'] as $prefix=>$label)
                    <li class="nav-item"><a @class(['nav-link','active'=>request()->routeIs($prefix.'.*')]) href="{{ route($prefix.'.index') }}" @if (request()->routeIs($prefix.'.*')) aria-current="page" @endif>{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
