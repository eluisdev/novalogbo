<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'user_id',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function auditable()
    {
        return $this->morphTo();
    }


    public function getOldValuesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function getNewValuesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
}
