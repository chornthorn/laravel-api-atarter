<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 5 work positions manually
        $salesManager = Position::create([
            'name' => 'Sales Manager',
            'description' => 'Sales Manager',
            'code' => Position::generateCode(),
        ]);

        // IT
        $itManager = Position::create([
            'name' => 'IT Manager',
            'description' => 'IT Manager',
            'code' => Position::generateCode(),
        ]);

        // Backend Developer
        $backendDeveloper = Position::create([
            'name' => 'Backend Developer',
            'description' => 'Backend Developer',
            'code' => Position::generateCode(),
        ]);

        // Frontend Developer
        $frontendDeveloper = Position::create([
            'name' => 'Frontend Developer',
            'description' => 'Frontend Developer',
            'code' => Position::generateCode(),
        ]);
    }
}
