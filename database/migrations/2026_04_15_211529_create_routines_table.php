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
    Schema::create('routines', function (Blueprint $table) {
        $table->id();
        $table->string('poste'); // Le nom du poste (ex: 'stagiaire', 'secrétaire')
        $table->string('heure'); // L'heure de la tâche (ex: '08:00')
        $table->string('tache'); // La description (ex: 'Accueil des clients')
        $table->timestamps();
    });
}
};
