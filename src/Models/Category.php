<?php
namespace Models;

use Core\Model;

class Category extends Model {
    /**
     * Obtenir toutes les catégories
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Obtenir une catégorie par ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Obtenir une catégorie par slug
     */
    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Obtenir les cours d'une catégorie
     */
    public function getCoursesById(int $categoryId, ?string $search = null, ?array $tags = null): array {
        $sql = "
            SELECT c.*, cat.name as category_name, f.original_name as file_name 
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN files f ON c.file_id = f.id
            WHERE c.category_id = :category_id
        ";

        // Filtre recherche
        if ($search) {
            $search = "%$search%";
            $sql .= " AND (c.title LIKE :search OR c.description LIKE :search)";
        }

        // Filtre tags
        if (!empty($tags)) {
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            $sql .= " AND c.id IN (
                SELECT course_id FROM course_tags 
                WHERE tag_id IN ($placeholders)
                GROUP BY course_id
            )";
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['category_id' => $categoryId, 'search' => $search ?? null]);
        return $stmt->fetchAll();
    }
}