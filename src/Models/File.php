<?php
namespace Models;

use Core\Model;

class File extends Model {
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM files WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findLinkedToCourse(int $id): ?array {
        $stmt = $this->db->prepare("SELECT f.*
            FROM files f
            LEFT JOIN courses c ON c.file_id = f.id OR c.id = f.course_id
            WHERE f.id = :id AND c.id IS NOT NULL
            LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Créer un enregistrement de fichier
     */
    public function create(string $originalName, string $storedName, string $mimeType, int $size, ?int $courseId = null): ?int {
        $sql = "INSERT INTO files (original_name, stored_name, mime_type, size, course_id, created_at) 
                VALUES (:original_name, :stored_name, :mime_type, :size, :course_id, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $mimeType,
            'size' => $size,
            'course_id' => $courseId
        ]);
        return $result ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Obtenir tous les fichiers d'un cours
     */
    public function getByCourseId(int $courseId): array {
        $sql = "SELECT f.* FROM files f
                JOIN courses c ON c.file_id = f.id
                WHERE c.id = :course_id
                ORDER BY f.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll();
    }

    /**
     * Supprimer un fichier
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM files WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}