<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // invoice 1
        $invoice1 = Invoice::factory()->create([
            'customer_id' => 1,
            'issue_date' => '2023-08-25',
            'due_date' => '2023-08-25',
            'description' => 'Invoice 1',
            'exchange_rate' => 1,
            'sub_total' => 100,
            'tax' => 10,
            'total' => 110,
            'paid' => 0,
            'balance' => 110,
        ]);

        // invoice 2
        $invoice2 = Invoice::factory()->create([
            'customer_id' => 2,
            'issue_date' => '2023-08-25',
            'due_date' => '2023-08-25',
            'description' => 'Invoice 2',
            'exchange_rate' => 1,
            'sub_total' => 200,
            'tax' => 20,
            'total' => 220,
            'paid' => 0,
            'balance' => 220,
        ]);
    }
}
