<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Stage',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'), // Ton mot de passe sera admin123
            'role' => 'admin',
            'poste' => 'Administrateur Système',
        ]);
    }
}