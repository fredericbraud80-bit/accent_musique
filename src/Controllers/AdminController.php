<?php
namespace Controllers;

use Core\Controller;
use Core\Mailer;
use Core\Session;
use Models\User;
use Models\Course;
use Models\Category;
use Models\File as FileModel;

class AdminController extends Controller {
    private User $userModel;
    private Course $courseModel;
    private Category $categoryModel;
    private FileModel $fileModel;

    private const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'mp3', 'mp4', 'm4a', 'wav', 'ogg',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'zip'
    ];

    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/x-pdf',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/webp',
        'image/gif',
        'audio/mpeg',
        'audio/mp3',
        'audio/x-mp3',
        'audio/mpg',
        'audio/x-mpeg',
        'audio/x-wav',
        'audio/wav',
        'audio/wave',
        'audio/x-pn-wav',
        'audio/m4a',
        'audio/x-m4a',
        'audio/mp4',
        'audio/ogg',
        'audio/x-aac',
        'audio/aac',
        'video/mp4',
        'video/x-m4v',
        'video/quicktime',
        'video/webm',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.oasis.opendocument.text',
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream'
    ];

    private const MAX_FILE_SIZE = 62914560; // 60 * 1024 * 1024

    public function __construct() {
        $this->userModel = new User();
        $this->courseModel = new Course();
        $this->categoryModel = new Category();
        $this->fileModel = new FileModel();
    }
    /**
     * Extraire la liste brute des entrées de liens de livre à partir de différents formats de payload
     */
    private function extractRawBookLinkEntries(array $rawLinks): array {
        if ($rawLinks === []) {
            return [];
        }

        if (array_is_list($rawLinks)) {
            return array_filter($rawLinks, 'is_array');
        }

        $entries = [];
        $count = 0;
        foreach (['name', 'page', 'page_number', 'url'] as $field) {
            if (isset($rawLinks[$field]) && is_array($rawLinks[$field])) {
                $count = max($count, count($rawLinks[$field]));
            }
        }

        if ($count > 0) {
            for ($index = 0; $index < $count; $index++) {
                $entries[] = [
                    'name' => $rawLinks['name'][$index] ?? '',
                    'page_number' => $rawLinks['page_number'][$index] ?? ($rawLinks['page'][$index] ?? null),
                    'url' => $rawLinks['url'][$index] ?? '',
                ];
            }
        }

        foreach ($rawLinks as $item) {
            if (is_array($item)) {
                $entries[] = $item;
            }
        }

        return $entries;
    }

    /**
     * Valider et formater une entrée de lien de livre
     */
    private function formatBookLinkEntry(mixed $rawLink): ?array {
        if (!is_array($rawLink)) {
            return null;
        }

        $name = trim((string)($rawLink['name'] ?? ''));
        $pageRaw = $rawLink['page_number'] ?? ($rawLink['page'] ?? null);
        $page = filter_var($pageRaw, FILTER_VALIDATE_INT);
        $url = trim((string)($rawLink['url'] ?? ''));

        if ($name === '' || $page === false || $page <= 0 || $url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url) && !str_starts_with($url, '/')) {
            $url = 'https://' . ltrim($url, '/');
        }

        return [
            'name' => $name,
            'page_number' => $page,
            'url' => $url,
        ];
    }

    private function normalizeBookLinks(array $rawLinks): array {
        $entries = $this->extractRawBookLinkEntries($rawLinks);
        $links = [];

        foreach ($entries as $entry) {
            $formatted = $this->formatBookLinkEntry($entry);
            if ($formatted !== null) {
                $links[] = $formatted;
            }
        }

        return $links;
    }

    private function saveBookLinksForCourse(int $courseId, int $categoryId): void {
        $category = $this->categoryModel->findById($categoryId);
        if (($category['slug'] ?? '') !== 'livres') {
            return;
        }

        $rawLinks = $_POST['book_links'] ?? [];
        if (!is_array($rawLinks) || $rawLinks === []) {
            return;
        }

        $normalizedLinks = $this->normalizeBookLinks($rawLinks);
        if ($normalizedLinks === []) {
            return;
        }

        $this->courseModel->saveCourseLinks($courseId, $normalizedLinks);
    }

    /**
     * Page de tableau de bord admin
     */
public function dashboard(): void {
    $db = \Core\Database::getInstance();

    // Désactiver automatiquement les licences expirées
    $this->userModel->deactivateExpiredLicenses();

    // Utilisateurs en attente de validation
    $pendingStmt = $db->query("
        SELECT id, fullname, email, created_at
        FROM users
        WHERE is_validated = 0
        ORDER BY created_at DESC
    ");
    $pendingUsers = $pendingStmt->fetchAll();

    // Utilisateurs validés
    $validStmt = $db->query("
        SELECT id, fullname, email, role, created_at, license_expires_at, is_license_active
        FROM users
        WHERE is_validated = 1
        ORDER BY created_at DESC
    ");
    $validatedUsers = $validStmt->fetchAll();

    // Stats utilisateurs
    $statsStmt = $db->query("
        SELECT
            COUNT(*) as total,
            SUM(is_validated) as validated,
            SUM(is_validated = 0) as pending
        FROM users
    ");
    $stats = $statsStmt->fetch();

    // Stats cours
    $coursesStmt = $db->query("SELECT COUNT(*) as count FROM courses");
    $coursesStats = $coursesStmt->fetch();

    // Licences expirées
        $expiredStmt = $db->query("
            SELECT id, fullname, email, license_expires_at
            FROM users
            WHERE license_expires_at IS NOT NULL
            AND license_expires_at < NOW()
            ORDER BY license_expires_at ASC
        ");
        $expiredLicenses = $expiredStmt->fetchAll();


    // Licences expirant dans 30 jours
    $soonStmt = $db->query("
        SELECT id, fullname, email, license_expires_at
        FROM users
        WHERE is_license_active = 1
        AND license_expires_at <= DATE_ADD(NOW(), INTERVAL 30 DAY)
        ORDER BY license_expires_at ASC
    ");
    $soonLicenses = $soonStmt->fetchAll();

    $this->render('admin/dashboard', [
        'pendingUsers' => $pendingUsers,
        'validatedUsers' => $validatedUsers,
        'stats' => $stats,
        'coursesStats' => $coursesStats,
        'expiredLicenses' => $expiredLicenses,
        'soonLicenses' => $soonLicenses
    ]);
}


    /**
     * Valider un utilisateur pour 1 an
     */
    public function validate(int $id): void {
    $user = $this->userModel->findById($id);

    if (!$user) {
        Session::setFlash('error', 'Utilisateur introuvable.');
        $this->redirect('/admin');
    }

    $db = \Core\Database::getInstance();
    // Désactiver automatiquement les licences expirées
        $this->userModel->deactivateExpiredLicenses();

    $stmt = $db->prepare("
        UPDATE users
        SET
            is_validated = 1,
            license_issued_at = NOW(),
            license_expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR),
            is_license_active = 1
        WHERE id = :id
    ");

    if ($stmt->execute(['id' => $id])) {
        $mailer = new Mailer();
        $mailer->sendAccountValidated($user['email'], $user['fullname']);

        Session::setFlash('success', "Le compte de {$user['fullname']} a été validé et la licence activée pour 1 an.");
    } else {
        Session::setFlash('error', 'Erreur lors de la validation.');
    }

    $this->redirect('/admin');
}

public function renewLicense(int $id): void {
    $user = $this->userModel->findById($id);

    if (!$user) {
        Session::setFlash('error', 'Utilisateur introuvable.');
        $this->redirect('/admin');
    }

    $db = \Core\Database::getInstance();
    $stmt = $db->prepare("
        UPDATE users
        SET
            license_issued_at = NOW(),
            license_expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR),
            is_license_active = 1
        WHERE id = :id
    ");

    if ($stmt->execute(['id' => $id])) {
        Session::setFlash('success', "Licence de {$user['fullname']} renouvelée pour 1 an.");
    } else {
        Session::setFlash('error', 'Erreur lors du renouvellement.');
    }

    $this->redirect('/admin');
}

    /**
     * Rejeter un utilisateur (supprimer)
     */
    public function reject(int $id): void {
        $user = $this->userModel->findById($id);

        if (!$user) {
            Session::setFlash('error', 'Utilisateur introuvable.');
            $this->redirect('/admin');
        }

        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");

        if ($stmt->execute(['id' => $id])) {
            Session::setFlash('success', "Le compte de {$user['fullname']} a été rejeté et supprimé.");
        } else {
            Session::setFlash('error', 'Erreur lors de la suppression.');
        }

        $this->redirect('/admin');
    }

    /**
     * Promouvoir un utilisateur en admin
     */
    public function promote(int $id): void {
        $user = $this->userModel->findById($id);

        if (!$user) {
            Session::setFlash('error', 'Utilisateur introuvable.');
            $this->redirect('/admin');
        }

        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE id = :id");

        if ($stmt->execute(['id' => $id])) {
            Session::setFlash('success', "{$user['fullname']} est maintenant administrateur.");
        } else {
            Session::setFlash('error', 'Erreur lors de la promotion.');
        }

        $this->redirect('/admin');
    }

    /**
     * Afficher le formulaire de création de cours
     */
    public function createCourse(): void {
        $categories = $this->categoryModel->getAll();

        $this->render('admin/courses/create', [
            'categories' => $categories
        ]);
    }

        /**
     * Obtenir et préparer le dossier de stockage des fichiers
     */
    private function getUploadDirectory(): string {
        $uploadDir = defined('STORAGE_PATH') ? STORAGE_PATH : (__DIR__ . '/../../storage/files/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        return $uploadDir;
    }

    /**
     * Valider la taille, l'extension et le type MIME d'un fichier téléversé
     */
    private function isValidUploadedFile(string $originalName, string $tmpName, int $size): bool {
        if ($size <= 0 || $size > self::MAX_FILE_SIZE) {
            return false;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return false;
        }

        $mimeType = mime_content_type($tmpName);
        if (!is_string($mimeType) || !in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return false;
        }

        return true;
    }

    /**
     * Traiter les fichiers téléversés pour un cours et retourner l'identifiant du premier fichier
     */
    private function processCourseFileUploads(int $courseId): ?int {
        if (empty($_FILES['files']['name']) || !is_array($_FILES['files']['name'])) {
            return null;
        }

        $uploadDir = $this->getUploadDirectory();
        $fileCount = count($_FILES['files']['name']);
        $firstFileId = null;

        for ($i = 0; $i < $fileCount; $i++) {
            if (!isset($_FILES['files']['error'][$i]) || $_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $originalName = basename($_FILES['files']['name'][$i]);
            $tmpName = $_FILES['files']['tmp_name'][$i];
            $size = (int) $_FILES['files']['size'][$i];

            if (!$this->isValidUploadedFile($originalName, $tmpName, $size)) {
                continue;
            }

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $mimeType = (string) mime_content_type($tmpName);
            $storedName = uniqid('file_', true) . '.' . $extension;
            $uploadPath = rtrim($uploadDir, "/\\") . DIRECTORY_SEPARATOR . $storedName;

            if (move_uploaded_file($tmpName, $uploadPath)) {
                $fileId = $this->fileModel->create($originalName, $storedName, $mimeType, $size, $courseId);
                if ($fileId !== null && $firstFileId === null) {
                    $firstFileId = $fileId;
                }
            }
        }

        return $firstFileId;
    }

    /**
     * Normaliser et valider une URL YouTube (retourne null si vide, string normalisée si valide, false si invalide)
     */
    private function normalizeYoutubeUrl(?string $url): string|false|null {
        $url = trim((string)$url);
        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return $url;
    }
    /**
     * Créer un cours (traiter le formulaire)
     */
    public function storeCourse(): void {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $categoryId = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);

        if ($title === '' || $description === '' || $categoryId === false || $categoryId <= 0) {
            Session::setFlash('error', 'Titre, description et catégorie sont requis.');
            $this->redirect('/admin/courses/create');
            return;
        }

        $youtubeUrl = $this->normalizeYoutubeUrl($_POST['youtube_url'] ?? null);
        if ($youtubeUrl === false) {
            Session::setFlash('error', 'L’URL YouTube est invalide.');
            $this->redirect('/admin/courses/create');
            return;
        }

        $courseId = $this->courseModel->create($title, $description, $youtubeUrl, $categoryId);
        if (!$courseId) {
            Session::setFlash('error', 'Erreur lors de la création du cours.');
            $this->redirect('/admin/courses/create');
            return;
        }

        $this->saveBookLinksForCourse($courseId, $categoryId);

        $firstFileId = $this->processCourseFileUploads($courseId);
        if ($firstFileId !== null) {
            $this->courseModel->update($courseId, $title, $description, $youtubeUrl, $categoryId, $firstFileId);
        }

        Session::setFlash('success', 'Cours créé avec succès !');
        $this->redirect('/admin/courses');
    }

    /**
     * Afficher la liste des cours
     */
    public function courses(): void {
        $db = \Core\Database::getInstance();
        $stmt = $db->query("
            SELECT c.*, cat.name as category_name, COUNT(f.id) as file_count
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN files f ON f.course_id = c.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $courses = $stmt->fetchAll();

        $this->render('admin/courses/list', [
            'courses' => $courses
        ]);
    }

    /**
     * Éditer un cours
     */
    public function editCourse(int $id): void {
        $course = $this->courseModel->findWithDetails($id);

        if (!$course) {
            Session::setFlash('error', 'Cours introuvable.');
            $this->redirect('/admin/courses');
            return;
        }

        $categories = $this->categoryModel->getAll();
        $courseLinks = $this->courseModel->getCourseLinks($id);

        $this->render('admin/courses/edit', [
            'course' => $course,
            'categories' => $categories,
            'courseLinks' => $courseLinks,
        ]);
    }

    /**
     * Mettre à jour un cours
     */
    public function updateCourse(int $id): void {
        $course = $this->courseModel->findWithDetails($id);

        if (!$course) {
            Session::setFlash('error', 'Cours introuvable.');
            $this->redirect('/admin/courses');
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $categoryId = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);

        if ($title === '' || $description === '' || $categoryId === false || $categoryId <= 0) {
            Session::setFlash('error', 'Titre, description et catégorie sont requis.');
            $this->redirect('/admin/courses/edit/' . $id);
            return;
        }

        $youtubeUrl = $this->normalizeYoutubeUrl($_POST['youtube_url'] ?? null);
        if ($youtubeUrl === false) {
            Session::setFlash('error', 'L’URL YouTube est invalide.');
            $this->redirect('/admin/courses/edit/' . $id);
            return;
        }

        $fileId = !empty($course['file_id']) ? (int)$course['file_id'] : null;

        if ($this->courseModel->update($id, $title, $description, $youtubeUrl, $categoryId, $fileId)) {
            $this->saveBookLinksForCourse($id, $categoryId);
            Session::setFlash('success', 'Cours mis à jour avec succès !');
        } else {
            Session::setFlash('error', 'Erreur lors de la mise à jour.');
        }

        $this->redirect('/admin/courses');
    }

    /**
     * Supprimer un cours
     */
    public function deleteCourse(int $id): void {
        $course = $this->courseModel->findWithDetails($id);

        if (!$course) {
            Session::setFlash('error', 'Cours introuvable.');
            $this->redirect('/admin/courses');
            return;
        }

        if ($this->courseModel->delete($id)) {
            Session::setFlash('success', 'Cours supprimé avec succès !');
        } else {
            Session::setFlash('error', 'Erreur lors de la suppression.');
        }

        $this->redirect('/admin/courses');
    }
}

