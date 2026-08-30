<?php
namespace Core;

abstract class Controller {
    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../../views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException('Vue introuvable : ' . $view);
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../../views/layouts/' . $layout . '.php';
        if (is_file($layoutFile)) {
            require $layoutFile;
            return;
        }

        echo $content;
    }

    protected function redirect(string $path): void {
        $path = '/' . ltrim($path, '/');
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}