<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Testing\Fluent\Concerns\Has;

class Incoterm extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $cast = [
        'is_active' => 'boolean',
    ];


    public $timestamps = true;


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
