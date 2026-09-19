<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $prefix = 'metadata.'.$this->route('galleryPhoto')->id.'.';

        return [
            $prefix.'caption' => ['nullable', 'string', 'max:255'],
            $prefix.'alt_text' => ['nullable', 'string', 'max:255'],
            $prefix.'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }
}
