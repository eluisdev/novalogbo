<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Continent extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = ['name', 'code'];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public $timestamps = true;
}
