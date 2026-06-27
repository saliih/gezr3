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
    protected $fillable = ['username', 'password', 'roles'];

    protected $hidden = ['password'];

    protected $casts = [
        'roles' => 'array',
    ];

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
