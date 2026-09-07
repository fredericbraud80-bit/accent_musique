<?php
namespace Models;

use Core\Model;
use PDO;

class ArtistSpace extends Model {
    public function hasSpace(int $userId, string $space): bool {
        $column = $space === 'artist' ? 'access_artist' : 'access_student';
        $stmt = $this->db->prepare("SELECT {$column} FROM users WHERE id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function setSpaces(int $userId, array $spaces): bool {
        $stmt = $this->db->prepare('UPDATE users SET access_student = :student, access_artist = :artist WHERE id = :user_id');
        return $stmt->execute([
            'student' => in_array('student', $spaces, true) ? 1 : 0,
            'artist' => in_array('artist', $spaces, true) ? 1 : 0,
            'user_id' => $userId,
        ]);
    }

    public function getUsersWithSpaces(): array {
        $stmt = $this->db->query("SELECT u.id, u.fullname, u.email, u.role, u.created_at, u.is_validated,
            u.license_expires_at, u.is_license_active,
            u.access_student, u.access_artist,
            TRIM(BOTH ',' FROM CONCAT(IF(u.access_student = 1, 'student,', ''), IF(u.access_artist = 1, 'artist', ''))) AS spaces
            FROM users u WHERE u.role <> 'admin' AND u.is_validated = 1 ORDER BY u.created_at DESC");
        return $stmt->fetchAll();
    }

    public function createFolder(int $spaceId, string $name, string $driveId, ?int $parentId, int $adminId): int {
        $stmt = $this->db->prepare('INSERT INTO artist_folders (space_id, name, parent_id, google_folder_id, created_by) VALUES (:space_id, :name, :parent_id, :drive_id, :admin_id)');
        $stmt->execute(['space_id' => $spaceId, 'name' => $name, 'parent_id' => $parentId, 'drive_id' => $driveId, 'admin_id' => $adminId]);
        return (int)$this->db->lastInsertId();
    }

    public function createSpace(string $name, string $driveId, int $adminId): int {
        $stmt = $this->db->prepare('INSERT INTO artist_spaces (name, google_folder_id, created_by) VALUES (:name, :drive_id, :admin_id)');
        $stmt->execute(['name' => $name, 'drive_id' => $driveId, 'admin_id' => $adminId]);
        return (int)$this->db->lastInsertId();
    }

    public function findSpaceByDriveId(string $driveId): ?array {
        $stmt = $this->db->prepare('SELECT id, name FROM artist_spaces WHERE google_folder_id = :drive_id LIMIT 1');
        $stmt->execute(['drive_id' => $driveId]);
        return $stmt->fetch() ?: null;
    }

    public function createSyncedSpace(string $name, string $driveId, int $adminId): int {
        $existing = $this->findSpaceByDriveId($driveId);
        if ($existing) {
            return (int)$existing['id'];
        }
        return $this->createSpace($name, $driveId, $adminId);
    }

    public function updateSpaceName(int $spaceId, string $name): void {
        $stmt = $this->db->prepare('UPDATE artist_spaces SET name = :name WHERE id = :space_id');
        $stmt->execute(['name' => $name, 'space_id' => $spaceId]);
    }

    public function removeSpacesMissingFromDrive(array $driveSpaceIds): int {
        $driveSpaceIds = array_values(array_filter(array_map('strval', $driveSpaceIds)));
        $stmt = $this->db->query("SELECT id, google_folder_id FROM artist_spaces WHERE google_folder_id IS NOT NULL AND google_folder_id <> ''");
        $spaces = $stmt->fetchAll();
        $removed = 0;
        foreach ($spaces as $space) {
            if (in_array((string)$space['google_folder_id'], $driveSpaceIds, true)) {
                continue;
            }
            $folderStmt = $this->db->prepare('SELECT id FROM artist_folders WHERE space_id = :space_id');
            $folderStmt->execute(['space_id' => $space['id']]);
            $folderIds = array_map('intval', $folderStmt->fetchAll(PDO::FETCH_COLUMN));
            if ($folderIds) {
                $marks = implode(',', array_fill(0, count($folderIds), '?'));
                $this->db->prepare("DELETE FROM artist_tracks WHERE folder_id IN ({$marks})")->execute($folderIds);
                $this->db->prepare("DELETE FROM artist_folders WHERE id IN ({$marks})")->execute($folderIds);
            }
            $this->db->prepare('DELETE FROM artist_space_users WHERE space_id = :space_id')->execute(['space_id' => $space['id']]);
            $delete = $this->db->prepare('DELETE FROM artist_spaces WHERE id = :space_id');
            $delete->execute(['space_id' => $space['id']]);
            $removed += $delete->rowCount();
        }
        return $removed;
    }

    public function findFolderByDriveId(int $spaceId, string $driveId): ?array {
        $stmt = $this->db->prepare('SELECT id, parent_id FROM artist_folders WHERE space_id = :space_id AND google_folder_id = :drive_id LIMIT 1');
        $stmt->execute(['space_id' => $spaceId, 'drive_id' => $driveId]);
        return $stmt->fetch() ?: null;
    }

    public function createSyncedFolder(int $spaceId, string $name, string $driveId, ?int $parentId, int $adminId): int {
        $existing = $this->findFolderByDriveId($spaceId, $driveId);
        if ($existing) {
            return (int)$existing['id'];
        }
        return $this->createFolder($spaceId, $name, $driveId, $parentId, $adminId);
    }

    public function removeFoldersMissingFromDrive(int $spaceId, array $driveFolderIds): int {
        $driveFolderIds = array_values(array_filter(array_map('strval', $driveFolderIds)));
        $stmt = $this->db->prepare('SELECT id, google_folder_id FROM artist_folders WHERE space_id = :space_id');
        $stmt->execute(['space_id' => $spaceId]);
        $folders = $stmt->fetchAll();
        $removed = 0;

        foreach ($folders as $folder) {
            if (in_array((string)$folder['google_folder_id'], $driveFolderIds, true)) {
                continue;
            }
            $deleteTracks = $this->db->prepare('DELETE FROM artist_tracks WHERE folder_id = :folder_id');
            $deleteTracks->execute(['folder_id' => $folder['id']]);
            $delete = $this->db->prepare('DELETE FROM artist_folders WHERE id = :folder_id');
            $delete->execute(['folder_id' => $folder['id']]);
            $removed += $delete->rowCount();
        }
        return $removed;
    }
    
    public function linkSpaceToDrive(int $spaceId, string $driveId): bool {
        $stmt = $this->db->prepare('UPDATE artist_spaces SET google_folder_id = :drive_id WHERE id = :space_id');
        return $stmt->execute(['drive_id' => $driveId, 'space_id' => $spaceId]);
    }
    
    public function linkFolderToDrive(int $folderId, string $driveId): bool {
        $stmt = $this->db->prepare('UPDATE artist_folders SET google_folder_id = :drive_id WHERE id = :folder_id');
        return $stmt->execute(['drive_id' => $driveId, 'folder_id' => $folderId]);
    }

    public function assignFolder(int $folderId, int $userId): bool {
        $stmt = $this->db->prepare('INSERT IGNORE INTO artist_folder_users (folder_id, user_id) SELECT :folder_id, id FROM users WHERE id = :user_id AND is_validated = 1');
        $result = $stmt->execute(['folder_id' => $folderId, 'user_id' => $userId]);
        $enable = $this->db->prepare('UPDATE users SET access_artist = 1 WHERE id = :user_id AND is_validated = 1');
        $enable->execute(['user_id' => $userId]);
        return $result;
    }

    public function getFoldersForUser(int $userId): array {
        $stmt = $this->db->prepare('SELECT af.* FROM artist_folders af JOIN artist_space_users asu ON asu.space_id = af.space_id WHERE asu.user_id = :user_id ORDER BY af.name');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function userCanAccessFolder(int $userId, int $folderId): bool {
        $stmt = $this->db->prepare('SELECT 1 FROM artist_space_users asu JOIN artist_folders af ON af.space_id = asu.space_id WHERE asu.user_id = :user_id AND af.id = :folder_id LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'folder_id' => $folderId]);
        return (bool)$stmt->fetchColumn();
    }

    public function setFolderUsers(int $folderId, array $userIds): bool {
        $folderStmt = $this->db->prepare('SELECT space_id FROM artist_folders WHERE id = :folder_id LIMIT 1');
        $folderStmt->execute(['folder_id' => $folderId]);
        $spaceId = $folderStmt->fetchColumn();
        if (!$spaceId) {
            return false;
        }

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), static fn (int $id): bool => $id > 0)));
        $this->db->beginTransaction();
        try {
            $delete = $this->db->prepare('DELETE FROM artist_folder_users WHERE folder_id = :folder_id');
            $delete->execute(['folder_id' => $folderId]);

            $insert = $this->db->prepare('INSERT IGNORE INTO artist_folder_users (folder_id, user_id) SELECT :folder_id, id FROM users WHERE id = :user_id AND is_validated = 1');
            $enableArtist = $this->db->prepare('UPDATE users SET access_artist = 1 WHERE id = :user_id AND is_validated = 1');
            foreach ($userIds as $userId) {
                $insert->execute(['folder_id' => $folderId, 'user_id' => $userId]);
                $enableArtist->execute(['user_id' => $userId]);
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getFolderUsers(int $folderId): array {
        $stmt = $this->db->prepare('SELECT u.id, u.fullname, u.email FROM artist_space_users asu JOIN artist_folders af ON af.space_id = asu.space_id JOIN users u ON u.id = asu.user_id WHERE af.id = :folder_id ORDER BY u.fullname');
        $stmt->execute(['folder_id' => $folderId]);
        return $stmt->fetchAll();
    }

    public function getSpaceUsers(int $spaceId): array {
        $stmt = $this->db->prepare('SELECT u.id, u.fullname, u.email FROM artist_space_users asu JOIN users u ON u.id = asu.user_id WHERE asu.space_id = :space_id ORDER BY u.fullname');
        $stmt->execute(['space_id' => $spaceId]);
        return $stmt->fetchAll();
    }

    public function searchValidatedSpaceUsers(string $query, int $spaceId): array {
        $stmt = $this->db->prepare("SELECT u.id, u.fullname, u.email FROM users u
            WHERE u.is_validated = 1 AND u.role <> 'admin'
            AND (u.fullname LIKE :name_query OR u.email LIKE :email_query)
            AND NOT EXISTS (SELECT 1 FROM artist_space_users asu WHERE asu.space_id = :space_id AND asu.user_id = u.id)
            ORDER BY u.fullname LIMIT 10");
        $like = '%' . $query . '%';
        $stmt->execute(['name_query' => $like, 'email_query' => $like, 'space_id' => $spaceId]);
        return $stmt->fetchAll();
    }

    public function addUserToSpace(int $spaceId, int $userId): bool {
        $stmt = $this->db->prepare('INSERT IGNORE INTO artist_space_users (space_id, user_id) SELECT :space_id, id FROM users WHERE id = :user_id AND is_validated = 1 AND role <> \'admin\'');
        $result = $stmt->execute(['space_id' => $spaceId, 'user_id' => $userId]);
        if ($result && $stmt->rowCount() > 0) {
            $this->db->prepare('UPDATE users SET access_artist = 1 WHERE id = :user_id')->execute(['user_id' => $userId]);
        }
        return $result;
    }

    public function removeUserFromSpace(int $spaceId, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM artist_space_users WHERE space_id = :space_id AND user_id = :user_id');
        $result = $stmt->execute(['space_id' => $spaceId, 'user_id' => $userId]);
        $remaining = $this->db->prepare('SELECT 1 FROM artist_space_users WHERE user_id = :user_id LIMIT 1');
        $remaining->execute(['user_id' => $userId]);
        if (!$remaining->fetchColumn()) {
            $this->db->prepare('UPDATE users SET access_artist = 0 WHERE id = :user_id')->execute(['user_id' => $userId]);
        }
        return $result;
    }

    public function searchValidatedUsers(string $query, int $folderId): array {
        $stmt = $this->db->prepare("SELECT u.id, u.fullname, u.email FROM users u
            WHERE u.is_validated = 1 AND u.role <> 'admin'
            AND (u.fullname LIKE :name_query OR u.email LIKE :email_query)
            AND NOT EXISTS (SELECT 1 FROM artist_folder_users afu WHERE afu.folder_id = :folder_id AND afu.user_id = u.id)
            ORDER BY u.fullname LIMIT 10");
        $like = '%' . $query . '%';
        $stmt->execute(['name_query' => $like, 'email_query' => $like, 'folder_id' => $folderId]);
        return $stmt->fetchAll();
    }

    public function addUserToFolder(int $folderId, int $userId): bool {
        $stmt = $this->db->prepare('INSERT IGNORE INTO artist_folder_users (folder_id, user_id) SELECT :folder_id, id FROM users WHERE id = :user_id AND is_validated = 1 AND role <> \'admin\'');
        $result = $stmt->execute(['folder_id' => $folderId, 'user_id' => $userId]);
        if ($result && $stmt->rowCount() > 0) {
            $this->db->prepare('UPDATE users SET access_artist = 1 WHERE id = :user_id')->execute(['user_id' => $userId]);
        }
        return $result;
    }

    public function removeUserFromFolder(int $folderId, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM artist_folder_users WHERE folder_id = :folder_id AND user_id = :user_id');
        return $stmt->execute(['folder_id' => $folderId, 'user_id' => $userId]);
    }

    public function deleteFolder(int $folderId): ?string {
        $stmt = $this->db->prepare('SELECT google_folder_id FROM artist_folders WHERE id = :folder_id LIMIT 1');
        $stmt->execute(['folder_id' => $folderId]);
        $driveId = $stmt->fetchColumn();
        $ids = [$folderId];
        for ($index = 0; $index < count($ids); $index++) {
            $children = $this->db->prepare('SELECT id FROM artist_folders WHERE parent_id = :parent_id');
            $children->execute(['parent_id' => $ids[$index]]);
            foreach ($children->fetchAll() as $child) {
                $ids[] = (int)$child['id'];
            }
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $delete = $this->db->prepare("DELETE FROM artist_folders WHERE id IN ({$placeholders})");
        $delete->execute($ids);
        return (string)$driveId;
    }
}