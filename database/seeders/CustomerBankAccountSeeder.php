<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // find customer by their first name
        // $customer1 = Customer::where('first_name', 'John')->first();
        // $customer2 = Customer::where('first_name', 'Jane')->first();

        // // find bank account by their account name
        // $bankAccount1 = BankAccount::where('account_name', 'John Doe')->first();
        // $bankAccount2 = BankAccount::where('account_name', 'Jane Doe')->first();

        // // create bank account relationship between customer and bank account
        // $customer1->bankAccounts()->save($bankAccount1);
        // $customer2->bankAccounts()->save($bankAccount2);
    }
}
