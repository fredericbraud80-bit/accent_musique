<?php
namespace Controllers;

use Core\Controller;
use Core\Mailer;
use Core\Session;
use Core\GoogleDrive;
use Models\User;
use Models\ArtistSpace;
use Models\Course;
use Models\Category;
use Models\File as FileModel;

class AdminController extends Controller {
    private User $userModel;
    private Course $courseModel;
    private Category $categoryModel;
    private FileModel $fileModel;
    private ArtistSpace $artistSpace;

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
        $this->artistSpace = new ArtistSpace();
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
    $validatedUsers = $this->artistSpace->getUsersWithSpaces();
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

public function artistManagement(): void {
    $db = \Core\Database::getInstance();
    $users = $this->artistSpace->getUsersWithSpaces();
    $artistSpaces = $db->query("SELECT id, name, google_folder_id FROM artist_spaces ORDER BY name")->fetchAll();
    foreach ($artistSpaces as &$space) {
        $space['users'] = $this->artistSpace->getSpaceUsers((int)$space['id']);
    }
    unset($space);
    $artistFolders = $db->query("SELECT af.id, af.space_id, af.name, af.parent_id, af.google_folder_id,
            COUNT(DISTINCT asu.user_id) AS assigned_count
        FROM artist_folders af LEFT JOIN artist_space_users asu ON asu.space_id = af.space_id
        GROUP BY af.id ORDER BY af.name")->fetchAll();
    foreach ($artistFolders as &$folder) {
        $folder['drive_contents'] = [];
        if (!empty($folder['google_folder_id'])) {
            try {
                $folder['drive_contents'] = (new GoogleDrive())->listFolderContents($folder['google_folder_id']);
            } catch (\Throwable $exception) {
                error_log($exception->getMessage());
                $folder['drive_error'] = 'Contenu Drive inaccessible.';
            }
        }
    }
    unset($folder);
    $driveContentsBySpace = [];
    foreach ($artistSpaces as $space) {
        $driveContentsBySpace[(int)$space['id']] = [];
        if (!empty($space['google_folder_id'])) {
            try {
                $driveContentsBySpace[(int)$space['id']] = (new GoogleDrive())->listFolderContents($space['google_folder_id']);
            } catch (\Throwable $exception) {
                error_log($exception->getMessage());
            }
        }
    }
    $driveConnected = (int)$db->query('SELECT COUNT(*) FROM google_drive_tokens')->fetchColumn() > 0;

    $this->render('admin/artists', [
        'users' => $users,
        'artistSpaces' => $artistSpaces,
        'driveContentsBySpace' => $driveContentsBySpace,
        'artistFolders' => $artistFolders,
        'driveConnected' => $driveConnected,
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

public function updateSpaces(int $id): void {
    $user = $this->userModel->findById($id);
    if (!$user) {
        Session::setFlash('error', 'Utilisateur introuvable.');
        $this->redirect('/admin');
    }

    $spaces = $_POST['spaces'] ?? [];
    if (!is_array($spaces)) {
        $spaces = [];
    }
    $spaces = array_values(array_intersect(['student', 'artist'], $spaces));
    if (!$this->artistSpace->setSpaces($id, $spaces)) {
        Session::setFlash('error', 'Impossible de mettre à jour les espaces de ' . $user['fullname'] . '.');
        $this->redirect('/admin');
    }

    Session::setFlash('success', 'Les espaces de ' . $user['fullname'] . ' ont été mis à jour.');
    $this->redirect('/admin');
}

public function updateArtistFolderUsers(int $folderId): void {
    $userIds = $_POST['user_ids'] ?? [];
    if (!is_array($userIds)) {
        $userIds = [];
    }
    if (!$this->artistSpace->setFolderUsers($folderId, $userIds)) {
        Session::setFlash('error', 'Dossier artiste introuvable.');
        $this->redirect('/admin/artistes');
    }
    Session::setFlash('success', 'Les accès au dossier artiste ont été mis à jour.');
    $this->redirect('/admin/artistes');
}

public function searchArtistFolderUsers(int $folderId): void {
    header('Content-Type: application/json; charset=utf-8');
    $query = trim((string)($_GET['q'] ?? ''));
    echo json_encode($query === '' ? [] : $this->artistSpace->searchValidatedUsers($query, $folderId), JSON_UNESCAPED_UNICODE);
    exit;
}

public function searchArtistSpaceUsers(int $spaceId): void {
    header('Content-Type: application/json; charset=utf-8');
    $query = trim((string)($_GET['q'] ?? ''));
    echo json_encode($query === '' ? [] : $this->artistSpace->searchValidatedSpaceUsers($query, $spaceId), JSON_UNESCAPED_UNICODE);
    exit;
}

public function addArtistSpaceUser(int $spaceId): void {
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    if ($userId === false || !$this->artistSpace->addUserToSpace($spaceId, (int)$userId)) {
        Session::setFlash('error', 'Impossible d’ajouter cet utilisateur au groupe.');
    } else {
        Session::setFlash('success', 'Utilisateur ajouté au groupe artiste.');
    }
    $this->redirect('/admin/artistes');
}

public function removeArtistSpaceUser(int $spaceId, int $userId): void {
    $this->artistSpace->removeUserFromSpace($spaceId, $userId);
    Session::setFlash('success', 'Accès retiré du groupe artiste.');
    $this->redirect('/admin/artistes');
}

public function addArtistFolderUser(int $folderId): void {
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    if ($userId === false || !$this->artistSpace->addUserToFolder($folderId, (int)$userId)) {
        Session::setFlash('error', 'Impossible d’ajouter cet utilisateur au dossier.');
    } else {
        Session::setFlash('success', 'Utilisateur ajouté au dossier.');
    }
    $this->redirect('/admin/artistes');
}

public function removeArtistFolderUser(int $folderId, int $userId): void {
    $this->artistSpace->removeUserFromFolder($folderId, $userId);
    Session::setFlash('success', 'Accès retiré du dossier.');
    $this->redirect('/admin/artistes');
}

public function deleteArtistFolder(int $folderId): void {
    $driveId = $this->artistSpace->deleteFolder($folderId);
    if ($driveId) {
        try {
            (new GoogleDrive())->deleteFolder($driveId);
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
        }
    }
    Session::setFlash('success', 'Dossier supprimé.');
    $this->redirect('/admin/artistes');
}

public function connectGoogleDrive(): void {
    header('Location: ' . (new GoogleDrive())->authorizationUrl());
    exit;
}

public function googleDriveCallback(): void {
    $code = trim((string)($_GET['code'] ?? ''));
    $state = $_GET['state'] ?? null;
    $drive = new GoogleDrive();

    if ($code === '' || !$drive->verifyOAuthState(is_string($state) ? $state : null)) {
        Session::setFlash('error', 'Autorisation Google Drive annulée.');
        $this->redirect('/admin');
    }
    $drive->exchangeCode($code);
    Session::setFlash('success', 'Google Drive est connecté.');
    $this->redirect('/admin');
}

public function checkGoogleDrive(): void {
    try {
        $result = (new GoogleDrive())->checkConnection();
        Session::setFlash('success', 'Google Drive répond correctement. Lecture et écriture vérifiées (' . $result['folders_seen'] . ' dossier détecté).');
    } catch (\Throwable $exception) {
        error_log($exception->getMessage());
        Session::setFlash('error', 'Google Drive ne répond pas : ' . $exception->getMessage());
    }
    $this->redirect('/admin/artistes');
}

public function syncArtistSpaces(): void {
    try {
        $db = \Core\Database::getInstance();
        $drive = new GoogleDrive();
        $created = 0;
        $removed = 0;
        $rootDriveIds = [];
        if (GOOGLE_DRIVE_ROOT_FOLDER_ID !== '') {
            foreach ($drive->listSubfolders(GOOGLE_DRIVE_ROOT_FOLDER_ID) as $driveSpace) {
                $rootDriveIds[] = $driveSpace->getId();
                $space = $this->artistSpace->findSpaceByDriveId($driveSpace->getId());
                if (!$space) {
                    $spaceId = $this->artistSpace->createSyncedSpace($driveSpace->getName(), $driveSpace->getId(), (int)Session::get('user_id'));
                    $created++;
                } else {
                    $spaceId = (int)$space['id'];
                    $this->artistSpace->updateSpaceName($spaceId, $driveSpace->getName());
                }
                $sync = $this->syncDriveFolders($drive, $spaceId, $driveSpace->getId(), null, (int)Session::get('user_id'));
                $created += $sync['created'];
                $removed += $this->artistSpace->removeFoldersMissingFromDrive($spaceId, $sync['drive_ids']);
            }
            $removed += $this->artistSpace->removeSpacesMissingFromDrive($rootDriveIds);
        } else {
            $spaces = $db->query('SELECT id, google_folder_id FROM artist_spaces WHERE google_folder_id IS NOT NULL AND google_folder_id <> \'\'')->fetchAll();
            foreach ($spaces as $space) {
                $sync = $this->syncDriveFolders($drive, (int)$space['id'], (string)$space['google_folder_id'], null, (int)Session::get('user_id'));
                $created += $sync['created'];
                $removed += $this->artistSpace->removeFoldersMissingFromDrive((int)$space['id'], $sync['drive_ids']);
            }
        }
        Session::setFlash('success', $created . ' dossier(s) ajouté(s), ' . $removed . ' dossier(s) supprimé(s) de la base.');
    } catch (\Throwable $exception) {
        error_log($exception->getMessage());
        Session::setFlash('error', 'La synchronisation Google Drive a échoué : ' . $exception->getMessage());
    }
    $this->redirect('/admin/artistes');
}

private function syncDriveFolders(GoogleDrive $drive, int $spaceId, string $driveParentId, ?int $parentId, int $adminId): array {
    $created = 0;
    $driveIds = [];
    foreach ($drive->listSubfolders($driveParentId) as $driveFolder) {
        $driveIds[] = $driveFolder->getId();
        $folder = $this->artistSpace->findFolderByDriveId($spaceId, $driveFolder->getId());
        if (!$folder) {
            $folderId = $this->artistSpace->createSyncedFolder($spaceId, $driveFolder->getName(), $driveFolder->getId(), $parentId, $adminId);
            $created++;
        } else {
            $folderId = (int)$folder['id'];
        }
        $childSync = $this->syncDriveFolders($drive, $spaceId, $driveFolder->getId(), (int)$folderId, $adminId);
        $created += $childSync['created'];
        $driveIds = array_merge($driveIds, $childSync['drive_ids']);
    }
    return ['created' => $created, 'drive_ids' => $driveIds];
}

public function createArtistSpace(): void {
    $name = trim((string)($_POST['name'] ?? ''));
    if ($name === '') {
        Session::setFlash('error', 'Le nom du groupe ou de l’artiste est requis.');
        $this->redirect('/admin/artistes');
    }
    try {
        $driveId = (new GoogleDrive())->createFolder($name);
        $this->artistSpace->createSpace($name, $driveId, (int)Session::get('user_id'));
        Session::setFlash('success', 'Racine créée dans Google Drive et enregistrée dans la base.');
    } catch (\Throwable $exception) {
        error_log($exception->getMessage());
        Session::setFlash('error', 'La racine n’a pas pu être créée : ' . $exception->getMessage());
    }
    $this->redirect('/admin/artistes');
}

public function linkArtistSpaceDrive(int $spaceId): void {
    $driveId = trim((string)($_POST['google_folder_id'] ?? ''));
    if ($driveId === '') {
        Session::setFlash('error', 'Identifiant Google Drive requis.');
    } else {
        $this->artistSpace->linkSpaceToDrive($spaceId, $driveId);
        Session::setFlash('success', 'La racine est maintenant reliée à Google Drive.');
    }
    $this->redirect('/admin/artistes');
}

public function linkArtistFolderDrive(int $folderId): void {
    $driveId = trim((string)($_POST['google_folder_id'] ?? ''));
    if ($driveId === '') {
        Session::setFlash('error', 'Identifiant Google Drive requis.');
    } else {
        $this->artistSpace->linkFolderToDrive($folderId, $driveId);
        Session::setFlash('success', 'Le dossier est maintenant relié à Google Drive.');
    }
    $this->redirect('/admin/artistes');
}

public function createArtistFolder(): void {
    $name = trim((string)($_POST['name'] ?? ''));
    $spaceId = filter_var($_POST['space_id'] ?? null, FILTER_VALIDATE_INT);
    $parentId = filter_var($_POST['parent_id'] ?? null, FILTER_VALIDATE_INT);
    if ($name === '' || $spaceId === false || $spaceId === null) {
        Session::setFlash('error', 'Nom et groupe/artiste requis.');
        $this->redirect('/admin/artistes');
    }
    $parentDriveId = null;
    if ($parentId !== false && $parentId !== null) {
        $parentStmt = \Core\Database::getInstance()->prepare('SELECT google_folder_id FROM artist_folders WHERE id = :id AND space_id = :space_id LIMIT 1');
        $parentStmt->execute(['id' => $parentId, 'space_id' => $spaceId]);
        $parentDriveId = $parentStmt->fetchColumn() ?: null;
    }
    if ($parentDriveId === null) {
        $rootStmt = \Core\Database::getInstance()->prepare('SELECT google_folder_id FROM artist_spaces WHERE id = :space_id LIMIT 1');
        $rootStmt->execute(['space_id' => $spaceId]);
        $parentDriveId = $rootStmt->fetchColumn() ?: null;
    }
    if ($parentDriveId === null) {
        Session::setFlash('error', 'Cette racine n’est pas encore reliée à Google Drive.');
        $this->redirect('/admin/artistes');
    }
    $driveId = (new GoogleDrive())->createFolder($name, $parentDriveId);
    $folderId = $this->artistSpace->createFolder((int)$spaceId, $name, $driveId, $parentId ?: null, (int)Session::get('user_id'));
    Session::setFlash('success', 'Le dossier artiste a été créé. Vous pouvez maintenant gérer ses accès.');
    $this->redirect('/admin/artistes');
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

