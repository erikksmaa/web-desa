<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use App\Support\IndonesianDate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_remains_protected(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_empty_dashboard_renders_all_summary_and_empty_sections(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSeeText('Berita')
            ->assertSeeText('Pengumuman')
            ->assertSeeText('Agenda')
            ->assertSeeText('Berkas')
            ->assertSeeText('Album Galeri')
            ->assertSeeText('Perangkat Desa')
            ->assertSeeText('Belum ada agenda mendatang')
            ->assertSeeText('Belum ada berita')
            ->assertSeeText('Belum ada pengumuman')
            ->assertSee('data-admin-delete-form', false)
            ->assertDontSee('id="confirmation-modal"', false);
    }

    public function test_dashboard_shows_counts_and_only_upcoming_published_agendas(): void
    {
        $this->freezeTime(function (): void {
            $category = NewsCategory::factory()->create();
            News::factory()->create([
                'news_category_id' => $category->id,
                'status' => News::STATUS_PUBLISHED,
                'published_at' => now()->subDay(),
            ]);
            News::factory()->create([
                'news_category_id' => $category->id,
                'status' => News::STATUS_DRAFT,
            ]);
            $documentCategory = DocumentCategory::factory()->create();
            Document::factory()->create([
                'document_category_id' => $documentCategory->id,
                'status' => Document::STATUS_DRAFT,
            ]);
            GalleryAlbum::factory()->create(['status' => GalleryAlbum::STATUS_PUBLISHED]);
            Announcement::factory()->create([
                'title' => 'Pengumuman dashboard',
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);

            Agenda::factory()->create([
                'title' => 'Agenda yang tampil',
                'status' => Agenda::STATUS_PUBLISHED,
                'start_at' => now()->addDay()->setTime(9, 0),
            ]);
            Agenda::factory()->create([
                'title' => 'Agenda lampau',
                'status' => Agenda::STATUS_PUBLISHED,
                'start_at' => now()->subDay(),
            ]);
            Agenda::factory()->create([
                'title' => 'Agenda draf mendatang',
                'status' => Agenda::STATUS_DRAFT,
                'start_at' => now()->addDay(),
            ]);

            $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

            $response->assertOk()
                ->assertSeeText('Agenda yang tampil')
                ->assertDontSeeText('Agenda lampau')
                ->assertDontSeeText('Agenda draf mendatang')
                ->assertSeeText(IndonesianDate::format(now()->addDay()->setTime(9, 0), true))
                ->assertSeeText('Pengumuman dashboard');
        });
    }

    public function test_sidebar_represents_future_modules_without_dead_links(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSeeTextInOrder(['Utama', 'Dashboard', 'Konten', 'Berita', 'Pengumuman', 'Informasi', 'Agenda', 'Berkas', 'Galeri', 'Profil Desa', 'Informasi Desa', 'Perangkat Desa', 'Tampilan', 'Banner', 'Sistem', 'Pengguna'])
            ->assertDontSee('href="#"', false)
            ->assertSee('aria-disabled="true"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_layout_renders_all_flash_message_variants_and_post_logout(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->withSession([
                'success' => 'Berhasil disimpan.',
                'error' => 'Terjadi kesalahan.',
                'warning' => 'Periksa data.',
                'info' => 'Informasi tersedia.',
            ])
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSeeText('Berhasil disimpan.')
            ->assertSeeText('Terjadi kesalahan.')
            ->assertSeeText('Periksa data.')
            ->assertSeeText('Informasi tersedia.')
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('admin.logout').'"', false);
    }

    public function test_status_badge_maps_known_and_unknown_values(): void
    {
        $cases = [
            'draft' => ['Draf', 'text-bg-secondary'],
            'published' => ['Terbit', 'text-bg-success'],
            'active' => ['Aktif', 'text-bg-success'],
            'inactive' => ['Nonaktif', 'text-bg-secondary'],
            'expired' => ['Kedaluwarsa', 'text-bg-warning'],
            'review' => ['review', 'text-bg-light'],
        ];

        foreach ($cases as $status => [$label, $class]) {
            $html = Blade::render('<x-admin.status-badge :status="$status" />', compact('status'));
            $this->assertStringContainsString($label, $html);
            $this->assertStringContainsString($class, $html);
        }
    }

    public function test_form_component_renders_old_value_required_marker_and_validation_state(): void
    {
        $errors = new ViewErrorBag;
        $errors->put('default', new MessageBag(['title' => ['Judul wajib diisi.']]));
        view()->share('errors', $errors);
        request()->setLaravelSession(app('session.store'));
        session()->flashInput(['title' => 'Judul lama']);

        $html = Blade::render(
            '<x-admin.form.input name="title" label="Judul" help="Maksimal 255 karakter." required />',
        );

        $this->assertStringContainsString('value="Judul lama"', $html);
        $this->assertStringContainsString('is-invalid', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('Judul wajib diisi.', $html);
        $this->assertStringContainsString('Maksimal 255 karakter.', $html);
        $this->assertStringContainsString('required-indicator', $html);
    }

    public function test_layout_uses_one_csrf_protected_delete_form_for_sweetalert_confirmation(): void
    {
        $html = $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'data-admin-delete-form'));
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringNotContainsString('confirmation-modal', $html);
    }

    public function test_destructive_button_exposes_safe_data_for_central_confirmation_handler(): void
    {
        $html = Blade::render(
            '<x-admin.confirm-button action="/admin/news/1" item="Berita contoh" />',
        );

        $this->assertStringContainsString('data-delete-action="/admin/news/1"', $html);
        $this->assertStringContainsString('data-delete-item="Berita contoh"', $html);
        $this->assertStringContainsString('data-delete-message=', $html);
        $this->assertStringContainsString('aria-label="Hapus Berita contoh"', $html);
        $this->assertStringNotContainsString('onclick=', $html);
    }

    public function test_bootstrap_five_is_the_default_pagination_view(): void
    {
        $this->assertSame('pagination::bootstrap-5', Paginator::$defaultView);
        $this->assertSame('pagination::simple-bootstrap-5', Paginator::$defaultSimpleView);
    }

    public function test_indonesian_date_formatting_handles_date_time_and_null(): void
    {
        $date = Carbon::create(2026, 9, 17, 9, 5, 0, 'Asia/Jakarta');

        $this->assertSame('17 September 2026', IndonesianDate::format($date));
        $this->assertSame('17 September 2026, 09:05', IndonesianDate::format($date, true));
        $this->assertSame('—', IndonesianDate::format(null));
    }
}
