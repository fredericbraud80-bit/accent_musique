<?php
namespace Core;

use Google\Client;
use Google\Service\Drive;

class GoogleDrive {
    private const FOLDER_MIME_TYPE = 'application/vnd.google-apps.folder';

    private Client $client;

    public function __construct() {
        $this->client = new Client();
        $this->client->setApplicationName(APP_NAME);
        $this->client->setAuthConfig(\GOOGLE_CLIENT_SECRET_FILE);
        $this->client->setRedirectUri(\GOOGLE_REDIRECT_URI);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setScopes([Drive::DRIVE]);
    }

    public function authorizationUrl(): string {
        $state = bin2hex(random_bytes(32));
        Session::set('google_oauth_state', $state);
        $this->client->setState($state);
        return $this->client->createAuthUrl();
    }

    public function verifyOAuthState(?string $state): bool {
        $expectedState = Session::get('google_oauth_state');
        Session::remove('google_oauth_state');

        return is_string($expectedState)
            && is_string($state)
            && $expectedState !== ''
            && hash_equals($expectedState, $state);
    }

    public function exchangeCode(string $code): void {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw new \RuntimeException('La connexion Google Drive a échoué : ' . ($token['error_description'] ?? $token['error']));
        }
        $db = Database::getInstance();
        $previousRefreshToken = $db->query('SELECT refresh_token FROM google_drive_tokens WHERE id = 1')->fetchColumn() ?: null;
        $refreshToken = $token['refresh_token'] ?? $previousRefreshToken;
        $stmt = $db->prepare('REPLACE INTO google_drive_tokens (id, access_token, refresh_token, expires_at) VALUES (1, :access_token, :refresh_token, :expires_at)');
        $expiresAt = date('Y-m-d H:i:s', time() + (int)($token['expires_in'] ?? 3600));
        $stmt->execute([
            'access_token' => json_encode($token, JSON_THROW_ON_ERROR),
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
        ]);
    }

    private function service(): Drive {
        $stmt = Database::getInstance()->query('SELECT access_token FROM google_drive_tokens WHERE id = 1 LIMIT 1');
        $token = $stmt->fetchColumn();
        if (!$token) {
            throw new \RuntimeException('Google Drive n’est pas connecté.');
        }
        $this->client->setAccessToken(json_decode($token, true, 512, JSON_THROW_ON_ERROR));
        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken();
            if ($refreshToken) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                $newToken['refresh_token'] = $refreshToken;
                $db = Database::getInstance();
                $update = $db->prepare('UPDATE google_drive_tokens SET access_token = :access_token, refresh_token = :refresh_token, expires_at = :expires_at WHERE id = 1');
                $update->execute([
                    'access_token' => json_encode($newToken, JSON_THROW_ON_ERROR),
                    'refresh_token' => $refreshToken,
                    'expires_at' => date('Y-m-d H:i:s', time() + (int)($newToken['expires_in'] ?? 3600)),
                ]);
            }
        }
        return new Drive($this->client);
    }

    public function createFolder(string $name, ?string $parentId = null): string {
        $data = ['name' => $name, 'mimeType' => self::FOLDER_MIME_TYPE];
        if (($parentId === null || $parentId === '') && GOOGLE_DRIVE_ROOT_FOLDER_ID !== '') {
            $parentId = GOOGLE_DRIVE_ROOT_FOLDER_ID;
        }
        if ($parentId !== null && $parentId !== '') {
            $this->assertFolderAccessible($parentId);
            $data['parents'] = [$parentId];
        }
        $folder = new Drive\DriveFile($data);
        return $this->service()->files->create($folder, ['fields' => 'id'])->getId();
    }

    private function assertFolderAccessible(string $folderId): void {
        try {
            $file = $this->service()->files->get($folderId, ['fields' => 'id,name,mimeType,trashed']);
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Le dossier parent Google Drive est introuvable ou inaccessible pour le compte connecté. ' .
                'Vérifiez GOOGLE_DRIVE_ROOT_FOLDER_ID et le partage du dossier. (' . $exception->getMessage() . ')',
                0,
                $exception
            );
        }
        if ($file->getTrashed() || $file->getMimeType() !== self::FOLDER_MIME_TYPE) {
            throw new \RuntimeException('GOOGLE_DRIVE_ROOT_FOLDER_ID ne correspond pas à un dossier Drive actif.');
        }
    }

    public function listFolder(string $folderId): array {
        $response = $this->service()->files->listFiles([
            'q' => sprintf("'%s' in parents and trashed = false", addslashes($folderId)),
            'fields' => 'files(id,name,mimeType,size,webViewLink,webContentLink)',
            'orderBy' => 'folder,name',
        ]);
        return $response->getFiles();
    }

    public function listFolderContents(string $folderId): array {
        $response = $this->service()->files->listFiles([
            'q' => sprintf("'%s' in parents and trashed = false", addslashes($folderId)),
            'fields' => 'files(id,name,mimeType,size,modifiedTime,webViewLink,webContentLink)',
            'orderBy' => 'folder,name',
            'pageSize' => 1000,
        ]);
        return $response->getFiles();
    }

    public function listSubfolders(string $folderId): array {
        return array_values(array_filter(
            $this->listFolderContents($folderId),
            static fn ($file): bool => $file->getMimeType() === self::FOLDER_MIME_TYPE
        ));
    }

    public function getDownloadUrl(string $fileId): ?string {
        $file = $this->service()->files->get($fileId, ['fields' => 'webContentLink,webViewLink']);
        return $file->getWebContentLink() ?: $file->getWebViewLink();
    }

    public function deleteFolder(string $folderId): void {
        $this->service()->files->delete($folderId);
    }

    public function checkConnection(): array {
        $service = $this->service();
        $rootId = GOOGLE_DRIVE_ROOT_FOLDER_ID !== '' ? GOOGLE_DRIVE_ROOT_FOLDER_ID : 'root';
        if ($rootId !== 'root') {
            $this->assertFolderAccessible($rootId);
        }
        $response = $service->files->listFiles([
            'q' => sprintf("'%s' in parents and trashed = false and mimeType = '%s'", addslashes($rootId), self::FOLDER_MIME_TYPE),
            'pageSize' => 1,
            'fields' => 'files(id,name)',
        ]);
        $probeName = '.accent-musique-oauth-check-' . bin2hex(random_bytes(6));
        $probeId = $this->createFolder($probeName);
        $this->deleteFolder($probeId);
        return ['read' => true, 'write' => true, 'folders_seen' => count($response->getFiles())];
    }
}