<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $adminPassword = env('SEED_ADMIN_PASSWORD', Str::password(16));

        User::updateOrCreate(
            [
                'email' => 'admin@simpeg.test',
            ],
            [
                'name' => 'Administrator',
                'password' => Hash::make($adminPassword),
                'role_id' => $adminRole?->id,
                'must_change_password' => true,
            ]
        );
    }
}