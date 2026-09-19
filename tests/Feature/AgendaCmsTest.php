<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaCmsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace(['title' => 'Musyawarah', 'description' => '<script>bad</script>', 'location' => 'Balai Desa', 'status' => 'draft', 'start_at' => now()->addDay()->format('Y-m-d H:i:s'), 'end_at' => null], $overrides);
    }

    public function test_all_admin_routes_require_authentication(): void
    {
        $item = Agenda::factory()->create();
        foreach (['index', 'create', 'edit'] as $action) {
            $this->get(route('admin.agendas.'.$action, $action === 'edit' ? $item : []))->assertRedirect(route('admin.login'));
        }
        $this->post(route('admin.agendas.store'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.agendas.update', $item))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.agendas.destroy', $item))->assertRedirect(route('admin.login'));
    }

    public function test_crud_author_slug_validation_and_soft_delete(): void
    {
        $admin = User::factory()->create();
        Agenda::factory()->create(['slug' => 'musyawarah'])->delete();
        $this->actingAs($admin)->get(route('admin.agendas.create'))->assertOk();
        $this->post(route('admin.agendas.store'), $this->payload(['user_id' => 999, 'slug' => 'tampered']))->assertSessionHasNoErrors()->assertRedirect();
        $item = Agenda::where('slug', 'musyawarah-2')->firstOrFail();
        $this->assertSame($admin->id, $item->user_id);
        $this->get(route('admin.agendas.edit', $item))->assertOk();
        $this->put(route('admin.agendas.update', $item), $this->payload(['end_at' => now()->toDateTimeString()]))->assertSessionHasErrors('end_at');
        $this->post(route('admin.agendas.store'), $this->payload(['start_at' => 'invalid']))->assertSessionHasErrors('start_at');
        $this->actingAs(User::factory()->create())->put(route('admin.agendas.update', $item), $this->payload(['title' => 'Musyawarah Baru', 'status' => 'published']))->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertSame('musyawarah-baru', $item->slug);
        $this->assertSame($admin->id, $item->user_id);
        $this->get(route('agendas.show', $item->slug))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>bad</script>', false);
        $this->delete(route('admin.agendas.destroy', $item))->assertRedirect();
        $this->assertSoftDeleted($item);
        $this->get(route('agendas.show', $item->slug))->assertNotFound();
    }

    public function test_public_upcoming_ongoing_and_past_order_and_visibility(): void
    {
        $this->travelTo(now()->startOfSecond());
        foreach ([
            ['title' => 'Next week', 'start_at' => now()->addWeek(), 'end_at' => null],
            ['title' => 'Tomorrow', 'start_at' => now()->addDay(), 'end_at' => null],
            ['title' => 'Ongoing', 'start_at' => now()->subHour(), 'end_at' => now()->addHour()],
            ['title' => 'Last week', 'start_at' => now()->subWeek(), 'end_at' => null],
            ['title' => 'Yesterday', 'start_at' => now()->subDay(), 'end_at' => null],
        ] as $data) {
            Agenda::factory()->create([...$data, 'status' => 'published']);
        }
        $draft = Agenda::factory()->create(['title' => 'Hidden draft', 'status' => 'draft']);
        $deleted = Agenda::factory()->create(['title' => 'Hidden deleted', 'status' => 'published']);
        $deleted->delete();
        $this->get(route('agendas.index'))->assertOk()->assertSeeTextInOrder(['Ongoing', 'Tomorrow', 'Next week', 'Yesterday', 'Last week'])
            ->assertDontSeeText('Hidden draft')->assertDontSeeText('Hidden deleted');
        $this->get(route('agendas.show', $draft->slug))->assertNotFound();
        $this->get(route('agendas.show', $deleted->slug))->assertNotFound();
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_search_location_status_and_pagination(): void
    {
        $this->actingAs(User::factory()->create());
        Agenda::factory()->count(16)->create(['title' => 'Kegiatan warga', 'location' => 'Balai', 'status' => 'draft']);
        Agenda::factory()->create(['title' => 'Excluded agenda', 'location' => 'Balai', 'status' => 'published']);
        $this->get(route('admin.agendas.index', ['search' => 'Balai', 'status' => 'draft']))->assertOk()
            ->assertSeeText('Kegiatan warga')->assertDontSeeText('Excluded agenda')->assertSee('search=Balai', false)->assertSee('status=draft',false)->assertSee('page=2',false);
    }
}
