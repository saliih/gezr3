<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrixM3 extends Model
{
    protected $table = 'prix_m3';
    public $timestamps = false;

    protected $fillable = ['year', 'price', 'activation_fee'];

    protected $casts = [
        'price'          => 'float',
        'activation_fee' => 'float',
    ];
}
