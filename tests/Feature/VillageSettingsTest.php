<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VillageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_and_settings_page(): void
    {
        $this->get(route('admin.village-profile.edit'))->assertRedirect(route('admin.login'));
        $this->patch(route('admin.village-profile.update'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get(route('admin.village-profile.edit'))->assertOk()->assertSeeText('Informasi Desa')->assertSee('name="_token"', false);
    }

    public function test_approved_fields_are_saved_without_arbitrary_keys_or_head_identity(): void
    {
        $data = ['site_name' => 'Desa Uji', 'vision' => 'Visi baru', 'mission' => "Misi satu\nMisi dua", 'history' => 'Sejarah', 'head_welcome' => 'Sambutan', 'address' => 'Alamat', 'phone' => '+62 (021) 123', 'email' => 'desa@example.com', 'facebook' => 'https://facebook.com/desauji', 'map_url' => 'https://www.google.com/maps/embed?pb=abc', 'footer_description' => 'Footer', 'key' => 'evil', 'type' => 'json', 'group' => 'evil', 'village.head_name' => 'Injected'];
        $this->actingAs(User::factory()->create())->patch(route('admin.village-profile.update'), $data)->assertSessionHasNoErrors()->assertRedirect(route('admin.village-profile.edit'));
        foreach (SiteSettings::FIELDS as $field => [$key, $type, $group]) {
            if (in_array($field, ['logo', 'sotk'])) {
                continue;
            }
            $this->assertDatabaseHas('settings', ['key' => $key, 'value' => $data[$field] ?? null, 'type' => $type, 'group' => $group]);
        }
        $this->assertDatabaseMissing('settings', ['key' => 'evil']);
        $this->assertDatabaseMissing('settings', ['key' => 'village.head_name']);
        $this->assertDatabaseCount('settings', 14);
        $this->get(route('admin.village-profile.edit'))->assertSee('Desa Uji');
    }

    public function test_urls_email_and_images_are_validated(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        foreach (['javascript:alert(1)', 'data:text/html,test', '<iframe src="https://example.com"></iframe>', 'https://www.google.com.evil.test/maps/embed', 'https://example.com/map'] as $url) {
            $this->patch(route('admin.village-profile.update'), ['site_name' => 'Desa', 'map_url' => $url])->assertSessionHasErrors('map_url');
        }
        $this->patch(route('admin.village-profile.update'), ['site_name' => 'Desa', 'facebook' => 'javascript:alert(1)', 'email' => 'bad', 'logo' => UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml'), 'sotk' => UploadedFile::fake()->image('chart.png')->size(5121)])->assertSessionHasErrors(['facebook', 'email', 'logo', 'sotk']);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_logo_and_sotk_replacement_and_preservation(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->patch(route('admin.village-profile.update'), ['site_name' => 'Desa', 'logo' => UploadedFile::fake()->image('logo.png'), 'sotk' => UploadedFile::fake()->image('sotk.jpg')])->assertSessionHasNoErrors();
        $old = Setting::whereIn('key', ['site.logo', 'sotk.image'])->pluck('value')->all();
        Storage::disk('public')->assertExists($old);
        $this->patch(route('admin.village-profile.update'), ['site_name' => 'Updated'])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($old);
        $this->patch(route('admin.village-profile.update'), ['site_name' => 'Updated', 'logo' => UploadedFile::fake()->image('new.png'), 'sotk' => UploadedFile::fake()->image('new.jpg')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists(Setting::whereIn('key', ['site.logo', 'sotk.image'])->pluck('value')->all());
    }

    public function test_failed_settings_save_rolls_back_rows_and_new_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/logo/old.png', 'old');
        Setting::create(['key' => 'site.logo', 'value' => 'settings/logo/old.png', 'type' => 'string', 'group' => 'site']);
        Setting::saving(function ($setting) {
            if ($setting->key === 'sotk.image') {
                throw new \RuntimeException('Settings failed');
            }
        });
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->patch(route('admin.village-profile.update'), ['site_name' => 'Desa', 'logo' => UploadedFile::fake()->image('new.png'), 'sotk' => UploadedFile::fake()->image('new.jpg')]);
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Settings failed', $e->getMessage());
        } finally {
            Setting::flushEventListeners();
        }
        $this->assertDatabaseCount('settings', 1);
        $this->assertSame(['settings/logo/old.png'], Storage::disk('public')->allFiles());
    }
}
