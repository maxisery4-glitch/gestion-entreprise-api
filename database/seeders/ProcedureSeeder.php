<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcedureSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('procedures')->insert([
            [
                'titre' => 'Sécurité et Incendie',
                'description' => 'Consignes d’évacuation et numéros d’urgence.',
                'type' => 'texte',
                'contenu' => "1. En cas d'alerte, évacuez par la porte la plus proche.\n2. Ne prenez pas les ascenseurs.\n3. Rendez-vous au point de rassemblement sur le parking.",
                'created_at' => now()
            ],
            [
                'titre' => 'Installation d’un poste de travail',
                'description' => 'Procédure pour les nouveaux arrivants (stagiaires).',
                'type' => 'texte',
                'contenu' => "1. Branchez l'unité centrale.\n2. Connectez-vous avec vos identifiants fournis par l'admin.\n3. Configurez votre boîte mail entreprise.",
                'created_at' => now()
            ],
            [
                'titre' => 'Accueil Client au téléphone',
                'description' => 'Standard et politesse.',
                'type' => 'texte',
                'contenu' => "Dites toujours : 'Bonjour, Entreprise GESTION ENTREPRISE, [Votre Prénom] à votre écoute, en quoi puis-je vous aider ?'",
                'created_at' => now()
            ]
        ]);
    }
}