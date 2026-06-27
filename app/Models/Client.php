<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $table = 'client';

    // Symfony stores timestamps as datetime_immutable without TZ
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name', 'gtree', 'mtree', 'otree', 'ptree',
        'cin', 'num_dossier', 'comments', 'tel', 'old_solde',
    ];

    protected $casts = [
        'old_solde' => 'float',
    ];

    public function vannes(): BelongsToMany
    {
        return $this->belongsToMany(Vannes::class, 'client_vannes', 'client_id', 'vannes_id');
    }

    public function soldes(): HasMany
    {
        return $this->hasMany(Solde::class, 'client_id');
    }

    public function activations(): HasMany
    {
        return $this->hasMany(ClientActivation::class, 'client_id');
    }

    public function isActiveForYear(int $year): bool
    {
        return $this->activations()->where('year', $year)->exists();
    }

    public function __toString(): string
    {
        $parts = [];
        if ($this->id) {
            $parts[] = "#{$this->id}";
        }
        if ($this->num_dossier) {
            $parts[] = "({$this->num_dossier})";
        }
        $parts[] = $this->name;
        return implode(' ', $parts);
    }
}
