<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
            ]
        );

        Role::updateOrCreate(
            ['name' => 'pimpinan'],
            [
                'display_name' => 'Pimpinan',
            ]
        );

        Role::updateOrCreate(
            ['name' => 'pegawai'],
            [
                'display_name' => 'Pegawai',
            ]
        );
    }
}