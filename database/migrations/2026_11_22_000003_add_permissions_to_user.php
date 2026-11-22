<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// Idempotente : droits par utilisateur {"module": ["view","create","edit","delete"], ...}.
// NULL = ancien compte non réglé : il garde l'accès complet d'avant (sauf pages admin).
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('user', 'permissions')) {
            Schema::table('user', function ($table) {
                $table->json('permissions')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user', 'permissions')) {
            Schema::table('user', function ($table) {
                $table->dropColumn('permissions');
            });
        }
    }
};
