<?php
// src/Middlewares/StudentAccessMiddleware.php
namespace Middlewares;

use Core\Session;

class StudentAccessMiddleware {
    public function handle(): void {
        if (!Session::has('user_id')) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $isAdmin = Session::get('user_role') === 'admin';
        $hasStudentAccess = $isAdmin || Session::get('user_access_student') === true;

        if (!$hasStudentAccess) {
            Session::setFlash('error', 'Vous n\'avez pas accès à l\'espace étudiant.');
            // Rediriger vers l'espace auquel l'utilisateur a droit
            header('Location: ' . BASE_URL . (Session::get('user_access_artist') === true ? '/artiste' : '/'));
            exit;
        }
    }
}
