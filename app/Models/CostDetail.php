<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'cost_id',
        'amount',
        'currency',
        'concept',
        'amount_parallel'
    ];

    public function quotation():BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_detail_id');
    }
    public function cost():BelongsTo
    {
        return $this->belongsTo(Cost::class);
    }
}
