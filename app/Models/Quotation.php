<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'currency',
        'delivery_date',
        'amount',
        'exchange_rate',
        'incoterm_id',
        'customers_id',
        'users_id',
        'status',

    ];
    protected $casts = [
        'delivery_date' => 'datetime',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'NIT');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function details(): HasMany
    {
        return $this->hasMany(QuotationDetail::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(QuotationService::class);
    }
}
