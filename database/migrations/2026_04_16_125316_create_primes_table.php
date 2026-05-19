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
        Schema::create('primes', function (Blueprint $table) {
            $table->id();
            // Crée le lien avec l'employé. Si on supprime l'employé, ses primes sont supprimées aussi.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('montant');
            $table->string('periode'); // Exemple: "Avril 2026"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primes');
    }
};