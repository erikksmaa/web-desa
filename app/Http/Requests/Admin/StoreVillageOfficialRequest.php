<?php

namespace App\Http\Requests\Admin;

use App\Support\FileUploads;
use Illuminate\Foundation\Http\FormRequest;

class StoreVillageOfficialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:150'],
            'biography' => ['nullable', 'string', 'max:15000'],
            'photo' => ['nullable', ...FileUploads::imageRules()],
            'is_active' => ['required', 'boolean'],
            'is_village_head' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }
}
