<?php
// src/Middlewares/AuthMiddleware.php
namespace Middlewares;

use Core\Session;

class AuthMiddleware {
    public function handle(): void {
        if (!Session::has('user_id')) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}