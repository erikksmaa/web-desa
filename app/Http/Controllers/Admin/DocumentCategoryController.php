<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentCategoryRequest;
use App\Http\Requests\Admin\UpdateDocumentCategoryRequest;
use App\Models\DocumentCategory;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentCategoryController extends Controller
{
    public function index(): View
    {
        $categories = DocumentCategory::query()
            ->withCount('documents')
            ->when(request('search'), fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.document-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.document-categories.create');
    }

    public function store(StoreDocumentCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::generate(DocumentCategory::query(), $data['name']);
        DocumentCategory::create($data);

        return to_route('admin.document-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(DocumentCategory $documentCategory): View
    {
        return view('admin.document-categories.edit', compact('documentCategory'));
    }

    public function update(UpdateDocumentCategoryRequest $request, DocumentCategory $documentCategory): RedirectResponse
    {
        $data = $request->validated();

        if ($data['name'] !== $documentCategory->name) {
            $data['slug'] = UniqueSlug::generate(DocumentCategory::query(), $data['name'], $documentCategory->id);
        }

        $documentCategory->update($data);

        return to_route('admin.document-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(DocumentCategory $documentCategory): RedirectResponse
    {
        if ($documentCategory->documents()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh berkas.');
        }

        $documentCategory->delete();

        return to_route('admin.document-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
