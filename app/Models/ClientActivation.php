<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientActivation extends Model
{
    protected $table = 'client_activation';
    public $timestamps = false;

    protected $fillable = ['client_id', 'year'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
