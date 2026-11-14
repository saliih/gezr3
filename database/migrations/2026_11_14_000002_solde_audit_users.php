<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// Idempotente : utilisateur créateur / dernier modificateur d'une ligne de solde.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solde', function ($table) {
            if (!Schema::hasColumn('solde', 'created_by')) {
                $table->integer('created_by')->nullable()->after('locked');
            }
            if (!Schema::hasColumn('solde', 'updated_by')) {
                $table->integer('updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solde', function ($table) {
            foreach (['created_by', 'updated_by'] as $col) {
                if (Schema::hasColumn('solde', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
