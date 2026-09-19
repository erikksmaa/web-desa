<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsRequest;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\News;
use App\Models\NewsCategory;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class NewsController extends Controller
{
    public function index(): View
    {
        $newsItems = News::query()
            ->with('category:id,name')
            ->when(request('search'), function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('title', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when(request('status'), fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when(request('category'), fn (Builder $query, string $category): Builder => $query->where('news_category_id', $category))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = NewsCategory::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('admin.news.index', compact('newsItems', 'categories'));
    }

    public function create(): View
    {
        return view('admin.news.create', ['categories' => $this->categoryOptions()]);
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('thumbnail');
        $newThumbnail = $request->file('thumbnail')?->store('news/thumbnails', 'public');

        try {
            DB::transaction(function () use ($request, $data, $newThumbnail): void {
                News::create([
                    ...$data,
                    'user_id' => $request->user()->id,
                    'slug' => UniqueSlug::generate(News::withTrashed(), $data['title']),
                    'thumbnail' => $newThumbnail,
                    'published_at' => $this->publicationDate($data),
                ]);
            });
        } catch (Throwable $exception) {
            if ($newThumbnail) {
                Storage::disk('public')->delete($newThumbnail);
            }

            throw $exception;
        }

        return to_route('admin.news.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(News $news): View
    {
        return view('admin.news.edit', [
            'news' => $news,
            'categories' => $this->categoryOptions($news->news_category_id),
        ]);
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $data = $request->safe()->except('thumbnail');
        $newThumbnail = $request->file('thumbnail')?->store('news/thumbnails', 'public');
        $oldThumbnail = $news->thumbnail;

        try {
            DB::transaction(function () use ($data, $newThumbnail, $news): void {
                if ($data['title'] !== $news->title) {
                    $data['slug'] = UniqueSlug::generate(News::withTrashed(), $data['title'], $news->id);
                }

                if ($newThumbnail) {
                    $data['thumbnail'] = $newThumbnail;
                }

                $data['published_at'] = $this->publicationDate($data, $news);
                $news->update($data);
            });
        } catch (Throwable $exception) {
            if ($newThumbnail) {
                Storage::disk('public')->delete($newThumbnail);
            }

            throw $exception;
        }

        if ($newThumbnail && $oldThumbnail) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        return to_route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return to_route('admin.news.index')->with('success', 'Berita berhasil dihapus.');
    }

    private function categoryOptions(?int $currentCategoryId = null): Collection
    {
        return NewsCategory::query()
            ->where(function (Builder $query) use ($currentCategoryId): void {
                $query->where('is_active', true)
                    ->when($currentCategoryId, fn (Builder $builder): Builder => $builder->orWhereKey($currentCategoryId));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    private function publicationDate(array $data, ?News $news = null): mixed
    {
        if ($data['status'] !== News::STATUS_PUBLISHED) {
            return $data['published_at'] ?? $news?->published_at;
        }

        return $data['published_at'] ?: $news?->published_at ?: now();
    }
}
