<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_development_seeder_refuses_production(): void
    {
        $this->app->instance('env', 'production');
        $this->app->make(AdminUserSeeder::class)->run();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_local_seeder_hashes_password_and_does_not_overwrite_existing_account(): void
    {
        $this->app->instance('env', 'local');
        $this->seed(AdminUserSeeder::class);
        $user = User::where('email', 'admin@example.com')->sole();
        $this->assertTrue(Hash::check('password', $user->password));

        $user->update(['password' => Hash::make('changed-password')]);
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Hash::check('changed-password', $user->fresh()->password));
    }
}
