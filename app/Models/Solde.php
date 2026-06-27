<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solde extends Model
{
    protected $table = 'solde';

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'client_id', 'vannes_id', 'date_transfert', 'transfert_number',
        'amount', 'old_counter', 'new_counter', 'consume', 'reminder',
        'type', 'coupon_number', 'auto_number', 'plan_date', 'locked',
    ];

    protected $casts = [
        'date_transfert' => 'date',
        'plan_date'      => 'date',
        'locked'         => 'boolean',
        'amount'         => 'float',
        'reminder'       => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function vannes(): BelongsTo
    {
        return $this->belongsTo(Vannes::class, 'vannes_id');
    }
}
