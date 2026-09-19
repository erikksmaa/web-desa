<?php

namespace App\Http\Requests\Admin;

use App\Support\FileUploads;
use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryPhotosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['photos' => ['required', 'array', 'min:1', 'max:10'], 'photos.*' => ['required', ...FileUploads::imageRules()]];
    }
}
