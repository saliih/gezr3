<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Idempotente : journal d'audit de la table `solde`.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('solde_history')) {
            return;
        }

        Schema::create('solde_history', function (Blueprint $table) {
            $table->id();
            $table->integer('solde_id')->index();
            $table->string('action', 10);                // created | updated | deleted
            $table->integer('user_id')->nullable()->index();
            $table->string('username', 180)->nullable(); // copie : reste lisible si l'utilisateur est supprimé
            $table->integer('client_id')->nullable()->index();
            $table->integer('vannes_id')->nullable();
            $table->string('type', 10)->nullable();
            $table->json('changes')->nullable();
            $table->dateTime('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solde_history');
    }
};
