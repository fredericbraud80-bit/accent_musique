<?php
use Core\Security;
?>

<div class="main-container">

    <!-- En-tête -->
    <div class="page-header">
        <h1 class="page-title">Tous nos cours</h1>
        <p class="page-subtitle">Accédez à vos partitions, supports PDF et exercices vidéo.</p>
    </div>
    <!-- Retour -->
    <a href="<?= BASE_URL ?>/accueil" class="back-link">
        ← Retour aux catégories.
    </a>

    <!-- Grille des cours -->
    <div class="course-grid">

        <?php foreach ($courses as $course): ?>

            <article class="course-card">

                <div>

                    <!-- Catégorie -->
                    <?php if ($course['category_name']): ?>
                        <span class="badge badge-primary">
                            <?= Security::sanitize($course['category_name']) ?>
                        </span>
                    <?php endif; ?>

                    <div class="course-title-row">
                        <h2 class="course-card-title">
                            <?= Security::sanitize($course['title']) ?>
                        </h2>

                        <form method="POST" action="<?= BASE_URL ?>/favorites/<?= $course['id'] ?>/toggle" class="favorite-form">
                            <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                            <button type="submit" class="favorite-toggle <?= !empty($course['is_favorite']) ? 'active' : '' ?>" aria-label="Mettre en favoris">
                                <?= !empty($course['is_favorite']) ? '★' : '☆' ?>
                            </button>
                        </form>
                    </div>

                    <!-- Description -->
                    <p class="course-desc">
                        <?= Security::sanitize($course['description'] ?? 'Aucune description disponible.') ?>
                    </p>

                </div>

                <!-- Footer -->
                <div class="course-card-footer">
                    <a href="<?= BASE_URL ?>/courses/<?= $course['id'] ?>" 
                       class="course-link">
                        Consulter le cours →
                    </a>

                    <?php if ($course['file_id']): ?>
                        <span class="badge">📎 Partition incluse</span>
                    <?php endif; ?>
                </div>

            </article>

        <?php endforeach; ?>

    </div>

</div>
