<footer class="public-footer">
<div class="container public-footer__main">
<div class="row g-5">
<div class="col-lg-4">
<div class="public-footer-brand">
@if ($logo = $siteSettings->image('site.logo'))<img class="public-footer-logo" src="{{ $logo }}" alt="Logo {{ $siteSettings->get('site.name', config('app.name')) }}">@else<span class="public-footer-emblem" aria-hidden="true">D</span>@endif
<div><h2 class="h4 mb-1">{{ $siteSettings->get('site.name', config('app.name')) }}</h2><p class="public-footer-kicker">Portal informasi resmi desa</p></div>
</div>
@if ($description = $siteSettings->get('footer.description'))<p class="public-plain-text public-footer-description">{{ $description }}</p>@elseif ($tagline = $siteSettings->get('site.tagline'))<p class="public-footer-description">{{ $tagline }}</p>@endif
<ul class="list-unstyled d-flex flex-wrap gap-3 public-social-links mb-0">
@foreach (['facebook'=>'Facebook','instagram'=>'Instagram','youtube'=>'YouTube'] as $key=>$label)
@if (\App\Support\SafeUrl::web($url = $siteSettings->get('social.'.$key)))<li><a href="{{ $url }}">{{ $label }}</a></li>@endif
@endforeach
</ul>
</div>
<div class="col-6 col-lg-2"><h2 class="public-footer-heading">Jelajahi</h2><ul class="list-unstyled public-footer-links"><li><a href="{{ route('home') }}">Beranda</a></li><li><a href="{{ route('profile.index') }}">Profil Desa</a></li><li><a href="{{ route('news.index') }}">Berita</a></li><li><a href="{{ route('gallery.index') }}">Galeri</a></li></ul></div>
<div class="col-6 col-lg-2"><h2 class="public-footer-heading">Informasi</h2><ul class="list-unstyled public-footer-links"><li><a href="{{ route('announcements.index') }}">Pengumuman</a></li><li><a href="{{ route('agendas.index') }}">Agenda</a></li><li><a href="{{ route('documents.index') }}">Berkas Publik</a></li><li><a href="{{ route('profile.officials') }}">Perangkat Desa</a></li></ul></div>
@if ($siteSettings->get('village.address') || $siteSettings->get('village.phone') || $siteSettings->get('village.email'))
<div class="col-lg-4">
<h2 class="public-footer-heading">Kontak Desa</h2>
<div class="public-footer-contact">
@if ($address = $siteSettings->get('village.address'))<p class="public-plain-text"><span aria-hidden="true">⌖</span> {{ $address }}</p>@endif
@if ($phone = $siteSettings->get('village.phone'))<p><span aria-hidden="true">☎</span> {{ $phone }}</p>@endif
@if ($email = $siteSettings->get('village.email'))
<p><span aria-hidden="true">✉</span> @if (filter_var($email, FILTER_VALIDATE_EMAIL))<a href="mailto:{{ $email }}">{{ $email }}</a>@else{{ $email }}@endif</p>
@endif
</div>
</div>
@endif
</div>
</div>
@if (\App\Support\SafeUrl::map($map = $siteSettings->get('map.embed_url')))<div class="container pb-5"><div class="public-footer-map"><iframe class="public-map" src="{{ $map }}" title="Peta lokasi {{ $siteSettings->get('site.name', config('app.name')) }}" loading="lazy" referrerpolicy="no-referrer" sandbox="allow-scripts allow-same-origin allow-popups" allowfullscreen></iframe></div></div>@endif
<div class="public-footer-bottom"><div class="container d-flex flex-wrap justify-content-between gap-2"><p class="mb-0">&copy; {{ date('Y') }} {{ $siteSettings->get('site.name', config('app.name')) }}.</p><p class="mb-0">Informasi desa untuk seluruh masyarakat.</p></div></div>
</footer>
