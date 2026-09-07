<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . APP_NAME : APP_NAME ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">

    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">

            <!-- Logo -->
            <a href="<?= BASE_URL ?>" class="nav-brand">
                ACCENT<span><span class="brand-sub">MUSIQUE</span>
            </a>

            <!-- Infos utilisateur -->
            <div class="nav-user-info">

                <?php if (\Core\Session::has('user_id')): ?>

                        <?php
                            $username = \Core\Security::sanitize(\Core\Session::get('user_name'));
                            $initial = strtoupper(substr($username, 0, 1));
                            $isAdmin = \Core\Session::get('user_role') === 'admin';
                            $hasStudentAccess = $isAdmin || \Core\Session::get('user_access_student') === true;
                            $hasArtistAccess = $isAdmin || \Core\Session::get('user_access_artist') === true;
                        ?>
                        <div class="user-avatar" id="user-menu-toggle">
                            <?= $initial ?>
                        </div>

                        <div class="user-dropdown" id="user-dropdown">
                            <?php if ($hasStudentAccess): ?>
                                <a href="<?= BASE_URL ?>/accueil">🏠 Catégories</a>
                                <a href="<?= BASE_URL ?>/favorites">⭐ Favoris</a>
                            <?php endif; ?>
                            <?php if ($hasArtistAccess && !$isAdmin): ?>
                                <a href="<?= BASE_URL ?>/artiste">🎙️ Espace artiste</a>
                            <?php endif; ?>

                            <form action="<?= BASE_URL ?>/logout" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <button type="submit">🚪 Déconnexion</button>
                            </form>
                        </div>


                    <?php if ($isAdmin): ?>
                        <a href="<?= BASE_URL ?>/admin" class="btn"
                           style="background:#a855f7; color:white;">
                            Admin
                        </a>
                    <?php endif; ?>
                    <button id="theme-toggle" class="btn btn-danger-outline" style="padding:0.3rem 0.8rem;">
                          🌙
                    </button>

                <?php else: ?>

                    <a href="<?= BASE_URL ?>/login" class="page-subtitle">
                        Connexion
                    </a>

                    <a href="<?= BASE_URL ?>/register" class="btn btn-primary">
                        S'inscrire
                    </a>

                <?php endif; ?>

            </div>
        </div>
    </nav>
<script src="<?= BASE_URL ?>/assets/js/theme.js"></script>

    <!-- CONTENU PRINCIPAL -->
    <main class="main-container">

        <!-- Messages Flash -->
        <?php if ($msg = \Core\Session::getFlash('error')): ?>
            <div class="alert alert-error"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($msg = \Core\Session::getFlash('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
