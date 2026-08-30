<?php
namespace Models;

use Core\Model;
use PDO;

class User extends Model {
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, fullname, email, role, is_validated, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $fullname, string $email, string $password, string $role = 'student'): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (fullname, email, password_hash, role, is_validated, created_at)
            VALUES (:fullname, :email, :hash, :role, 0, NOW())
        ");
        return $stmt->execute([
            'fullname' => $fullname,
            'email'    => $email,
            'hash'     => $hash,
            'role'     => $role
        ]);
    }

    private static bool $passwordResetsTableChecked = false;

    private function ensurePasswordResetsTable(): void {
        if (self::$passwordResetsTableChecked) {
            return;
        }

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_token_hash (token_hash),
                KEY idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        self::$passwordResetsTableChecked = true;
    }

    public function createPasswordReset(string $email, string $tokenHash, int $expiryMinutes = 60): bool {
        $this->ensurePasswordResetsTable();

        // Supprimer les anciens jetons de cet email
        $deleteStmt = $this->db->prepare("DELETE FROM password_resets WHERE email = :email");
        $deleteStmt->execute(['email' => $email]);

        // Insérer le nouveau jeton hashé avec date d'expiration
        $stmt = $this->db->prepare("
            INSERT INTO password_resets (email, token_hash, expires_at, created_at)
            VALUES (:email, :token_hash, DATE_ADD(NOW(), INTERVAL :expiry_minutes MINUTE), NOW())
        ");

        return $stmt->execute([
            'email'          => $email,
            'token_hash'     => $tokenHash,
            'expiry_minutes' => $expiryMinutes
        ]);
    }

    public function findValidPasswordReset(string $tokenHash): ?array {
        $this->ensurePasswordResetsTable();

        $stmt = $this->db->prepare("
            SELECT id, email, token_hash, expires_at
            FROM password_resets
            WHERE token_hash = :token_hash AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute(['token_hash' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function deletePasswordResetsForEmail(string $email): bool {
        $this->ensurePasswordResetsTable();

        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = :email");
        return $stmt->execute(['email' => $email]);
    }

    public function updatePassword(int $userId, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            UPDATE users
            SET password_hash = :hash
            WHERE id = :id
        ");
        return $stmt->execute([
            'hash' => $hash,
            'id'   => $userId
        ]);
    }
    public function deactivateExpiredLicenses(): int {
        return (int) $this->db->exec("
            UPDATE users
            SET is_license_active = 0
            WHERE license_expires_at IS NOT NULL
            AND license_expires_at < NOW()
            AND is_license_active != 0
        ");
    }
}
