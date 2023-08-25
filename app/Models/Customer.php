<?php

namespace App\Models;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'vat_number',
        'status',
    ];

    /**
     * Get the status attribute.
     *
     * @param mixed $value The value of the attribute.
     * @return string The status ('Active' or 'Inactive').
     */
    public function getStatusAttribute($value)
    {
        return $value == 1 ? 'Active' : 'Inactive';
    }

    /**
     * Sets the value of the status attribute.
     *
     * @param mixed $value The value to set for the status attribute.
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value == 'Active' ? 1 : 0;
    }

    /**
     * Retrieve the bank accounts associated with the current customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function bankAccounts()
    {
        return $this->belongsToMany(BankAccount::class, 'customer_bank_info', 'customer_id', 'bank_account_id');
    }

    /**
     * Retrieve the invoices associated with the current customer.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

}
