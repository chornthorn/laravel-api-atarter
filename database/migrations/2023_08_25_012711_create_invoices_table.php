<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->string('invoice_number');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->double('exchange_rate')->nullable();
            $table->double('sub_total')->nullable();
            $table->double('tax')->nullable();
            $table->double('total')->nullable();
            $table->double('paid')->nullable();
            $table->double('balance')->nullable();

            $table->tinyInteger('status')->default(1)->comment('1 = Active, 0 = Inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
