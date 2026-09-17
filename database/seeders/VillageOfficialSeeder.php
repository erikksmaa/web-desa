<?php

namespace Database\Seeders;

use App\Models\VillageOfficial;
use Illuminate\Database\Seeder;

class VillageOfficialSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $hasActiveHead = VillageOfficial::active()->where('is_village_head', true)->exists();
        foreach (['Kepala Desa', 'Sekretaris Desa', 'Kaur Tata Usaha', 'Kaur Keuangan', 'Kasi Pemerintahan', 'Kasi Pelayanan'] as $order => $position) {
            VillageOfficial::firstOrCreate(['name' => 'Contoh '.$position, 'position' => $position], [
                'biography' => 'Profil fiktif untuk pengembangan, bukan data pejabat resmi.',
                'is_village_head' => $order === 0 && ! $hasActiveHead,
                'is_active' => true,
                'sort_order' => $order,
            ]);
        }
    }
}
