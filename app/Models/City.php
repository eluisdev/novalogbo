<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'country_id',
    ];



    public $timestamps = true;


    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function originProduct():HasMany
    {
        return $this->hasMany(Product::class, 'origin_id');
    }
    public function destinationProduct():HasMany
    {
        return $this->hasMany(Product::class, 'destination_id');
    }

}
