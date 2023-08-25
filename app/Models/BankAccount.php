<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_number',
        'account_name',
        'account_type',
        'status',
    ];

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

    /**
     * Retrieves the customers associated with the bank account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_bank_info', 'bank_account_id', 'customer_id');
    }

    /**
     * Retrieves the vendors associated with the bank account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_bank_info', 'bank_account_id', 'vendor_id');
    }
}
