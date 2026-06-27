<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Params extends Model
{
    protected $table = 'params';
    public $timestamps = false;

    protected $fillable = ['unit_price', 'valve_per_day'];

    protected $casts = [
        'unit_price' => 'float',
    ];
}
