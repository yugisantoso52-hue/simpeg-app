<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::updateOrCreate(
            [
                'email' => 'admin@simpeg.test',
            ],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin12345'),
                'role_id' => $adminRole?->id,
            ]
        );
    }
}