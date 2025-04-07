<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_detail_id',
        'cost_id',
        'amount',
        'currency',
        'concept'
    ];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class, 'quotation_detail_id');
    }
    public function cost():BelongsTo
    {
        return $this->belongsTo(Cost::class);
    }
}
