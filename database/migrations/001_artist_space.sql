-- Compatible MySQL 8 et MariaDB. Idempotente : peut etre executee plusieurs fois.

-- 1. Colonne google_folder_id sur artist_spaces (conditionnee en SQL portable).
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'artist_spaces' AND COLUMN_NAME = 'google_folder_id');
SET @ddl := IF(@col_exists = 0,
    'ALTER TABLE artist_spaces ADD COLUMN google_folder_id VARCHAR(255) NULL AFTER name',
    'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'artist_spaces' AND INDEX_NAME = 'idx_artist_space_google_folder_id');
SET @ddl := IF(@idx_exists = 0,
    'ALTER TABLE artist_spaces ADD INDEX idx_artist_space_google_folder_id (google_folder_id)',
    'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS google_drive_tokens (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    access_token TEXT NOT NULL,
    refresh_token TEXT NULL,
    expires_at DATETIME NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nettoyage des lignes orphelines. Les doublons de liaison sont deja bloques
-- par la cle primaire (space_id, user_id).
DELETE asu FROM artist_space_users asu
LEFT JOIN artist_spaces s ON s.id = asu.space_id
LEFT JOIN users u ON u.id = asu.user_id
WHERE s.id IS NULL OR u.id IS NULL;

DELETE af FROM artist_folders af
LEFT JOIN artist_spaces s ON s.id = af.space_id
WHERE af.space_id IS NOT NULL AND s.id IS NULL;

CREATE TABLE IF NOT EXISTS artist_folder_users (
    folder_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (folder_id, user_id),
    KEY idx_artist_folder_users_user (user_id),
    CONSTRAINT fk_artist_folder_users_folder FOREIGN KEY (folder_id) REFERENCES artist_folders(id) ON DELETE CASCADE,
    CONSTRAINT fk_artist_folder_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migration des droits par espace vers les droits par dossier.
-- Les liaisons artist_space_users existantes sont conservees (elles donnent
-- acces a la racine) et chaque utilisateur d'un espace recoit aussi les
-- dossiers de cet espace.
INSERT IGNORE INTO artist_folder_users (folder_id, user_id)
SELECT af.id, asu.user_id
FROM artist_folders af
JOIN artist_space_users asu ON asu.space_id = af.space_id;
