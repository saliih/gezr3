<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'user';
    public $timestamps = false;
    protected $fillable = ['username', 'password', 'roles', 'permissions'];

    protected $hidden = ['password'];

    protected $casts = [
        'roles'       => 'array',
        'permissions' => 'array',
    ];

    /**
     * Droit sur un module/action. Les admins ont tout. Un utilisateur sans réglage
     * enregistré (permissions NULL, anciens comptes) garde l'accès complet d'avant.
     */
    public function canDo(string $module, string $action = 'view'): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $perms = $this->permissions;
        if ($perms === null) {
            return array_key_exists($module, \App\Support\Permissions::modules());
        }

        return in_array($action, $perms[$module] ?? [], true);
    }

    /** Première page autorisée (pour la redirection après connexion). */
    public function firstAllowedRoute(): ?string
    {
        foreach (\App\Support\Permissions::modules() as $module => $def) {
            // L'action d'entrée d'un module est sa première (عرض, ou إضافة pour « تفعيل جديد »)
            if ($this->canDo($module, array_key_first($def['actions']))) {
                return $def['route'];
            }
        }
        return null;
    }

    public function isAdmin(): bool
    {
        $roles = $this->roles ?? [];
        return in_array('ROLE_ADMIN', $roles) || in_array('ROLE_SUPER_ADMIN', $roles);
    }

    public function isSuperAdmin(): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $this->roles ?? []);
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->username;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }
}
