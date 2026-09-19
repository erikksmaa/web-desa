<?php

namespace App\Http\Requests\Admin;

use App\Models\News;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->input('title')) ? trim($this->input('title')) : $this->input('title'),
            'excerpt' => is_string($this->input('excerpt')) ? trim($this->input('excerpt')) : $this->input('excerpt'),
            'content' => is_string($this->input('content')) ? trim($this->input('content')) : $this->input('content'),
        ]);
    }

    public function rules(): array
    {
        $currentCategoryId = $this->route('news')?->news_category_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'news_category_id' => [
                'required',
                'integer',
                Rule::exists('news_categories', 'id')->where(
                    fn (Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->when($currentCategoryId, fn (Builder $builder): Builder => $builder->orWhere('id', $currentCategoryId)),
                ),
            ],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'status' => ['required', Rule::in([News::STATUS_DRAFT, News::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
