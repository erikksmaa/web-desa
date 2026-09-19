@props(['title', 'description' => null, 'eyebrow' => 'Informasi desa', 'variant' => 'default'])
<header @class(['public-page-header', 'public-page-header--'.$variant])>
    <div class="container public-page-header__inner">
        <nav aria-label="Breadcrumb"><ol class="breadcrumb small mb-4"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item active" aria-current="page">{{ $title }}</li></ol></nav>
        <p class="public-eyebrow">{{ $eyebrow }}</p>
        <h1 class="mb-3">{{ $title }}</h1>
        @if ($description)<p class="public-page-intro mb-0">{{ $description }}</p>@endif
    </div>
</header>
