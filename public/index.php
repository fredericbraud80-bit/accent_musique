<?php
require_once __DIR__ . '/../config/config.php';

set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require __DIR__ . '/../views/errors/500.php';
    exit;
});

set_error_handler(function (int $severity, string $message, string $file, int $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Session;
use Core\Router;

Session::start();

$router = new Router();

$router->get('/', [Controllers\LandingController::class, 'index']);
$router->get('/magasin', [Controllers\LandingController::class, 'magasin']);
$router->get('/app', [Controllers\LandingController::class, 'app']);
$router->get('/tarifs', [Controllers\LandingController::class, 'tarifs']);
$router->get('/contact', [Controllers\LandingController::class, 'contact']);
$router->get('/mentions-legales', [Controllers\LegalController::class, 'mentionsLegales']);
$router->get('/cgu', [Controllers\LegalController::class, 'cgu']);
$router->get('/CGU', [Controllers\LegalController::class, 'cgu']);

$router->get('/accueil', [Controllers\CategoryController::class, 'home'], [Middlewares\AuthMiddleware::class]);
$router->get('/categorie/{slug}', [Controllers\CategoryController::class, 'show'], [Middlewares\AuthMiddleware::class]);

$router->get('/login', [Controllers\AuthController::class, 'showLogin'], [Middlewares\GuestMiddleware::class]);
$router->post('/login', [Controllers\AuthController::class, 'login'], [Middlewares\GuestMiddleware::class]);
$router->get('/register', [Controllers\AuthController::class, 'showRegister'], [Middlewares\GuestMiddleware::class]);
$router->post('/register', [Controllers\AuthController::class, 'register'], [Middlewares\GuestMiddleware::class]);
$router->get('/forgot-password', [Controllers\AuthController::class, 'showForgotPassword'], [Middlewares\GuestMiddleware::class]);
$router->post('/forgot-password', [Controllers\AuthController::class, 'forgotPassword'], [Middlewares\GuestMiddleware::class]);
$router->get('/reset-password', [Controllers\AuthController::class, 'showResetPassword'], [Middlewares\GuestMiddleware::class]);
$router->post('/reset-password', [Controllers\AuthController::class, 'resetPassword'], [Middlewares\GuestMiddleware::class]);
$router->post('/logout', [Controllers\AuthController::class, 'logout'], [Middlewares\AuthMiddleware::class]);

$router->get('/courses', [Controllers\CourseController::class, 'index'], [Middlewares\AuthMiddleware::class]);
$router->get('/books/{id}', [Controllers\CourseController::class, 'show'], [Middlewares\AuthMiddleware::class]);
$router->get('/courses/{id}', [Controllers\CourseController::class, 'show'], [Middlewares\AuthMiddleware::class]);
$router->get('/favorites', [Controllers\CourseController::class, 'favorites'], [Middlewares\AuthMiddleware::class]);
$router->post('/favorites/{id}/toggle', [Controllers\CourseController::class, 'toggleFavorite'], [Middlewares\AuthMiddleware::class]);
$router->get('/files/{fileId}/preview', [Controllers\DownloadController::class, 'preview'], [Middlewares\AuthMiddleware::class]);
$router->get('/download/{fileId}', [Controllers\DownloadController::class, 'download'], [Middlewares\AuthMiddleware::class]);
$router->get('/artiste', [Controllers\ArtistController::class, 'index'], [Middlewares\AuthMiddleware::class]);
$router->get('/artiste/dossier/{id}', [Controllers\ArtistController::class, 'folder'], [Middlewares\AuthMiddleware::class]);
$router->get('/artiste/dossier/{folderId}/fichier/{fileId}', [Controllers\ArtistController::class, 'file'], [Middlewares\AuthMiddleware::class]);

$router->get('/admin', [Controllers\AdminController::class, 'dashboard'], [Middlewares\AdminMiddleware::class]);
$router->get('/admin/artistes', [Controllers\AdminController::class, 'artistManagement'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/validate/{id}', [Controllers\AdminController::class, 'validate'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/reject/{id}', [Controllers\AdminController::class, 'reject'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/promote/{id}', [Controllers\AdminController::class, 'promote'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/renewLicense/{id}', [Controllers\AdminController::class, 'renewLicense'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/users/{id}/spaces', [Controllers\AdminController::class, 'updateSpaces'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-folders/{id}/users', [Controllers\AdminController::class, 'updateArtistFolderUsers'], [Middlewares\AdminMiddleware::class]);
$router->get('/admin/artist-folders/{id}/users/search', [Controllers\AdminController::class, 'searchArtistFolderUsers'], [Middlewares\AdminMiddleware::class]);
$router->get('/admin/artist-spaces/{id}/users/search', [Controllers\AdminController::class, 'searchArtistSpaceUsers'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-folders/{id}/users/add', [Controllers\AdminController::class, 'addArtistFolderUser'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-spaces/{id}/users/add', [Controllers\AdminController::class, 'addArtistSpaceUser'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-folders/{id}/users/{userId}/remove', [Controllers\AdminController::class, 'removeArtistFolderUser'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-spaces/{id}/users/{userId}/remove', [Controllers\AdminController::class, 'removeArtistSpaceUser'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-folders/{id}/delete', [Controllers\AdminController::class, 'deleteArtistFolder'], [Middlewares\AdminMiddleware::class]);
$router->get('/admin/google-drive/connect', [Controllers\AdminController::class, 'connectGoogleDrive'], [Middlewares\AdminMiddleware::class]);
$router->get('/admin/google-drive/callback', [Controllers\AdminController::class, 'googleDriveCallback'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/google-drive/check', [Controllers\AdminController::class, 'checkGoogleDrive'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/google-drive/sync', [Controllers\AdminController::class, 'syncArtistSpaces'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-folders', [Controllers\AdminController::class, 'createArtistFolder'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-spaces', [Controllers\AdminController::class, 'createArtistSpace'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-spaces/{id}/drive', [Controllers\AdminController::class, 'linkArtistSpaceDrive'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/artist-folders/{id}/drive', [Controllers\AdminController::class, 'linkArtistFolderDrive'], [Middlewares\AdminMiddleware::class]);

$router->get('/admin/courses', [Controllers\AdminController::class, 'courses'], [Middlewares\AdminMiddleware::class]);
$router->get('/admin/courses/create', [Controllers\AdminController::class, 'createCourse'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/courses', [Controllers\AdminController::class, 'storeCourse'], [Middlewares\AdminMiddleware::class]);
$router->get('/admin/courses/edit/{id}', [Controllers\AdminController::class, 'editCourse'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/courses/{id}/update', [Controllers\AdminController::class, 'updateCourse'], [Middlewares\AdminMiddleware::class]);
$router->post('/admin/courses/{id}/delete', [Controllers\AdminController::class, 'deleteCourse'], [Middlewares\AdminMiddleware::class]);

$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
