<?php
use Core\Security;
?>

<div class="main-container">
    <div class="page-header">
        <h1 class="page-title">Mes cours favoris</h1>
        <p class="page-subtitle">Tous les cours que vous avez sélectionnés.</p>
    </div>

    <a href="<?= BASE_URL ?>/courses" class="back-link">
        ← Retour à la liste des cours
    </a>

    <?php if (empty($courses)): ?>
        <div class="info-box" style="text-align:center;">
            <p class="course-desc">Vous n'avez encore aucun cours en favoris.</p>
        </div>
    <?php else: ?>
        <div class="course-grid">
            <?php foreach ($courses as $course): ?>
                <article class="course-card">
                    <div>
                        <?php if (!empty($course['category_name'])): ?>
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
                                <button type="submit" class="favorite-toggle active" aria-label="Retirer des favoris">
                                    ★
                                </button>
                            </form>
                        </div>

                        <p class="course-desc">
                            <?= Security::sanitize($course['description'] ?? 'Aucune description disponible.') ?>
                        </p>
                    </div>

                    <div class="course-card-footer">
                        <?php
                            $courseUrl = (($course['category_slug'] ?? '') === 'livres')
                                ? BASE_URL . '/books/' . (int)$course['id']
                                : BASE_URL . '/courses/' . (int)$course['id'];
                        ?>
                        <a href="<?= $courseUrl ?>" class="course-link">
                            Consulter le cours →
                        </a>

                        <?php if (!empty($course['file_name'])): ?>
                            <span class="badge">📎 Fichier</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
