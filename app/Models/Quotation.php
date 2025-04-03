<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'delivery_date',
        'amount',
        'currency_origin',
        'currency_destination',
        'exchange_rate',
        'incoterm_id',
        'customers_id',
        'users_id'
    ];
    protected $casts = [
        'delivery_date' => 'datetime',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
    ];
    function incoterm(): BelongsTo
    {
        return $this->belongsTo(Incoterm::class);
    }
    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
