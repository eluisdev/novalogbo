<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency',
        'exchange_rate',
        'status',
        'due_date',
        'notes',
        'user_id',
        'customer_nit',
        'billing_note_id',
        'quotation_id'
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_nit');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function billingNote(): BelongsTo
    {
        return $this->belongsTo(BillingNote::class);
    }
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public static function generateInvoiceNumber()
    {
        $year = date('y');
        $prefix = 'INV';
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $latestInvoice = self::whereYear('created_at', Carbon::now()->year)
                ->orderBy('invoice_number', 'desc')
                ->first();

            if (!$latestInvoice) {
                $number = 1;
            } else {
                // Extract number from latest invoice number (e.g., INV-001-25)
                $parts = explode('-', $latestInvoice->invoice_number);
                // Asegurarse de que el formato sea el esperado antes de intentar acceder al índice
                if (count($parts) === 3 && $parts[0] === $prefix && is_numeric($parts[1]) && $parts[2] === $year) {
                    $number = (int)$parts[1] + 1;
                } else {
                    // Si el formato no coincide, empezar desde 1 para el año actual
                    $number = 1;
                }
            }

            $invoiceNumber = $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT) . '-' . $year;

            // Verificar si el número de factura ya existe para el año actual
            $isUnique = !self::where('invoice_number', $invoiceNumber)
                ->whereYear('created_at', Carbon::now()->year)
                ->exists();

            $attempts++;

            if ($attempts > $maxAttempts) {
                error_log("Error al generar un número de factura único después de {$maxAttempts} intentos.");
                return null; // O lanzar una excepción
            }
        } while (!$isUnique);

        return $invoiceNumber;
    }
}
