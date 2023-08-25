<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Boot the model.
     *
     * This method is called when the model is being booted.
     * It registers a creating event listener that sets the code attribute of the position model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($position) {
            $position->code = Position::generateCode();
        });
    }

    /**
     * Generate a code for the position.
     *
     * @return string
     */
    public static function generateCode()
    {
        return 'PO_' . str_pad(Position::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Retrieves the departments associated with this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'position_department');
    }

    /**
     * Retrieves the employees associated with this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

}
