-- ============================================================
-- 000_baseline_artist_schema.sql
-- Crée le schema "espace artiste" absent de la base de production.
-- A executer AVANT 001_artist_space.sql.
-- Compatible MySQL 8 / MariaDB. Idempotente.
-- ============================================================

-- 1. Colonnes access_student / access_artist sur users
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'access_student');
SET @ddl := IF(@col = 0,
    'ALTER TABLE users ADD COLUMN access_student TINYINT(1) NOT NULL DEFAULT 1 AFTER is_license_active',
    'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'access_artist');
SET @ddl := IF(@col = 0,
    'ALTER TABLE users ADD COLUMN access_artist TINYINT(1) NOT NULL DEFAULT 0 AFTER access_student',
    'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Espaces artistes
CREATE TABLE IF NOT EXISTS artist_spaces (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    google_folder_id VARCHAR(255) DEFAULT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_artist_space_google_folder_id (google_folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Liaison espace <-> utilisateur
CREATE TABLE IF NOT EXISTS artist_space_users (
    space_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (space_id, user_id),
    KEY idx_artist_space_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Dossiers artistes
CREATE TABLE IF NOT EXISTS artist_folders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    space_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    parent_id INT UNSIGNED DEFAULT NULL,
    artist_id INT UNSIGNED DEFAULT NULL,
    artist_email VARCHAR(255) DEFAULT NULL,
    google_folder_id VARCHAR(255) DEFAULT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_artist_folder_parent (parent_id),
    KEY idx_artist_folder_google_id (google_folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Fichiers / pistes artistes
CREATE TABLE IF NOT EXISTS artist_tracks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    space_id INT UNSIGNED DEFAULT NULL,
    folder_id INT UNSIGNED DEFAULT NULL,
    artist_id INT UNSIGNED DEFAULT NULL,
    artist_email VARCHAR(255) DEFAULT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) DEFAULT NULL,
    mime_type VARCHAR(120) NOT NULL,
    size BIGINT UNSIGNED NOT NULL DEFAULT 0,
    google_file_id VARCHAR(255) DEFAULT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_artist_track_folder (folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Notes par dossier
CREATE TABLE IF NOT EXISTS artist_folder_notes (
    folder_id INT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (folder_id),
    CONSTRAINT fk_artist_folder_notes_folder FOREIGN KEY (folder_id)
        REFERENCES artist_folders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tokens Google Drive (ligne unique id=1)
CREATE TABLE IF NOT EXISTS google_drive_tokens (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    access_token TEXT NOT NULL,
    refresh_token TEXT NULL,
    expires_at DATETIME NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
