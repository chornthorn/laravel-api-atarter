<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 2 bank accounts (ABA bank)
        $johnDeo = BankAccount::create([
            'bank_name' => 'ABA Bank',
            'account_number' => '123456789',
            'account_name' => 'John Doe',
        ]);

        $janeDoe = BankAccount::create([
            'bank_name' => 'ABA Bank',
            'account_number' => '987654321',
            'account_name' => 'Jane Doe',
        ]);
    }
}
