<?php

namespace App\Http\Requests\Admin;

use App\Models\Agenda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgendaRequest extends FormRequest
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
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'status' => ['required', Rule::in([Agenda::STATUS_DRAFT, Agenda::STATUS_PUBLISHED])],
        ];
    }

    public function messages(): array
    {
        return ['end_at.after_or_equal' => 'Waktu selesai tidak boleh sebelum waktu mulai.'];
    }
}
