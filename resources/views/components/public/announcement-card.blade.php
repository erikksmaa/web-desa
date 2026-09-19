@props(['item', 'heading' => 2])
@php($level = $heading === 3 ? 3 : 2)
<article class="public-information-card">
    <div class="public-notice-mark" aria-hidden="true">!</div>
    <div class="public-information-card__body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3"><span class="public-eyebrow mb-0">Pengumuman resmi</span><span class="public-metadata"><x-admin.date-text :date="$item->published_at" /></span></div>
        <h{{ $level }} class="h5 public-card-title"><a href="{{ route('announcements.show', $item->slug) }}">{{ $item->title }}</a></h{{ $level }}>
        <p class="text-muted">{{ Str::limit($item->content, 200) }}</p>
        @if ($item->expires_at)<p class="public-expiry">Berlaku hingga {{ \App\Support\IndonesianDate::format($item->expires_at, true) }} WIB</p>@endif
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
            <a class="public-text-link" href="{{ route('announcements.show', $item->slug) }}">Baca pengumuman<span class="visually-hidden">: {{ $item->title }}</span><span aria-hidden="true"> →</span></a>
            @if ($item->attachment_path)<span class="public-attachment-label">Lampiran tersedia</span>@endif
        </div>
    </div>
</article>
