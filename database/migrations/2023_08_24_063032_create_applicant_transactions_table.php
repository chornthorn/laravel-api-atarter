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
        Schema::create('applicant_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('applicant_status_id');
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
            $table->foreign('applicant_status_id')->references('id')->on('applicant_statuses')->onDelete('cascade');

            $table->primary(['applicant_id', 'applicant_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_transactions');
    }
};
