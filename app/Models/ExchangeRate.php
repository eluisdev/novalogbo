<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;
    protected $fillable = [
        'source_currency',
        'target_currency',
        'rate',
        'date',
        'active',
    ];
}
