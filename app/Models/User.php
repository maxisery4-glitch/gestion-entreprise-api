<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Liste des colonnes que Laravel a le droit d'écrire
    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'poste', 
        'fiche_poste', 
        'fiche_validee', 
        'date_validation',
        'solde_conges',
        'photo',
        'piece_identite',
        'contrat_travail',
        'signature_fiche'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fiche_validee' => 'boolean',
            'date_validation' => 'datetime',
            'solde_conges' => 'integer',
        ];
    }
}