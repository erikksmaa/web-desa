<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Document> */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_category_id' => DocumentCategory::factory(),
            'user_id' => null,
            'title' => fake()->sentence(5),
            'description' => 'Data uji; berkas belum tersedia.',
            'file_path' => 'development/placeholders/documents/'.fake()->uuid().'.pdf',
            'original_filename' => 'contoh-dokumen.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => null,
            'status' => Document::STATUS_DRAFT,
            'published_at' => null,
            'download_count' => 0,
        ];
    }
}
