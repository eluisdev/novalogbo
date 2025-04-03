<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationService extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'service_id',
        'included',
    ];

    public function quotation():BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function service():BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
