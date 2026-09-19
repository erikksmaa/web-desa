<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Http\Requests\Admin\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Support\FileUploads;
use App\Support\PublicationDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'status' => 'nullable|in:draft,published', 'category' => 'nullable|integer']);
        $documents = Document::with('category:id,name')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%')->orWhere('original_filename', 'like', '%'.$search.'%')->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('document_category_id', $id))
            ->latest()->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = DocumentCategory::orderBy('sort_order')->orderBy('name')->pluck('name', 'id');

        return view('admin.documents.index', compact('documents', 'categories'));
    }

    public function create(): View
    {
        return view('admin.documents.create', ['document' => new Document, 'categories' => $this->categoryOptions()]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->persist($request, new Document(['user_id' => $request->user()->id]));

        return to_route('admin.documents.index')->with('success', 'Berkas berhasil ditambahkan.');
    }

    public function edit(Document $document): View
    {
        return view('admin.documents.edit', ['document' => $document, 'categories' => $this->categoryOptions($document->document_category_id)]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->persist($request, $document);

        return to_route('admin.documents.index')->with('success', 'Berkas berhasil diperbarui.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();

        return to_route('admin.documents.index')->with('success', 'Berkas berhasil dihapus.');
    }

    private function categoryOptions(?int $current = null): Collection
    {
        return DocumentCategory::where(fn ($q) => $q->where('is_active', true)->when($current, fn ($q) => $q->orWhereKey($current)))
            ->orderBy('sort_order')->orderBy('name')->pluck('name', 'id');
    }

    private function persist(StoreDocumentRequest $request, Document $document): void
    {
        $data = $request->safe()->except('file');
        $data['published_at'] = PublicationDate::resolve($data, $document->published_at);
        $old = $document->file_path;
        $new = null;
        try {
            if ($file = $request->file('file')) {
                $new = FileUploads::store($file, 'local', 'documents', 'file');
                $data = [...$data, 'file_path' => $new, 'original_filename' => FileUploads::originalName($file->getClientOriginalName()), 'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize()];
            }
            DB::transaction(fn () => $document->fill($data)->save());
        } catch (Throwable $e) {
            FileUploads::remove('local', [$new]);
            throw $e;
        }
        if ($new) {
            FileUploads::remove('local',[$old]);
        }
    }
}
