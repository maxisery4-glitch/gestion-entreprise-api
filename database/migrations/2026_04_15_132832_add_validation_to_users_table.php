<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // On vérifie si la colonne fiche_poste existe déjà avant de l'ajouter
            if (!Schema::hasColumn('users', 'fiche_poste')) {
                $table->text('fiche_poste')->nullable();
            }
            
            // On ajoute les colonnes pour la validation
            $table->boolean('fiche_validee')->default(false);
            $table->timestamp('date_validation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fiche_validee', 'date_validation']);
            // On ne supprime fiche_poste que si on est sûr de vouloir tout retirer
            if (Schema::hasColumn('users', 'fiche_poste')) {
                $table->dropColumn('fiche_poste');
            }
        });
    }
};