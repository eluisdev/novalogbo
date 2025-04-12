<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'reference_customer',
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

    public function costDetails(): HasMany{
        return $this->hasMany(CostDetail::class);
    }

    public function billingNote(): HasOne
    {
        return $this->hasOne(BillingNote::class, 'quotation_id');
    }
}
