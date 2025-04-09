<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'NIT';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'NIT',
        'name',
        'email',
        'phone',
        'cellphone',
        'address',
        'department',
        'active',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'customer_nit', 'NIT');
    }

     // Relación con BillingNotes
     public function billingNotes(): HasMany
     {
         return $this->hasMany(BillingNote::class, 'customer_nit', 'NIT');
     }
}
