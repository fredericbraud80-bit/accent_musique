<?php

namespace Controllers;

use Core\Controller;

class LegalController extends Controller
{
    /**
     * Afficher la page des Mentions Légales (brouillon)
     */
    public function mentionsLegales(): void
    {
        $this->render('legal/mentions-legales', [
            'pageTitle' => 'Mentions Légales',
        ]);
    }

    /**
     * Afficher la page des Conditions Générales d'Utilisation (brouillon)
     */
    public function cgu(): void
    {
        $this->render('legal/cgu', [
            'pageTitle' => 'Conditions Générales d\'Utilisation (CGU)',
        ]);
    }
}
