@props(['news', 'compact' => false])
<article @class(['public-news-card', 'public-news-card--compact' => $compact])>
    <div class="public-card-media">
        @if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail))
            <img class="public-news-card__image" src="{{ Storage::disk('public')->url($news->thumbnail) }}" alt="{{ $news->title }}" loading="lazy">
        @else
            <div class="public-news-card__image public-news-card__placeholder" aria-hidden="true">Berita Desa</div>
        @endif
        <span class="public-category-label public-media-label">{{ $news->category->name }}</span>
    </div>
    <div class="public-news-card__body">
        <p class="public-metadata mb-2"><x-admin.date-text :date="$news->published_at" /> <span aria-hidden="true">·</span> {{ number_format($news->views, 0, ',', '.') }} dilihat</p>
        <h3 class="h5 public-card-title"><a href="{{ route('news.show', $news->slug) }}">{{ $news->title }}</a></h3>
        @if (!$compact)<p class="text-muted public-news-card__excerpt">{{ Str::limit($news->excerpt ?: $news->content, 160) }}</p>@endif
        <a class="public-text-link" href="{{ route('news.show', $news->slug) }}">Baca selengkapnya<span class="visually-hidden">: {{ $news->title }}</span><span aria-hidden="true"> →</span></a>
    </div>
</article>
