<?php
// src/Middlewares/GuestMiddleware.php
namespace Middlewares;

use Core\Session;

class GuestMiddleware {
    public function handle(): void {
        if (Session::has('user_id')) {
            // Rediriger vers l'espace auquel l'utilisateur a réellement droit
            if (Session::get('user_access_student') === true || Session::get('user_role') === 'admin') {
                header('Location: ' . BASE_URL . '/accueil');
            } elseif (Session::get('user_access_artist') === true) {
                header('Location: ' . BASE_URL . '/artiste');
            } else {
                header('Location: ' . BASE_URL . '/');
            }
            exit;
        }
    }
}