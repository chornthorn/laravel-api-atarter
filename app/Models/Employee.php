<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'code',
        'gender',
        'phone_number',
        'profile_url',
        'number_of_children',
        'address',
        'dob',
        'doj',
        'dol',
        'status',
        'position_id',
        'department_id',
    ];

    /**
     * Boot the model.
     *
     * This method is called when the model is being booted.
     * It registers a creating event listener that sets the code attribute of the employee model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->code = Employee::generateCode();
        });
    }

    /**
     * Generate a code for the employee.
     *
     * @return string
     */
    public static function generateCode()
    {
        return 'EMP_' . str_pad(Employee::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Retrieve the position associated with the current employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Retrieve the department associated with the current employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // convert status to string for display purpose
    public function getStatusAttribute($value)
    {
        return $value == 1 ? 'Active' : 'Inactive';
    }

    // convert status to integer for storage purpose
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value == 'Active' ? 1 : 0;
    }

    // convert gender to full string for display purpose (m= Male, f= Female)
    public function getGenderAttribute($value)
    {

        if ($value == 'm') {
            return 'Male';
        } elseif ($value == 'f') {
            return 'Female';
        } else {
            return null;
        }

    }

    // translate gender to full string for storage purpose (m= Male, f= Female)
    public function setGenderAttribute($value)
    {
        $this->attributes['gender'] = $value == 'Male' ? 'm' : 'f';
    }

}
