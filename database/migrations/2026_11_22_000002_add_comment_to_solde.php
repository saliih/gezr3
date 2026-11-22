<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// Idempotente : commentaire libre sur une ligne de solde (ex. ce qui s'est passé avec le fellah).
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('solde', 'comment')) {
            Schema::table('solde', function ($table) {
                $table->text('comment')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('solde', 'comment')) {
            Schema::table('solde', function ($table) {
                $table->dropColumn('comment');
            });
        }
    }
};
