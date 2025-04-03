<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Incoterm extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $cast = [
        'is_active' => 'boolean',
    ];


    public $timestamps = true;

}
