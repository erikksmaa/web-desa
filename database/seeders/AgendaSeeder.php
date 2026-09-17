<?php

namespace Database\Seeders;

use App\Models\Agenda;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AgendaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $authorId = User::where('email', 'admin@example.com')->value('id');
        foreach (['Musyawarah desa', 'Kerja bakti bersama', 'Rapat perangkat desa', 'Pelatihan layanan informasi', 'Evaluasi kegiatan desa'] as $order => $title) {
            $start = now()->startOfDay()->addDays(($order - 1) * 7)->setTime(9, 0);
            Agenda::withTrashed()->firstOrCreate(['slug' => 'contoh-'.Str::slug($title)], [
                'user_id' => $authorId,
                'title' => $title.' (Contoh)',
                'description' => 'Agenda fiktif untuk pengembangan, bukan undangan kegiatan resmi.',
                'location' => 'Balai Desa (contoh)',
                'start_at' => $start,
                'end_at' => $start->copy()->addHours(2),
                'status' => $order < 4 ? Agenda::STATUS_PUBLISHED : Agenda::STATUS_DRAFT,
            ]);
        }
    }
}
