<?php

namespace App\Models;

use App\Models\ApplicantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'notes',
        'resume_url',
    ];

    public function applicantStatuses()
    {
        return $this->belongsToMany(ApplicantStatus::class, 'applicant_transactions')->withTimestamps();
    }

}
