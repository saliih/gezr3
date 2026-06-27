<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vannes extends Model
{
    protected $table = 'vannes';
    public $timestamps = false;

    protected $fillable = ['reference', 'enable', 'last_value', 'link', 'consumed'];

    protected $casts = [
        'enable'   => 'boolean',
        'consumed' => 'boolean',
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_vannes', 'vannes_id', 'client_id');
    }

    public function soldes(): HasMany
    {
        return $this->hasMany(Solde::class, 'vannes_id');
    }

    public function __toString(): string
    {
        return $this->reference . ($this->link ? '.' . $this->link : '');
    }
}
