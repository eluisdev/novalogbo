<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingNoteItem extends Model
{
    protected $fillable = [
        'billing_note_id',
        'cost_id',
        'description',
        'amount',
        'currency'
    ];

    public function billingNote(): BelongsTo
    {
        return $this->belongsTo(BillingNote::class);
    }

    public function cost(): BelongsTo
    {
        return $this->belongsTo(Cost::class);
    }
}
