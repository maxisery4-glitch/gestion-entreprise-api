<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Routine; // Assure-toi que le modèle existe

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        Routine::create([
            'poste' => 'stagiare', 
            'heure' => '08:00',
            'tache' => 'Arrivée et émargement du registre de présence'
        ]);

        Routine::create([
            'poste' => 'stagiare',
            'heure' => '09:00',
            'tache' => 'Réunion matinale avec le tuteur de stage'
        ]);

        Routine::create([
            'poste' => 'stagiare',
            'heure' => '14:00',
            'tache' => 'Rapport d’activité journalier'
        ]);
    }
}