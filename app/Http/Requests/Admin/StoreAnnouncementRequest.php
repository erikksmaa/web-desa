<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use App\Support\FileUploads;
use App\Support\PublicationDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'attachment' => ['nullable', ...FileUploads::documentRules(10240)],
            'status' => ['required', Rule::in([Announcement::STATUS_DRAFT, Announcement::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->filled('expires_at')) {
                return;
            }
            $date = PublicationDate::resolve($this->only(['status', 'published_at']), $this->route('announcement')?->published_at);
            if ($date && $date->gt($this->date('expires_at'))) {
                $validator->errors()->add('expires_at', 'Tanggal berakhir tidak boleh sebelum tanggal publikasi.');
            }
        }];
    }

    public function attributes(): array
    {
        return ['title' => 'judul', 'content' => 'isi pengumuman', 'attachment' => 'lampiran', 'expires_at' => 'tanggal berakhir'];
    }
}
