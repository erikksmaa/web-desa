<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsCategoryRequest;
use App\Http\Requests\Admin\UpdateNewsCategoryRequest;
use App\Models\NewsCategory;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsCategoryController extends Controller
{
    public function index(): View
    {
        $categories = NewsCategory::query()
            ->withCount('news')
            ->when(request('search'), fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.news-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.news-categories.create');
    }

    public function store(StoreNewsCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::generate(NewsCategory::query(), $data['name']);
        NewsCategory::create($data);

        return to_route('admin.news-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(NewsCategory $newsCategory): View
    {
        return view('admin.news-categories.edit', compact('newsCategory'));
    }

    public function update(UpdateNewsCategoryRequest $request, NewsCategory $newsCategory): RedirectResponse
    {
        $data = $request->validated();

        if ($data['name'] !== $newsCategory->name) {
            $data['slug'] = UniqueSlug::generate(NewsCategory::query(), $data['name'], $newsCategory->id);
        }

        $newsCategory->update($data);

        return to_route('admin.news-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(NewsCategory $newsCategory): RedirectResponse
    {
        if ($newsCategory->news()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh berita.');
        }

        $newsCategory->delete();

        return to_route('admin.news-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
