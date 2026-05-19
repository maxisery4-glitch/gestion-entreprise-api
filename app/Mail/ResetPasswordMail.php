<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $name;

    public function __construct($url, $name)
    {
        $this->url = $url;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('Réinitialisation de votre mot de passe')
                    ->html("
                        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
                            <h2 style='color: #512da8;'>Bonjour {$this->name},</h2>
                            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre espace de gestion.</p>
                            <p>Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$this->url}' style='background: #512da8; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Réinitialiser mon mot de passe</a>
                            </div>
                            <p>Ce lien expirera bientôt. Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer ce mail.</p>
                            <hr>
                            <small>L'équipe Gestion Entreprise Pro</small>
                        </div>
                    ");
    }
}