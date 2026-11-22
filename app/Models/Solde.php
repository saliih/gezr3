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
        'client_id', 'vannes_id', 'transfert_number',
        'amount', 'old_counter', 'new_counter', 'consume', 'reminder',
        'type', 'coupon_number', 'auto_number', 'plan_date', 'locked',
    ];

    /** Les colonnes d'audit n'existent qu'après la migration : on ne les écrit que si elles sont présentes. */
    private static function hasAuditColumns(): bool
    {
        static $has = null;
        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('solde', 'created_by');
    }

    protected static function booted(): void
    {
        // Traçabilité : utilisateur qui a créé / modifié la ligne (auth()->id() renvoie le username ici)
        static::creating(function (Solde $s) {
            if (!self::hasAuditColumns()) return;
            $uid = auth()->user()?->id;
            $s->created_by = $s->created_by ?? $uid;
            $s->updated_by = $uid;
        });
        static::updating(function (Solde $s) {
            if (!self::hasAuditColumns()) return;
            $s->updated_by = auth()->user()?->id ?? $s->updated_by;
        });

        // Historique complet (visible uniquement par le super admin)
        static::created(function (Solde $s) {
            self::logHistory($s, 'created', self::historyAttributes($s->getAttributes()));
        });
        static::updated(function (Solde $s) {
            $diff = [];
            foreach (self::historyAttributes($s->getChanges()) as $field => $new) {
                $diff[$field] = [$s->getRawOriginal($field), $new];
            }
            if ($diff) {
                self::logHistory($s, 'updated', $diff);
            }
        });
        static::deleted(function (Solde $s) {
            self::logHistory($s, 'deleted', self::historyAttributes($s->getAttributes()));
        });
    }

    private static function hasHistoryTable(): bool
    {
        static $has = null;
        return $has ??= \Illuminate\Support\Facades\Schema::hasTable('solde_history');
    }

    /** Journalise une opération (qui, quand, quoi) dans solde_history. */
    private static function logHistory(Solde $s, string $action, array $changes): void
    {
        if (!self::hasHistoryTable()) return;

        $user = auth()->user();
        SoldeHistory::create([
            'solde_id'   => $s->id,
            'action'     => $action,
            'user_id'    => $user?->id,
            'username'   => $user?->username,
            'client_id'  => $s->client_id,
            'vannes_id'  => $s->vannes_id,
            'type'       => $s->type,
            'changes'    => $changes,
            'created_at' => now(),
        ]);
    }

    /** Champs techniques exclus du journal */
    private const HISTORY_IGNORED = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'];

    private static function historyAttributes(array $attrs): array
    {
        return array_diff_key($attrs, array_flip(self::HISTORY_IGNORED));
    }

    // Une seule date métier : plan_date (date de l'opération / du relevé).
    // created_at reste en base pour l'audit mais n'est affichée nulle part.
    protected $casts = [
        'plan_date' => 'date',
        'locked'    => 'boolean',
        'amount'    => 'float',
        'reminder'  => 'float',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function vannes(): BelongsTo
    {
        return $this->belongsTo(Vannes::class, 'vannes_id');
    }
}
