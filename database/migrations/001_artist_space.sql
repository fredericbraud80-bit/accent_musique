-- La base existante contient deja le schema artiste:
-- users.access_student/access_artist, artist_spaces, artist_space_users,
-- artist_folders et artist_tracks.

ALTER TABLE artist_spaces ADD COLUMN IF NOT EXISTS google_folder_id VARCHAR(255) NULL AFTER name;
ALTER TABLE artist_spaces ADD INDEX IF NOT EXISTS idx_artist_space_google_folder_id (google_folder_id);

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

INSERT IGNORE INTO artist_folder_users (folder_id, user_id)
SELECT af.id, asu.user_id
FROM artist_folders af
JOIN artist_space_users asu ON asu.space_id = af.space_id;

-- Les droits sont maintenant geres par dossier, cette ancienne liaison est obsolete.
DELETE FROM artist_space_users;

UPDATE users u
LEFT JOIN (SELECT DISTINCT user_id FROM artist_folder_users) f ON f.user_id = u.id
SET u.access_artist = IF(f.user_id IS NULL, 0, 1)
WHERE u.role = 'student';

-- Les anciens droits par dossier deviennent des droits sur la racine correspondante.
INSERT IGNORE INTO artist_space_users (space_id, user_id)
SELECT DISTINCT af.space_id, afu.user_id
FROM artist_folder_users afu
JOIN artist_folders af ON af.id = afu.folder_id
WHERE af.space_id IS NOT NULL;

DELETE FROM artist_folder_users;
