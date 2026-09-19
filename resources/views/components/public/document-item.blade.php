@props(['item', 'compact' => false])
@php($level = $compact ? 3 : 2)
<article class="public-document-row">
    <span class="public-file-type"><span aria-hidden="true">↓</span><strong>{{ strtoupper(pathinfo($item->original_filename, PATHINFO_EXTENSION)) ?: 'FILE' }}</strong></span>
    <div class="flex-grow-1">
        <p class="public-metadata mb-2">{{ $item->category->name }} · <x-admin.date-text :date="$item->published_at" /></p>
        <h{{ $level }} class="h5 mb-2">{{ $item->title }}</h{{ $level }}>
        @if (!$compact && $item->description)<p class="public-plain-text text-muted">{{ $item->description }}</p>@endif
        <p class="public-metadata mb-0">{{ \App\Support\HumanFileSize::format($item->file_size) }} · {{ number_format($item->download_count, 0, ',', '.') }} unduhan</p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('documents.download', $item) }}">Unduh berkas<span class="visually-hidden">: {{ $item->title }}</span><span aria-hidden="true"> ↓</span></a>
</article>
