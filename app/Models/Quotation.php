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
        'customer_nit',
        'users_id',
        'status',
        'observations',
    ];
    protected $casts = [
        'delivery_date' => 'datetime',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_nit', 'NIT');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(QuotationService::class);
    }

    public function costs(): HasMany{
        return $this->hasMany(CostDetail::class);
    }
}
