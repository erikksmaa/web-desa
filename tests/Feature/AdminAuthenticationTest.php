<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_access_dashboard_and_is_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertOk()->assertSee($user->name);

        $this->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_invalid_credentials_fail_without_retaining_password(): void
    {
        $user = User::factory()->create();

        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'incorrect',
        ])->assertRedirect(route('admin.login'))->assertSessionHasErrors('email')
            ->assertSessionHasInput('email', $user->email)
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_login_validates_email_and_password(): void
    {
        $this->post(route('admin.login.store'), ['email' => 'invalid', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_valid_login_authenticates_and_rotates_session(): void
    {
        $user = User::factory()->create(['password' => Hash::make('test-password')]);
        $this->get(route('admin.login'));
        $previousSession = session()->getId();

        $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'test-password',
        ])->assertRedirect(route('admin.dashboard'))->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($previousSession, session()->getId());
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['password' => Hash::make('test-password')]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.store'), [
                'email' => $user->email, 'password' => 'incorrect',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('admin.login.store'), [
            'email' => $user->email, 'password' => 'test-password',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_logout_invalidates_session_and_protected_access(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['private-marker' => 'remove-me'])
            ->get(route('admin.dashboard'));
        $previousSession = session()->getId();
        $previousToken = session()->token();

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'))
            ->assertSessionMissing('private-marker');

        $this->assertGuest();
        $this->assertNotSame($previousSession, session()->getId());
        $this->assertNotSame($previousToken, session()->token());
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_logout_does_not_accept_get_requests(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/logout')->assertStatus(405);
    }
}
