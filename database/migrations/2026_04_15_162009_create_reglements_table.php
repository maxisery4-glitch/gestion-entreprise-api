<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table pour stocker le texte du règlement (un seul texte pour tout le monde)
        Schema::create('reglements', function (Blueprint $table) {
            $table->id();
            $table->text('contenu');
            $table->timestamps();
        });

        // 2. Table pour enregistrer qui a validé et quand
        Schema::create('reglement_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('date_validation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglement_user');
        Schema::dropIfExists('reglements');
    }
};