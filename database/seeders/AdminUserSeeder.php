<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('matcha.admin.email');
        $password = config('matcha.admin.password');

        if (! $email || ! $password) {
            throw new RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before running the seeder.');
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = config('matcha.admin.name');
        $admin->password = $password; // hashed automatically by the model cast
        $admin->is_admin = true;
        $admin->email_verified_at = now();
        $admin->save();
    }
}
