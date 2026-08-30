<?php
// src/Middlewares/AdminMiddleware.php
namespace Middlewares;

use Core\Session;

class AdminMiddleware {
    public function handle(): void {
        // Vérifier d'abord l'authentification
        if (!Session::has('user_id')) {
            Session::setFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Vérifier le rôle admin
        if (Session::get('user_role') !== 'admin') {
            http_response_code(403);
            require __DIR__ . '/../../views/errors/403.php';
            exit;
        }
    }
}