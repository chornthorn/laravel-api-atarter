<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(RoleUserTableSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(PositionSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(ApplicantStatusSeeder::class);
        $this->call(BankAccountSeeder::class);
        $this->call(CustomerBankAccountSeeder::class);
        $this->call(ApplicantSeeder::class);
        $this->call(VendorSeeder::class);
        $this->call(InvoiceSeeder::class);
    }
}
