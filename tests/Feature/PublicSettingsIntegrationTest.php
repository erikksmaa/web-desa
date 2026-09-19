<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicSettingsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function setting(string $key, ?string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'string', 'group' => 'site']);
    }

    public function test_branding_contacts_social_map_and_profile_navigation(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/logo/logo.png', 'image');
        foreach (['site.name' => 'Desa Dinamis', 'site.logo' => 'settings/logo/logo.png', 'footer.description' => 'Footer dinamis', 'village.address' => 'Jalan Desa 1', 'village.phone' => '+62 (021) 321', 'village.email' => 'desa@example.com', 'social.facebook' => 'https://facebook.com/desauji', 'map.embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=1,2,3,4'] as $key => $value) {
            $this->setting($key, $value);
        }
        foreach (['home', 'profile.index', 'profile.organization', 'profile.officials', 'news.index', 'announcements.index', 'agendas.index', 'documents.index', 'gallery.index'] as $route) {
            $response = $this->get(route($route))->assertOk()->assertSeeText('Desa Dinamis')->assertSeeText('Jalan Desa 1')->assertSeeText('Footer dinamis')->assertSee('mailto:desa@example.com', false)->assertSee('https://facebook.com/desauji')->assertSee('referrerpolicy="no-referrer"', false)->assertSee(Storage::disk('public')->url('settings/logo/logo.png'))->assertDontSee('href="#"', false);
            foreach (['profile.index', 'profile.organization', 'profile.officials'] as $profile) {
                $response->assertSee(route($profile), false);
            }
        }
    }

    public function test_missing_optional_settings_and_untrusted_existing_values_are_safe(): void
    {
        Storage::fake('public');
        $this->get(route('home'))->assertOk()->assertDontSee('<iframe', false)->assertDontSee('mailto:', false)->assertDontSeeText('Facebook')->assertDontSeeText('Kontak Desa');
        foreach (['site.name' => '<script>name()</script>', 'footer.description' => '<script>footer()</script>', 'village.address' => '<img src=x onerror=bad()>', 'social.facebook' => 'javascript:alert(1)', 'map.embed_url' => '<iframe src="https://evil.test"></iframe>', 'site.logo' => 'missing.png'] as $key => $value) {
            $this->setting($key, $value);
        }
        $this->get(route('home'))->assertOk()->assertSee('&lt;script&gt;name()', false)->assertDontSee('<script>name()', false)->assertDontSee('<script>footer()', false)->assertDontSee('<img src=x', false)->assertDontSee('javascript:', false)->assertDontSee('<iframe', false)->assertDontSee('src="/storage/missing.png"', false);
    }

    public function test_settings_edits_are_visible_on_next_request_without_stale_cache(): void
    {
        $this->setting('site.name', 'Before Change');
        $this->get(route('home'))->assertSeeText('Before Change');
        $this->actingAs(User::factory()->create())->patch(route('admin.village-profile.update'), ['site_name' => 'After Change'])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertSeeText('After Change')->assertDontSeeText('Before Change');
    }

    public function test_admin_links_are_active_and_user_management_stays_disabled(): void
    {
        $this->actingAs(User::factory()->create());
        $response = $this->get(route('admin.dashboard'))->assertOk();
        foreach (['admin.village-profile.edit', 'admin.village-officials.index', 'admin.banners.index'] as $route) {
            $response->assertSee(route($route), false);
        }
        $this->get('/admin/users')->assertNotFound();
    }
}
