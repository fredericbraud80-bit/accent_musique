<?php
namespace Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    public static function regenerate(): void {
        session_regenerate_id(true);
    }

    public static function setFlash(string $type, string $message): void {
        $_SESSION['_flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string {
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $message;
    }
}