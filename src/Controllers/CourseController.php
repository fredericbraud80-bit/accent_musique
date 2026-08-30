<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Models\Course;

class CourseController extends Controller {
    private Course $courseModel;

    public function __construct() {
        $this->courseModel = new Course();
    }

    public function index(): void {
        $courses = $this->courseModel->getAllWithDetails();
        $favoriteIds = $this->courseModel->getFavoriteCourseIds((int) Session::get('user_id'));

        foreach ($courses as &$course) {
            $course['is_favorite'] = in_array((int) $course['id'], $favoriteIds, true);
        }

        $this->render('courses/index', ['courses' => $courses]);
    }

    public function show(int $id): void {
        $course = $this->courseModel->findWithDetails($id);

        if (!$course) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $course['is_favorite'] = $this->courseModel->isFavorite((int) Session::get('user_id'), $id);

        if (!empty($course['youtube_url'])) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.*\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $course['youtube_url'], $matches);
            $course['youtube_embed'] = $matches[1] ?? null;
        }

        if (($course['category_slug'] ?? '') === 'livres') {
            $course['links'] = $this->courseModel->getCourseLinks($id);
            $this->render('books/reader', ['course' => $course]);
            return;
        }

        $this->render('courses/show', ['course' => $course]);
    }

    public function favorites(): void {
        $userId = (int) Session::get('user_id');
        $courses = $this->courseModel->getFavoritesByUser($userId);

        foreach ($courses as &$course) {
            $course['is_favorite'] = true;
        }

        $this->render('favorites/index', ['courses' => $courses]);
    }

    public function toggleFavorite(int $id): void {
        $userId = (int) Session::get('user_id');
        $courseId = filter_var($id, FILTER_VALIDATE_INT);

        if ($userId <= 0 || $courseId === false) {
            http_response_code(400);
            exit;
        }

        $this->courseModel->toggleFavorite($userId, $courseId);

        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referrer !== '') {
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
            $basePath = rtrim($basePath, '/');

            $refPath = parse_url($referrer, PHP_URL_PATH) ?? '';
            if ($basePath !== '' && str_starts_with($refPath, $basePath)) {
                $refPath = substr($refPath, strlen($basePath));
            }

            $refQuery = parse_url($referrer, PHP_URL_QUERY);
            $target = '/' . ltrim($refPath, '/');
            if ($refQuery) {
                $target .= '?' . $refQuery;
            }

            if ($target !== '/') {
                $this->redirect($target);
                return;
            }
        }

        $this->redirect('/courses');
    }
}