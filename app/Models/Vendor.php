<?php

namespace App\Models;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'website',
        'notes',
        'vat_number',
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

    public function bankAccounts()
    {
        return $this->belongsToMany(BankAccount::class, 'vendor_bank_info', 'vendor_id', 'bank_account_id');
    }
}
