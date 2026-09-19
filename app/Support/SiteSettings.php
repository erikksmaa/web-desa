<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

final class SiteSettings
{
    public const FIELDS = [
        'site_name' => ['site.name', 'string', 'site'],
        'site_tagline' => ['site.tagline', 'string', 'site'],
        'logo' => ['site.logo', 'string', 'site'],
        'address' => ['village.address', 'text', 'village'],
        'phone' => ['village.phone', 'string', 'village'],
        'email' => ['village.email', 'string', 'village'],
        'vision' => ['village.vision', 'text', 'village'],
        'mission' => ['village.mission', 'text', 'village'],
        'history' => ['village.history', 'text', 'village'],
        'head_welcome' => ['village.head_welcome', 'text', 'village'],
        'facebook' => ['social.facebook', 'string', 'social'],
        'instagram' => ['social.instagram', 'string', 'social'],
        'youtube' => ['social.youtube', 'string', 'social'],
        'map_url' => ['map.embed_url', 'string', 'map'],
        'footer_description' => ['footer.description', 'text', 'footer'],
        'sotk' => ['sotk.image', 'string', 'sotk'],
    ];

    private ?array $values = null;

    public function get(string $key, ?string $fallback = null): ?string
    {
        $this->values ??= Setting::whereIn('key', array_column(self::FIELDS, 0))->pluck('value', 'key')->all();

        return $this->values[$key] ?? $fallback;
    }

    public function image(string $key): ?string
    {
        $path = $this->get($key);

        return $path && Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }

    public function forget(): void
    {
        $this->values = null;
    }
}
