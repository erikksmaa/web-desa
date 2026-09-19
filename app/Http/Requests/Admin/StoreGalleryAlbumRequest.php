<?php

namespace App\Http\Requests\Admin;

use App\Models\GalleryAlbum;
use App\Support\FileUploads;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['required', Rule::in([GalleryAlbum::STATUS_DRAFT, GalleryAlbum::STATUS_PUBLISHED])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'cover' => ['nullable', ...FileUploads::imageRules()],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['required', ...FileUploads::imageRules()],
        ];
    }
}
