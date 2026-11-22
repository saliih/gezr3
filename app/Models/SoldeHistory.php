<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Journal d'audit de la table `solde` : une ligne par création / modification / suppression.
 * Écrit uniquement par Solde::booted(), consultable par le super admin.
 */
class SoldeHistory extends Model
{
    protected $table = 'solde_history';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
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
