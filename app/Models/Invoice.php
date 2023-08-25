<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable =
        [
        'customer_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'description',
        'exchange_rate',
        'sub_total',
        'tax',
        'total',
        'paid',
        'balance',
        'status',
    ];

    // static generate invoice number automatically when creating an invoice with format: INV2023-0001
    public static function generateInvoiceNumber($id): string
    {
        return 'INV' . date('Y') . '-' . sprintf('%04d', 1); // format: (INV2023-0002, INV2023-0003, ...)
        // return 'INV' . date('Y') . '-' . sprintf('%04d', $id);
    }

    // check if the invoice number is not provide when creating an invoice, then generate it automatically boot
    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->invoice_number = self::generateInvoiceNumber($invoice->id);
        });
    }

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
     * Retrieve the customer associated with the current invoice.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // invoice items
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

}
