<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_logs', function (Blueprint $table) {
            $table->id();
            // Lie la tâche à l'utilisateur
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Lie à la routine spécifique
            $table->foreignId('routine_id')->constrained()->onDelete('cascade');
            // Enregistre le jour (ex: 2026-04-15)
            $table->date('date_du_jour'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_logs');
    }
};