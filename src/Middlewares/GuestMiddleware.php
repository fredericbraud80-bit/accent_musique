<?php
// src/Middlewares/GuestMiddleware.php
namespace Middlewares;

use Core\Session;

class GuestMiddleware {
    public function handle(): void {
        if (Session::has('user_id')) {
            header('Location: ' . BASE_URL . '/courses');
            exit;
        }
    }
}