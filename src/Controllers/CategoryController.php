<?php
namespace Controllers;

use Core\Controller;
use Models\Category;
use Models\Course;


class CategoryController extends Controller {
    private Category $categoryModel;
    private Course $courseModel;

    public function __construct() {
        $this->categoryModel = new Category();
        $this->courseModel = new Course();
    }

    /**
     * Afficher l'accueil avec les catégories
     */
    public function home(): void {

        // Récupérer les catégories
        $categories = $this->categoryModel->getAll();
        
        // Statistiques pour chaque catégorie
        $db = \Core\Database::getInstance();
        foreach ($categories as &$cat) {
            $result = $db->prepare("SELECT COUNT(*) as count FROM courses WHERE category_id = :id");
            $result->execute(['id' => $cat['id']]);
            $cat['course_count'] = $result->fetch()['count'];
        }

        // Envoi à la vue
        $this->render('categories/home', [
            'categories' => $categories,
        ]);
    }

    /**
     * Afficher les cours d'une catégorie
     */
    public function show(string $slug): void {
        $slug = preg_replace('/[^a-z0-9\-]+/i', '', strtolower(trim($slug))) ?: '';
        $category = $this->categoryModel->findBySlug($slug);

        if (!$category) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $search = trim((string)($_GET['q'] ?? ''));
        $search = $search !== '' ? $search : null;

        $tagFilter = $_GET['tags'] ?? [];
        if (!is_array($tagFilter)) {
            $tagFilter = array_filter(array_map('trim', explode(',', (string)$tagFilter)));
        }
        $tagFilter = array_values(array_unique(array_map('intval', $tagFilter)));

        $courses = $this->courseModel->search(
            keyword: $search,
            categoryId: $category['id'],
            tagIds: $tagFilter
        );

        $favoriteIds = $this->courseModel->getFavoriteCourseIds((int) \Core\Session::get('user_id'));
        foreach ($courses as &$course) {
            $course['is_favorite'] = in_array((int) $course['id'], $favoriteIds, true);
        }

        // Récupérer tous les tags disponibles pour cette catégorie
        $tagsStmt = \Core\Database::getInstance()->prepare("
            SELECT DISTINCT t.* FROM tags t
            JOIN course_tags ct ON t.id = ct.tag_id
            JOIN courses c ON ct.course_id = c.id
            WHERE c.category_id = :category_id
            ORDER BY t.name
        ");
        $tagsStmt->execute(['category_id' => $category['id']]);
        $availableTags = $tagsStmt->fetchAll();

        // Ajouter les tags à chaque cours
        foreach ($courses as &$course) {
            $course['tags'] = $this->courseModel->getTags($course['id']);
        }

        $this->render('categories/show', [
            'category' => $category,
            'courses' => $courses,
            'availableTags' => $availableTags,
            'search' => $search,
            'selectedTags' => $tagFilter ?? []
        ]);
    }

    /**
     * API: Obtenir les catégories (JSON)
     */
    public function listJson(): void {
        header('Content-Type: application/json');
        $categories = $this->categoryModel->getAll();
        echo json_encode($categories);
    }
}
