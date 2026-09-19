<section class="public-hero" aria-label="Informasi utama desa">
@if ($banners->isNotEmpty())
    <h1 class="visually-hidden">{{ $siteSettings->get('site.name', config('app.name')) }}</h1>
    <div id="village-hero" @class(['public-hero-frame', 'carousel slide' => $banners->count() > 1]) @if ($banners->count() > 1) data-bs-interval="false" role="region" aria-roledescription="carousel" aria-label="Banner informasi desa" @endif>
        <div @class(['carousel-inner' => $banners->count() > 1])>
        @foreach ($banners as $banner)
            @php($hasImage = $banner->image_path && Storage::disk('public')->exists($banner->image_path))
            <article @class(['public-hero-slide', 'public-hero-slide--fallback' => !$hasImage, 'carousel-item' => $banners->count() > 1, 'active' => $loop->first && $banners->count() > 1]) @if ($banners->count() > 1) role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} dari {{ $banners->count() }}" @endif>
                @if ($hasImage)
                    <img class="public-hero-image" src="{{ Storage::disk('public')->url($banner->image_path) }}" alt="{{ $banner->title ?: 'Pemandangan '.$siteSettings->get('site.name', config('app.name')) }}" @if (!$loop->first) loading="lazy" @else fetchpriority="high" @endif>
                @else
                    <div class="public-hero-landscape" aria-hidden="true"><span></span><span></span><span></span></div>
                @endif
                <div class="public-hero-overlay" aria-hidden="true"></div>
                <div class="container public-hero-copy">
                    <div class="public-hero-copy-inner">
                        <p class="public-hero-kicker">Selamat datang di portal resmi</p>
                        <h2>{{ $banner->title ?: $siteSettings->get('site.name', config('app.name')) }}</h2>
                        @if ($banner->subtitle)<p class="public-hero-subtitle">{{ $banner->subtitle }}</p>
                        @else<p class="public-hero-subtitle">Informasi, pelayanan, dan kabar desa untuk seluruh masyarakat.</p>@endif
                        @if ($banner->cta_label && \App\Support\SafeUrl::web($banner->cta_url))<a class="btn btn-light public-hero-cta" href="{{ $banner->cta_url }}">{{ $banner->cta_label }}<span aria-hidden="true"> →</span></a>@endif
                    </div>
                </div>
            </article>
        @endforeach
        </div>
        @if ($banners->count() > 1)
        <button class="public-carousel-control public-carousel-control--prev" type="button" data-bs-target="#village-hero" data-bs-slide="prev"><span aria-hidden="true">‹</span><span class="visually-hidden">Banner sebelumnya</span></button>
        <button class="public-carousel-control public-carousel-control--next" type="button" data-bs-target="#village-hero" data-bs-slide="next"><span aria-hidden="true">›</span><span class="visually-hidden">Banner berikutnya</span></button>
        <div class="public-hero-counter" aria-hidden="true">{{ str_pad($banners->count(), 2, '0', STR_PAD_LEFT) }} informasi pilihan</div>
        @endif
    </div>
@else
    <div class="public-hero-frame public-hero-frame--welcome">
        <div class="public-hero-slide public-hero-slide--fallback">
            <div class="public-hero-landscape" aria-hidden="true"><span></span><span></span><span></span></div>
            <div class="public-hero-overlay" aria-hidden="true"></div>
            <div class="container public-hero-copy">
                <div class="public-hero-copy-inner">
                    <p class="public-hero-kicker">Selamat datang di portal resmi</p>
                    <h1>{{ $siteSettings->get('site.name', config('app.name')) }}</h1>
                    <p class="public-hero-subtitle">{{ $siteSettings->get('site.tagline') ?: 'Informasi, pelayanan, dan kabar desa untuk seluruh masyarakat.' }}</p>
                    <div class="d-flex flex-wrap gap-3 mt-4"><a class="btn btn-light public-hero-cta" href="{{ route('profile.index') }}">Mengenal desa<span aria-hidden="true"> →</span></a><a class="btn btn-outline-light public-hero-cta" href="{{ route('news.index') }}">Lihat kabar terbaru</a></div>
                </div>
            </div>
        </div>
    </div>
@endif
</section>
