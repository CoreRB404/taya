<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL and ADMIN_PASSWORD are not set; no admin account was created.');

            return;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || strlen($password) < 12
            || ! preg_match('/[a-z]/', $password)
            || ! preg_match('/[A-Z]/', $password)
            || ! preg_match('/[0-9]/', $password)
            || ! preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new \RuntimeException('ADMIN_EMAIL must be valid and ADMIN_PASSWORD must have 12+ characters with upper/lowercase letters, a number, and a symbol.');
        }

        User::firstOrCreate(
            ['email' => mb_strtolower($email)],
            [
                'name' => env('ADMIN_NAME', 'System Admin'),
                'password' => Hash::make($password),
                'role' => 'admin',
                'facility_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
