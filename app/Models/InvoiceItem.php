<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
        'currency',
        'exchange_rate',
        'is_amount_parallel',
        'amount_parallel',
        'type',
        'amount',

    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate tax amount based on subtotal and tax rate
     */
    public function calculateTaxAmount()
    {
        return $this->subtotal * ($this->tax_rate / 100);
    }

    /**
     * Calculate total including tax
     */
    public function calculateTotal()
    {
        return $this->subtotal + $this->tax_amount;
    }
}
