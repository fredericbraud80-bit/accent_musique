<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\GoogleDrive;
use Models\ArtistSpace;

class ArtistController extends Controller {
    private ArtistSpace $artistSpace;

    public function __construct() {
        $this->artistSpace = new ArtistSpace();
    }

    public function index(): void {
        $userId = (int)Session::get('user_id');
        if (!$this->artistSpace->hasSpace($userId, 'artist')) {
            http_response_code(403);
            require __DIR__ . '/../../views/errors/403.php';
            return;
        }
        $this->render('artist/index', ['folders' => $this->artistSpace->getFoldersForUser($userId)]);
    }

    public function folder(int $id): void {
        $userId = (int)Session::get('user_id');
        if (!$this->artistSpace->hasSpace($userId, 'artist') || !$this->artistSpace->userCanAccessFolder($userId, $id)) {
            http_response_code(403);
            require __DIR__ . '/../../views/errors/403.php';
            return;
        }
        $folder = null;
        foreach ($this->artistSpace->getFoldersForUser($userId) as $candidate) {
            if ((int)$candidate['id'] === $id) {
                $folder = $candidate;
                break;
            }
        }
        if (!$folder) {
            http_response_code(404);
            throw new \RuntimeException('Dossier introuvable.');
        }
        $files = (new GoogleDrive())->listFolder($folder['google_folder_id']);
        $this->render('artist/folder', ['folder' => $folder, 'files' => $files]);
    }

    public function file(int $folderId, string $fileId): void {
        $userId = (int)Session::get('user_id');
        if (!$this->artistSpace->userCanAccessFolder($userId, $folderId)) {
            http_response_code(403);
            return;
        }
        $folder = null;
        foreach ($this->artistSpace->getFoldersForUser($userId) as $candidate) {
            if ((int)$candidate['id'] === $folderId) {
                $folder = $candidate;
                break;
            }
        }
        if (!$folder) {
            http_response_code(404);
            return;
        }
        $isInSharedFolder = false;
        foreach ((new GoogleDrive())->listFolder($folder['google_folder_id']) as $file) {
            if ($file->getId() === $fileId) {
                $isInSharedFolder = true;
                break;
            }
        }
        if (!$isInSharedFolder) {
            http_response_code(404);
            return;
        }
        $download = (new GoogleDrive())->downloadFile($fileId);
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $download['mime_type']);
        header('Content-Disposition: attachment; filename="' . basename($download['name']) . '"');
        header('X-Content-Type-Options: nosniff');
        if (is_numeric($download['size'])) {
            header('Content-Length: ' . (int)$download['size']);
        }

        $body = $download['body'];
        while (!$body->eof()) {
            echo $body->read(8192);
        }
        exit;
    }
}