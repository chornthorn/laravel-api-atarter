<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $applicant1 = Applicant::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'r5gCt@example.com',
            'phone_number' => '123456789',
            'notes' => 'Notes',
            'resume_url' => null,
        ]);

        // find applicant status by name
        $applicantPending = ApplicantStatus::where('name', 'Pending')->first();
        $applicantReview = ApplicantStatus::where('name', 'Review')->first();

        $applicant1->applicantStatuses()->attach($applicantPending);
        $applicant1->applicantStatuses()->attach($applicantReview);
    }
}
