<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'source_currency',
        'target_currency',
        'rate',
        'date',
        'active',
    ];
}
