<?php

namespace Controllers;

class LandingController
{
    public function index(): void
    {
        require __DIR__ . '/../../views/landing/landing.php';
    }

    public function magasin(): void
    {
        header('Location: ' . BASE_URL . '/#magasin');
        exit;
    }

    public function app(): void
    {
        header('Location: ' . BASE_URL . '/#app');
        exit;
    }

    public function tarifs(): void
    {
        header('Location: ' . BASE_URL . '/#tarifs');
        exit;
    }

    public function contact(): void
    {
        header('Location: ' . BASE_URL . '/#contact');
        exit;
    }
}
