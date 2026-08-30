<?php
namespace Core;

class Security {
    public static function generateCsrfToken(): string {
        $token = Session::get('csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }

        return $token;
    }

    public static function verifyCsrfToken(?string $token): bool {
        $sessionToken = Session::get('csrf_token');
        if (!is_string($sessionToken) || !is_string($token) || $sessionToken === '' || $token === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function sanitize(string $data): string {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}