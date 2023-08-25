<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 2 customers manually
        $customer1 = Customer::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'XzWV3@example.com',
            'phone_number' => '1234567890',
            'address' => '123 Main St',
            'vat_number' => '123456789',
            'status' => 1,
        ]);

        $customer2 = Customer::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'janedeo@example.com',
            'phone_number' => '1234567890',
            'address' => '123 Main St',
            'vat_number' => '123456789',
            'status' => 1,
        ]);

        // create 2 bank accounts for the customers
        $customer1->bankAccounts()->createMany([
            [
                'bank_name' => 'ABA Bank',
                'account_number' => '97987966678',
                'account_name' => 'John Doe',
            ],
        ]);

        $customer2->bankAccounts()->createMany([
            [
                'bank_name' => 'ABA Bank',
                'account_number' => '21342342323',
                'account_name' => 'Jane Doe',
            ],
        ]);

    }
}
