<?php

namespace Database\Seeders;

use App\Models\ApplicantStatus;
use Illuminate\Database\Seeder;

class ApplicantStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 5 applicant statuses
        $pending = ApplicantStatus::create([
            'name' => 'Pending',
            'description' => 'Pending applicant',
        ]);

        $review = ApplicantStatus::create([
            'name' => 'Review',
            'description' => 'Review applicant',
        ]);

        $interview = ApplicantStatus::create([
            'name' => 'Interview',
            'description' => 'Interview applicant',
        ]);

        $pass = ApplicantStatus::create([
            'name' => 'Pass',
            'description' => 'Pass applicant',
        ]);

        $fail = ApplicantStatus::create([
            'name' => 'Fail',
            'description' => 'Fail applicant',
        ]);
    }
}
