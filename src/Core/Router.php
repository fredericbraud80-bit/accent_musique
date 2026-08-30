<?php
namespace Core;

class Router {
    private array $routes = [];

    public function get(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array $handler, array $middlewares): void {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_\-]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(string $uri, string $method): void {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $scriptDir = rtrim($scriptDir, '/');
        if ($scriptDir !== '' && $scriptDir !== '.' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '//') {
            $uri = '/';
        }

        if ($method === 'POST') {
            $this->validatePostRequest();
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            foreach ($route['middlewares'] as $middleware) {
                (new $middleware())->handle();
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key) && $key !== '0') {
                    $params[] = $value;
                }
            }

            [$controllerClass, $action] = $route['handler'];
            $controller = new $controllerClass();

            if ($params) {
                $controller->$action(...$params);
            } else {
                $controller->$action();
            }

            return;
        }

        http_response_code(404);
        require __DIR__ . '/../../views/errors/404.php';
    }

    private function validatePostRequest(): void {
        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if ($contentLength > 0 && empty($_POST) && empty($_FILES)) {
            $postMaxSize = $this->parseBytes(ini_get('post_max_size'));
            if ($contentLength > $postMaxSize) {
                http_response_code(413);
                throw new \RuntimeException('La taille du fichier dépasse la limite autorisée.');
            }
        }

        if (!Security::verifyCsrfToken(is_string($token) ? $token : null)) {
            http_response_code(403);
            throw new \RuntimeException('Jeton CSRF invalide.');
        }
    }

    private function parseBytes(string $value): int {
        $value = trim($value);
        $unit = strtoupper(substr($value, -1));
        $num = (int) $value;

        $multipliers = ['K' => 1024, 'M' => 1024 ** 2, 'G' => 1024 ** 3];
        return isset($multipliers[$unit]) ? $num * $multipliers[$unit] : $num;
    }
}