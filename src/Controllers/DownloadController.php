<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Models\File;

class DownloadController extends Controller {
    private function requireAuth(): void {
        if (!Session::has('user_id')) {
            http_response_code(403);
            throw new \RuntimeException('Accès refusé. Authentification requise.');
        }
    }

    private function resolveFile(int $fileId): ?array {
        $fileModel = new File();
        $file = $fileModel->findLinkedToCourse($fileId);

        if (!$file) {
            return null;
        }

        $storagePath = rtrim(STORAGE_PATH, "/\\") . DIRECTORY_SEPARATOR;
        $baseDir = realpath($storagePath);
        $fullPath = realpath($storagePath . ltrim((string)$file['stored_name'], "/\\"));

        if ($baseDir === false || $fullPath === false) {
            return null;
        }

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strncmp($fullPath, $baseDir, strlen($baseDir)) !== 0) {
            return null;
        }

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'audio/mpeg',
            'video/mp4'
        ];

        $mimeType = function_exists('mime_content_type') ? mime_content_type($fullPath) : null;
        if ($this->isPdfFile($fullPath)) {
            $mimeType = 'application/pdf';
        }
        if (!is_string($mimeType) || !in_array($mimeType, $allowedMimeTypes, true)) {
            return null;
        }

        $file['path'] = $fullPath;
        $file['mime_type'] = $mimeType;
        return $file;
    }

    private function isPdfFile(string $path): bool {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        $signature = fread($handle, 5);
        fclose($handle);

        return $signature === '%PDF-';
    }

    private function safeDownloadName(string $originalName): string {
        $name = basename($originalName);
        $name = preg_replace('/[\x00-\x1F\x7F"\\\\]/', '_', $name) ?: 'download';

        return $name;
    }

    public function download(int $fileId): void {
        $this->requireAuth();

        $file = $this->resolveFile($fileId);
        if (!$file) {
            http_response_code(404);
            throw new \RuntimeException('Fichier introuvable.');
        }

        $handle = @fopen($file['path'], 'rb');
        if ($handle === false) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            http_response_code(500);
            throw new \RuntimeException('Le fichier existe mais ne peut pas être lu par le serveur.');
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $this->safeDownloadName((string)$file['original_name']) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        fpassthru($handle);
        fclose($handle);
        exit;
    }

    public function preview(int $fileId): void {
        $this->requireAuth();

        $file = $this->resolveFile($fileId);
        if (!$file) {
            http_response_code(404);
            throw new \RuntimeException('Fichier introuvable.');
        }

        $handle = @fopen($file['path'], 'rb');
        if ($handle === false) {
            http_response_code(500);
            throw new \RuntimeException('Le fichier existe mais ne peut pas être lu par le serveur.');
        }

        $fileSize = filesize($file['path']);

        if (ob_get_level()) {
            ob_end_clean();
        }

        // ETag basé sur chemin + taille : validation côté navigateur
        $etag = '"' . md5($file['path'] . '|' . $fileSize) . '"';

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: inline; filename="' . $this->safeDownloadName((string)$file['original_name']) . '"');
        header('Accept-Ranges: bytes');
        header('ETag: ' . $etag);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', (int)filemtime($file['path'])) . ' GMT');
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');

        // 304 Not Modified : le navigateur ne retélécharge pas les 50 Mo
        // s'il possède déjà le document (rechargement de la page du livre)
        $clientEtag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : null;
        if ($clientEtag !== null && str_contains($clientEtag, $etag)) {
            fclose($handle);
            http_response_code(304);
            exit;
        }

        // Requête par plage (HTTP Range) : pdf.js en profite pour charger
        // le PDF progressivement au lieu du fichier complet d'un bloc
        $rangeHeader = isset($_SERVER['HTTP_RANGE']) ? trim($_SERVER['HTTP_RANGE']) : null;
        if ($rangeHeader !== null && preg_match('/^bytes=(\d*)-(\d*)$/', $rangeHeader, $m) && ($m[1] !== '' || $m[2] !== '')) {
            $start = $m[1] !== '' ? (int)$m[1] : max(0, $fileSize - (int)$m[2]);
            $end = $m[2] !== '' ? min((int)$m[2], $fileSize - 1) : $fileSize - 1;

            if ($start <= $end && $start < $fileSize) {
                fseek($handle, $start);
                $length = $end - $start + 1;

                http_response_code(206);
                header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
                header('Content-Length: ' . $length);

                $remaining = $length;
                while ($remaining > 0 && !feof($handle)) {
                    $chunk = fread($handle, min(8192, $remaining));
                    if ($chunk === false || $chunk === '') {
                        break;
                    }
                    $remaining -= strlen($chunk);
                    echo $chunk;
                    flush();
                }
                fclose($handle);
                exit;
            }

            // Plage invalide
            fclose($handle);
            http_response_code(416);
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        header('Content-Length: ' . $fileSize);

        fpassthru($handle);
        fclose($handle);
        exit;
    }
}