<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $authorId = User::where('email', 'admin@example.com')->value('id');
        $categories = DocumentCategory::whereIn('slug', ['peraturan-desa', 'laporan-desa', 'formulir-layanan'])->orderBy('sort_order')->get();
        foreach (['Peraturan tata tertib', 'Laporan kegiatan tahunan', 'Formulir permohonan informasi', 'Peraturan fasilitas umum', 'Laporan pelayanan', 'Formulir administrasi'] as $order => $title) {
            $filename = 'contoh-'.Str::slug($title).'.pdf';
            Document::withTrashed()->firstOrCreate(['file_path' => 'development/placeholders/documents/'.$filename], [
                'document_category_id' => $categories[$order % $categories->count()]->id,
                'user_id' => $authorId,
                'title' => $title.' (Contoh)',
                'description' => 'Placeholder pengembangan; berkas fisik belum tersedia. Jangan dipublikasikan sebelum berkas diganti.',
                'original_filename' => $filename,
                'mime_type' => 'application/pdf',
                'status' => Document::STATUS_DRAFT,
            ]);
        }
    }
}
