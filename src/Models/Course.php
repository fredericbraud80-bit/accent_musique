<?php
namespace Models;

use Core\Model;
use PDO;

class Course extends Model {
    private function ensureFavoritesTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS course_favorites (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                course_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_course (user_id, course_id),
                KEY idx_user_id (user_id),
                KEY idx_course_id (course_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Obtenir tous les cours avec détails
     */
    public function getAllWithDetails(): array {
        $sql = "SELECT c.*, cat.name as category_name, f.original_name as file_name 
                FROM courses c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN files f ON c.file_id = f.id
                ORDER BY c.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtenir un cours avec détails
     */
    public function findWithDetails(int $id): ?array {
        $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug, f.original_name, f.stored_name, f.mime_type 
                FROM courses c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN files f ON c.file_id = f.id
                WHERE c.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Obtenir les tags d'un cours
     */
    public function getTags(int $courseId): array {
        $sql = "SELECT t.* FROM tags t
                JOIN course_tags ct ON t.id = ct.tag_id
                WHERE ct.course_id = :course_id
                ORDER BY t.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll();
    }

    /**
     * Rechercher les cours
     */
    public function search(?string $keyword = null, ?int $categoryId = null, ?array $tagIds = null): array {
        $sql = "SELECT c.*, cat.name as category_name, f.original_name as file_name 
                FROM courses c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN files f ON c.file_id = f.id
                WHERE 1=1";
        
        $params = [];

        // Filtre par recherche
        if ($keyword !== null && $keyword !== '') {
            $sql .= " AND (c.title LIKE :keyword_title OR c.description LIKE :keyword_description)";
            $params['keyword_title'] = '%' . trim($keyword) . '%';
            $params['keyword_description'] = '%' . trim($keyword) . '%';
        }

        // Filtre par catégorie
        if ($categoryId) {
            $sql .= " AND c.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        // Filtre par tags
        if (!empty($tagIds)) {
            $tagParams = [];
            foreach ($tagIds as $index => $tagId) {
                $key = ':tag_' . $index;
                $tagParams[] = $key;
                $params['tag_' . $index] = (int)$tagId;
            }
            $sql .= " AND c.id IN (
                SELECT course_id FROM course_tags 
                WHERE tag_id IN (" . implode(',', $tagParams) . ")
            )";
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Créer un nouveau cours
     */
    public function create(string $title, string $description, ?string $youtubeUrl = null, int $categoryId, ?int $fileId = null): ?int {
        $sql = "INSERT INTO courses (title, description, youtube_url, category_id, file_id, created_at) 
                VALUES (:title, :description, :youtube_url, :category_id, :file_id, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'title' => $title,
            'description' => $description,
            'youtube_url' => $youtubeUrl,
            'category_id' => $categoryId,
            'file_id' => $fileId
        ]);
        
        return $result ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Mettre à jour un cours
     */
    public function update(int $id, string $title, string $description, ?string $youtubeUrl = null, int $categoryId, ?int $fileId = null): bool {
        $sql = "UPDATE courses 
                SET title = :title, description = :description, youtube_url = :youtube_url, 
                    category_id = :category_id, file_id = :file_id
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'youtube_url' => $youtubeUrl,
            'category_id' => $categoryId,
            'file_id' => $fileId
        ]);
    }

    /**
     * Supprimer un cours
     */
    public function delete(int $id): bool {
        $this->db->beginTransaction();

        try {
            $deleteLinksStmt = $this->db->prepare("DELETE FROM course_links WHERE course_id = :course_id");
            $deleteLinksStmt->execute(['course_id' => $id]);

            $deleteCourseStmt = $this->db->prepare("DELETE FROM courses WHERE id = :id");
            $result = $deleteCourseStmt->execute(['id' => $id]);

            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Ajouter un tag à un cours
     */
    public function addTag(int $courseId, int $tagId): bool {
        $sql = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)
                ON DUPLICATE KEY UPDATE course_id = course_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['course_id' => $courseId, 'tag_id' => $tagId]);
    }

    /**
     * Obtenir les cours d'une catégorie
     */
    public function getByCategoryId(int $categoryId): array {
        $sql = "SELECT c.*, f.original_name as file_name 
                FROM courses c
                LEFT JOIN files f ON c.file_id = f.id
                WHERE c.category_id = :category_id
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll();
    }

    private function ensureCourseLinksTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS course_links (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                course_id INT NOT NULL,
                link_name VARCHAR(255) NOT NULL,
                page_number INT NOT NULL,
                url TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_course_id (course_id),
                KEY idx_page_number (page_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function saveCourseLinks(int $courseId, array $links): void {
        $this->ensureCourseLinksTable();

        $deleteStmt = $this->db->prepare("DELETE FROM course_links WHERE course_id = :course_id");
        $deleteStmt->execute(['course_id' => $courseId]);

        if ($links === []) {
            return;
        }

        $insertStmt = $this->db->prepare(
            "INSERT INTO course_links (course_id, link_name, page_number, url, created_at)
             VALUES (:course_id, :link_name, :page_number, :url, NOW())"
        );

        foreach ($links as $link) {
            $linkName = trim((string)($link['name'] ?? ''));
            $pageNumber = isset($link['page_number']) ? (int)$link['page_number'] : ((isset($link['page']) ? (int)$link['page'] : 0));
            $url = trim((string)($link['url'] ?? ''));

            if ($linkName === '' || $pageNumber <= 0 || $url === '') {
                continue;
            }

            $insertStmt->execute([
                'course_id' => $courseId,
                'link_name' => $linkName,
                'page_number' => $pageNumber,
                'url' => $url,
            ]);
        }
    }

    public function getCourseLinks(int $courseId): array {
        $this->ensureCourseLinksTable();

        $stmt = $this->db->prepare(
            "SELECT id, course_id, link_name as name, page_number, url
             FROM course_links
             WHERE course_id = :course_id
             ORDER BY page_number ASC, id ASC"
        );
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll();
    }

    public function isFavorite(int $userId, int $courseId): bool {
        $this->ensureFavoritesTable();
        $sql = "SELECT 1 FROM course_favorites WHERE user_id = :user_id AND course_id = :course_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function getFavoriteCourseIds(int $userId): array {
        $this->ensureFavoritesTable();
        $sql = "SELECT course_id FROM course_favorites WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
    }

    public function getFavoritesByUser(int $userId): array {
        $this->ensureFavoritesTable();
        $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug, f.original_name as file_name
                FROM course_favorites cf
                JOIN courses c ON c.id = cf.course_id
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN files f ON c.file_id = f.id
                WHERE cf.user_id = :user_id
                ORDER BY cf.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function toggleFavorite(int $userId, int $courseId): bool {
        $this->ensureFavoritesTable();
        if ($this->isFavorite($userId, $courseId)) {
            $sql = "DELETE FROM course_favorites WHERE user_id = :user_id AND course_id = :course_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'user_id' => $userId,
                'course_id' => $courseId
            ]);
        }

        $sql = "INSERT INTO course_favorites (user_id, course_id, created_at) VALUES (:user_id, :course_id, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
    }
}