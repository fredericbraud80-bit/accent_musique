<?php
// src/Middlewares/ArtistAccessMiddleware.php
namespace Middlewares;

use Core\Session;

class ArtistAccessMiddleware
{
    public function handle(): void
    {
        if (!Session::has('user_id')) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $isAdmin = Session::get('user_role') === 'admin';
        $hasArtistAccess = $isAdmin || Session::get('user_access_artist') === true;

        if (!$hasArtistAccess) {
            Session::setFlash('error', 'Vous n\'avez pas accès à l\'espace artiste.');
            header('Location: ' . BASE_URL . (Session::get('user_access_student') === true ? '/accueil' : '/'));
            exit;
        }
    }
}
