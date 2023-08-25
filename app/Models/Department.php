<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            $department->code = Department::generateCode();
        });
    }

    /**
     * Generate a code for the department.
     *
     * @return string
     */
    public static function generateCode()
    {
        return 'DP_' . str_pad(Department::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Retrieve the positions associated with the current department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_department');
    }

    /**
     * Retrieve the employees associated with the current department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
