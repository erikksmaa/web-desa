<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development admin seeding is restricted to APP_ENV=local.');

            return;
        }

        // Public demonstration credentials only. Never use this account in production.
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator Lokal', 'password' => Hash::make('password')],
        );

        $this->command?->warn('Development account only. Replace development credentials before production. Existing accounts were not overwritten.');
    }
}
