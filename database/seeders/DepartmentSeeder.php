<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 departments manually
        $sales = Department::create([
            'name' => 'Sales',
            'description' => 'Sales department',
            'code' => Department::generateCode(),
        ]);

        $marketing = Department::create([
            'name' => 'Marketing',
            'description' => 'Marketing department',
            'code' => Department::generateCode(),
        ]);

        $finance = Department::create([
            'name' => 'Finance',
            'description' => 'Finance department',
            'code' => Department::generateCode(),
        ]);

        $hr = Department::create([
            'name' => 'Human Resources',
            'description' => 'Human Resources department',
            'code' => Department::generateCode(),
        ]);

        $it = Department::create([
            'name' => 'Information Technology',
            'description' => 'Information Technology department',
            'code' => Department::generateCode(),
        ]);

        $operations = Department::create([
            'name' => 'Operations',
            'description' => 'Operations department',
            'code' => Department::generateCode(),
        ]);

    }
}
