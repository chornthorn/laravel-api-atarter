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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('gender', 1)->nullable()->comment('m = Male, f = Female');
            $table->string('email', 50)->unique();
            $table->string('phone_number', 20)->nullable();
            $table->string('address')->nullable();
            $table->tinyInteger('number_of_children')->nullable();
            $table->string('profile_url')->nullable();
            $table->date('dob')->nullable()->comment('Date of birth');
            $table->date('doj')->nullable()->comment('Date of joining');
            $table->date('dol')->nullable()->comment('Date of leaving');
            $table->tinyInteger('status')->default(1)->comment('0 = Inactive, 1 = Active');

            // Foreign keys
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('department_id');

            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
