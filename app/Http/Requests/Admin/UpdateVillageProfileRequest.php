<?php

namespace App\Http\Requests\Admin;

use App\Support\FileUploads;
use App\Support\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVillageProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'site_name' => ['required', 'string', 'max:150'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', ...FileUploads::imageRules()],
            'sotk' => ['nullable', ...FileUploads::imageRules()],
        ];
        foreach (['address', 'vision', 'mission', 'history', 'head_welcome', 'footer_description'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:50000'];
        }
        foreach (['facebook', 'instagram', 'youtube', 'map_url'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) use ($field) {
                if (! ($field === 'map_url' ? SafeUrl::map($value) : SafeUrl::web($value))) {
                    $fail($field === 'map_url' ? 'Gunakan URL embed HTTPS Google Maps atau OpenStreetMap.' : 'Gunakan URL HTTP atau HTTPS yang valid.');
                }
            }];
        }

        return $rules;
    }
}
