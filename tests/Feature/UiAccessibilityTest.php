<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use App\Models\VillageOfficial;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UiAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function publicRoutes(): array
    {
        return ['home', 'profile.index', 'profile.organization', 'profile.officials', 'news.index', 'announcements.index', 'agendas.index', 'documents.index', 'gallery.index'];
    }

    public function test_empty_public_pages_have_one_h1_and_working_navigation_targets(): void
    {
        foreach ($this->publicRoutes() as $route) {
            $html = $this->get(route($route))->assertOk()->getContent();
            $dom = $this->dom($html);
            $this->assertSame(1, $dom->query('//h1')->length, $route);
            $this->assertSame(1, $dom->query('//main[@id="main-content"]')->length);
            foreach ($dom->query('//nav//a[@href] | //a[starts-with(@href,"#")]') as $link) {
                $href = $link->getAttribute('href');
                $this->assertNotSame('#', $href);
                if (str_starts_with($href, '#')) {
                    $this->assertSame(1, $dom->query('//*[@id="'.substr($href, 1).'"]')->length);
                } else {
                    $this->assertContains($href, array_map(fn ($name) => route($name), $this->publicRoutes()));
                }
            }
        }
    }

    public function test_detail_pages_retain_headings_and_long_content_without_unsafe_html(): void
    {
        $title = str_repeat('Judul panjang ', 15);
        $news = News::factory()->create(['title' => $title, 'status' => 'published', 'published_at' => now(), 'content' => "<script>unsafe()</script>\nBaris berikutnya"]);
        $announcement = Announcement::factory()->create(['title' => $title, 'status' => 'published', 'published_at' => now(), 'expires_at' => null]);
        $agenda = Agenda::factory()->create(['title' => $title, 'status' => 'published']);
        $album = GalleryAlbum::factory()->create(['title' => $title, 'status' => 'published']);
        foreach (['news.show' => $news, 'announcements.show' => $announcement, 'agendas.show' => $agenda, 'gallery.show' => $album] as $route => $record) {
            $response = $this->get(route($route, $record->slug))->assertOk()->assertSeeText($title)->assertDontSee('<script>unsafe()</script>', false);
            $dom = $this->dom($response->getContent());
            $this->assertSame(1, $dom->query('//h1')->length);
        }
    }

    public function test_missing_images_never_emit_broken_storage_urls(): void
    {
        Storage::fake('public');
        Setting::create(['key' => 'site.logo', 'value' => 'missing/logo.png', 'type' => 'string', 'group' => 'site']);
        Setting::create(['key' => 'sotk.image', 'value' => 'missing/sotk.png', 'type' => 'string', 'group' => 'sotk']);
        $news = News::factory()->create(['thumbnail' => 'missing/news.jpg', 'status' => 'published', 'published_at' => now()]);
        News::factory()->create(['news_category_id' => $news->news_category_id, 'thumbnail' => 'missing/related.jpg', 'status' => 'published', 'published_at' => now()]);
        VillageOfficial::factory()->create(['photo_path' => 'missing/official.jpg', 'is_active' => true, 'is_village_head' => true]);
        Banner::factory()->create(['image_path' => 'missing/banner.jpg', 'is_active' => true]);
        $album = GalleryAlbum::factory()->create(['cover_image' => 'missing/cover.jpg', 'status' => 'published']);
        GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'image_path' => 'missing/photo.jpg']);
        $urls = array_map(fn ($name) => route($name), $this->publicRoutes());
        $urls[] = route('news.show', $news->slug);
        $urls[] = route('gallery.show', $album->slug);
        $this->actingAs(User::factory()->create());
        $urls[] = route('admin.news.index');
        $urls[] = route('admin.news.edit', $news);
        foreach ($urls as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $dom = $this->dom($html);
            foreach ($dom->query('//img[@src]') as $image) {
                $this->assertStringNotContainsString('/missing/', $image->getAttribute('src'));
                $this->assertTrue($image->hasAttribute('alt'));
            }
        }
    }

    public function test_important_admin_forms_have_unique_labels_and_valid_descriptions(): void
    {
        $this->actingAs(User::factory()->create());
        $routes = ['admin.village-profile.edit', 'admin.news.create', 'admin.announcements.create', 'admin.agendas.create', 'admin.documents.create', 'admin.gallery.create', 'admin.village-officials.create', 'admin.banners.create'];
        foreach ($routes as $route) {
            $dom = $this->dom($this->get(route($route))->assertOk()->getContent());
            foreach ($dom->query('//main//input[not(@type="hidden")] | //main//select | //main//textarea') as $field) {
                $id = $field->getAttribute('id');
                $this->assertNotEmpty($id, $route);
                $this->assertSame(1, $dom->query('//*[@id="'.$id.'"]')->length);
                $this->assertSame(1, $dom->query('//label[@for="'.$id.'"]')->length);
                foreach (preg_split('/\s+/', trim($field->getAttribute('aria-describedby')), -1, PREG_SPLIT_NO_EMPTY) as $description) {
                    $this->assertSame(1, $dom->query('//*[@id="'.$description.'"]')->length);
                }
            }
        }
    }

    public function test_tables_remain_keyboard_scrollable_and_destructive_controls_are_named(): void
    {
        $this->actingAs(User::factory()->create());
        VillageOfficial::factory()->create(['name' => 'Perangkat Contoh']);
        Banner::factory()->create(['title' => 'Banner Contoh']);
        foreach (['admin.village-officials.index', 'admin.banners.index'] as $route) {
            $dom = $this->dom($this->get(route($route))->assertOk()->getContent());
            $this->assertSame(1, $dom->query('//div[@role="region" and @tabindex="0" and @aria-label]//table')->length);
            $this->assertSame(1, $dom->query('//table/caption')->length);
            $this->assertSame(0, $dom->query('//thead//th[not(@scope="col")]')->length);
            $this->assertSame(1, $dom->query('//button[@data-delete-action and @aria-label]')->length);
            $this->assertSame(1, $dom->query('//form[@data-admin-delete-form and @method="POST"]')->length);
        }
    }

    public function test_empty_footer_omits_optional_contacts_social_links_and_map(): void
    {
        $dom = $this->dom($this->get(route('home'))->assertOk()->getContent());
        $footer = $dom->query('//footer')->item(0);
        $this->assertNotNull($footer);
        $this->assertSame(0, $dom->query('.//iframe | .//a[starts-with(@href,"mailto:")]', $footer)->length);
        foreach (['Telepon:', 'Email:', 'Facebook', 'Instagram', 'YouTube', 'Kontak Desa'] as $label) {
            $this->assertStringNotContainsString($label, $footer->textContent);
        }
    }
}
