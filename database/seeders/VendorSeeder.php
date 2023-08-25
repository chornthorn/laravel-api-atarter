<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 1 vendor
        $vendor = Vendor::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => '9JyDp@example.com',
            'phone_number' => '123456789',
            'address' => '123 Main St',
            'website' => 'https://example.com',
            'notes' => 'Some notes',
            'vat_number' => '123456789',
        ]);

        // create 2 bank accounts for the vendor
        $vendor->bankAccounts()->createMany([
            [
                'bank_name' => 'ABA Bank',
                'account_number' => '123456789',
                'account_name' => 'John Doe',
            ],
            [
                'bank_name' => 'ABA Bank',
                'account_number' => '987654321',
                'account_name' => 'Jane Doe',
            ],
        ]);
    }
}
