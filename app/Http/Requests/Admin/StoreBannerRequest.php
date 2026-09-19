<?php

namespace App\Http\Requests\Admin;

use App\Support\FileUploads;
use App\Support\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => [$this->route('banner') ? 'nullable' : 'required', ...FileUploads::imageRules()],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'required_with:cta_label', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (! SafeUrl::web($value)) {
                    $fail('Gunakan URL lengkap HTTP atau HTTPS yang valid.');
                }
            }],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', ...($this->filled('starts_at') ? ['after:starts_at'] : [])],
        ];
    }
}
