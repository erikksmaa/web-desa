<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $settings = [
            'site.name' => ['Website Desa', Setting::TYPE_STRING, 'site'],
            'site.tagline' => ['Website informasi desa', Setting::TYPE_STRING, 'site'],
            'site.logo' => [null, Setting::TYPE_STRING, 'site'],
            'village.address' => [null, Setting::TYPE_TEXT, 'village'],
            'village.phone' => [null, Setting::TYPE_STRING, 'village'],
            'village.email' => [null, Setting::TYPE_STRING, 'village'],
            'village.vision' => [null, Setting::TYPE_TEXT, 'village'],
            'village.mission' => [null, Setting::TYPE_TEXT, 'village'],
            'village.history' => [null, Setting::TYPE_TEXT, 'village'],
            'village.head_welcome' => [null, Setting::TYPE_TEXT, 'village'],
            'social.facebook' => [null, Setting::TYPE_STRING, 'social'],
            'social.instagram' => [null, Setting::TYPE_STRING, 'social'],
            'social.youtube' => [null, Setting::TYPE_STRING, 'social'],
            'map.embed_url' => [null, Setting::TYPE_STRING, 'map'],
            'footer.description' => [null, Setting::TYPE_TEXT, 'footer'],
            'sotk.image' => [null, Setting::TYPE_STRING, 'sotk'],
        ];
        foreach ($settings as $key => [$value, $type, $group]) {
            Setting::firstOrCreate(['key' => $key], compact('value', 'type', 'group'));
        }
    }
}
