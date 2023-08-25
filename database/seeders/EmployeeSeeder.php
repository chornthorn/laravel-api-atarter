<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 2 employees manually
        $employee1 = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'code' => Employee::generateCode(),
            'email' => 'jonhdeo@gmail.com',
            'gender' => 'm',
            'number_of_children' => 1,
            'phone_number' => '081234567890',
            'address' => 'Jl. Raya Kebayoran Lama No. 12, Jakarta Selatan',
            'dob' => '1990-01-01',
            'doj' => '2021-01-01',
            'dol' => null,
            'status' => 1,
            'position_id' => 1,
            'department_id' => 1,
        ]);
    }
}
