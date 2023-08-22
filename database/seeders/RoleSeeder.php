<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Role::where('name', 'admin')->first()) {
            Role::create([
                'name' => 'admin',
                'description' => 'Administrator',
            ]);
        }

        if (!Role::where('name', 'user')->first()) {
            Role::create([
                'name' => 'user',
                'description' => 'User',
            ]);
        }

        if (!Role::where('name', 'guest')->first()) {
            Role::create([
                'name' => 'guest',
                'description' => 'Guest',
            ]);
        }

        if (!Role::where('name', 'super_admin')->first()) {
            Role::create([
                'name' => 'super_admin',
                'description' => 'Super Administrator',
            ]);
        }
    }
}
