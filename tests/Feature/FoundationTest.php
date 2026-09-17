<?php

namespace Tests\Feature;

use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_homepage_is_public_and_uses_indonesian_identity(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('lang="id"', false)
            ->assertSee('Sistem Informasi Desa sedang dalam pengembangan.');
    }

    public function test_login_page_is_available_without_public_account_links(): void
    {
        $this->get(route('admin.login'))->assertOk()
            ->assertSee('Masuk Admin')
            ->assertDontSee('Forgot password')
            ->assertDontSee('Register');
    }

    public function test_public_account_and_future_module_routes_do_not_exist(): void
    {
        foreach (['/register', '/login', '/forgot-password', '/admin/news', '/berita'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }
}
