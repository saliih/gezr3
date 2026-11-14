<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Idempotente : une seule date métier sur `solde` (plan_date). created_at reste en base.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('solde', 'date_transfert')) {
            return;
        }

        DB::statement('UPDATE `solde` SET `plan_date` = COALESCE(`plan_date`, `date_transfert`, DATE(`created_at`))');

        Schema::table('solde', function ($table) {
            $table->dropColumn('date_transfert');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('solde', 'date_transfert')) {
            Schema::table('solde', function ($table) {
                $table->date('date_transfert')->nullable();
            });
            DB::statement('UPDATE `solde` SET `date_transfert` = `plan_date`');
        }
    }
};
