<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'origin_id',
        'destination_id',
        'incoterm_id',
        'quantity',
        'quantity_description_id',
        'weight',
        'volume',
        'volume_unit',
        'description',
        'amount'
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
    public function origin(): BelongsTo
    {
        return $this->belongsTo(City::class, 'origin_id');
    }
    public function destination(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_id');
    }
    public function incoterm(): BelongsTo
    {
        return $this->belongsTo(Incoterm::class);
    }
    public function costDetails(): HasMany
    {
        return $this->hasMany(CostDetail::class);
    }
    public function quantityDescription(): BelongsTo
    {
        return $this->belongsTo(QuantityDescription::class);
    }

}
