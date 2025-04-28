<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingNote extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'op_number',
        'note_number',
        'emission_date',
        'total_amount',
        'currency',
        'exchange_rate',
        'user_id',
        'quotation_id',
        'customer_nit',
        'status',
    ];

    protected $dates = ['emission_date'];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

     public function customer(): BelongsTo
     {
         return $this->belongsTo(Customer::class, 'customer_nit');
     }

    public function items()
    {
        return $this->hasMany(BillingNoteItem::class);
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    // Generar números de nota automáticos
    public static function generateNumbers()
    {
        $year = Carbon::now()->format('y'); // 25 para 2025
        $lastNote = self::whereYear('created_at', Carbon::now()->year)
                        ->orderBy('id', 'desc')
                        ->first();

        $sequence = $lastNote ? (int)explode('-', $lastNote->op_number)[1] + 1 : 1;
        $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return [
            'op_number' => "OP-{$sequenceFormatted}-{$year}",
            'note_number' => "No-{$sequenceFormatted}-{$year}"
        ];
    }
}
