@extends('layouts.public')

@section('title', $news->title.' — '.config('app.name'))
@section('description', $news->excerpt ?: \Illuminate\Support\Str::limit($news->content, 155))

@section('content')
    <article>
        <header class="public-article-header public-news-heading">
            <div class="container public-article-container py-5">
                <nav aria-label="Breadcrumb">
                    <ol class="breadcrumb small mb-4">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('news.index') }}">Berita</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($news->title, 50) }}</li>
                    </ol>
                </nav>
                <span class="public-category-label">{{ $news->category->name }}</span>
                <h1 class="display-6 fw-semibold mt-3 mb-3">{{ $news->title }}</h1>
                <div class="d-flex flex-wrap gap-3 text-muted">
                    <time datetime="{{ $news->published_at->toIso8601String() }}">{{ \App\Support\IndonesianDate::format($news->published_at, true) }}</time>
                    <span>{{ number_format($news->views, 0, ',', '.') }} kali dilihat</span>
                </div>
            </div>
        </header>

        <div class="container public-article-container py-5">
            @if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail))
                <img class="public-article-image mb-5" src="{{ \Illuminate\Support\Facades\Storage::url($news->thumbnail) }}" alt="{{ $news->title }}">
            @endif
            <div class="public-article-content public-plain-text public-reading-text">{{ $news->content }}</div>
        </div>
    </article>

    @if ($relatedNews->isNotEmpty())
        <aside class="border-top bg-white" aria-labelledby="related-news-title">
            <div class="container py-5">
                <h2 class="h3 mb-4" id="related-news-title">Berita terkait</h2>
                <div class="row g-4">
                    @foreach ($relatedNews as $related)
                        <div class="col-md-6 col-xl-3">
                            <x-public.news-card :news="$related" :compact="true" />
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    @endif
@endsection
