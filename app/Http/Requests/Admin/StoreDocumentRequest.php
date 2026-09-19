<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use App\Support\FileUploads;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $currentCategory = $this->route('document')?->document_category_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_category_id' => ['required', 'integer', Rule::exists('document_categories', 'id')->where(fn ($q) => $q->where(fn ($q) => $q->where('is_active', true)->when($currentCategory, fn ($q) => $q->orWhere('id', $currentCategory))))],
            'file' => [$this->route('document') ? 'nullable' : 'required', ...FileUploads::documentRules(15360)],
            'status' => ['required', Rule::in([Document::STATUS_DRAFT, Document::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return ['file' => 'berkas', 'title' => 'judul', 'document_category_id' => 'kategori'];
    }
}
