<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Support\FileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'category' => 'nullable|integer']);
        $documents = Document::published()->with('category:id,name')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', '%'.$search.'%'))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('document_category_id', $id))
            ->latest('published_at')->orderByDesc('id')->paginate(12)->withQueryString();
        $categories = DocumentCategory::whereHas('documents', fn ($q) => $q->published())->orderBy('sort_order')->orderBy('name')->pluck('name', 'id');

        return view('public.documents.index', compact('documents', 'categories'));
    }

    public function download(string $document): StreamedResponse
    {
        $item = Document::published()->whereKey($document)->firstOrFail();
        abort_unless(Storage::disk('local')->exists($item->file_path), 404);
        $response = Storage::disk('local')->download($item->file_path, FileUploads::originalName($item->original_filename), ['X-Content-Type-Options' => 'nosniff']);
        $item->increment('download_count');

        return $response;
    }
}
