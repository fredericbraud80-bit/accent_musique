<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);

        if ($key !== '') {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

define('APP_NAME', $_ENV['APP_NAME'] ?? 'Accent-Musique');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'dev');

$defaultHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '127.0.0.1';
$defaultScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', $_ENV['BASE_URL'] ?? $defaultScheme . '://' . $defaultHost);
define('STORAGE_PATH', $_ENV['STORAGE_PATH'] ?? __DIR__ . '/../storage/files/');

define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'accent_musique');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Configuration E-mail / SMTP
define('MAIL_DRIVER', $_ENV['MAIL_DRIVER'] ?? 'smtp');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? '127.0.0.1');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 1025));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? '');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'contact@accent-musique.fr');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? APP_NAME);
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@accent-musique.fr');