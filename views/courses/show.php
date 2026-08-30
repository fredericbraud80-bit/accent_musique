<?php
use Core\Security;
?>

<div class="main-container course-detail">

    <!-- Retour -->
    <a href="<?= BASE_URL ?>/courses" class="back-link">
        ← Retour à la liste des cours
    </a>

    <!-- En-tête -->
    <header class="course-header">

        <?php if ($course['category_name']): ?>
            <span class="badge badge-primary">
                <?= Security::sanitize($course['category_name']) ?>
            </span>
        <?php endif; ?>

        <div class="course-title-row detail-title-row">
            <h1 class="page-title">
                <?= Security::sanitize($course['title']) ?>
            </h1>

            <form method="POST" action="<?= BASE_URL ?>/favorites/<?= $course['id'] ?>/toggle" class="favorite-form">
                <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                <button type="submit" class="favorite-toggle <?= !empty($course['is_favorite']) ? 'active' : '' ?>" aria-label="Mettre en favoris">
                    <?= !empty($course['is_favorite']) ? '★' : '☆' ?>
                </button>
            </form>
        </div>

        <?php if ($course['author']): ?>
            <p class="page-subtitle course-author">
                Auteur / Professeur :
                <strong class="author-name">
                    <?= Security::sanitize($course['author']) ?>
                </strong>
            </p>
        <?php endif; ?>

    </header>

    <!-- Vidéo -->
    <?php if (!empty($course['youtube_embed'])): ?>
        <section class="video-container">
            <iframe 
                src="https://www.youtube.com/embed/<?= $course['youtube_embed'] ?>" 
                title="Support vidéo" 
                allowfullscreen>
            </iframe>
        </section>
    <?php endif; ?>

    <!-- Description -->
    <?php if (!empty($course['description'])): ?>
        <section class="info-box">
            <h2 class="course-card-title info-title">Explications du cours</h2>
            <p class="course-desc course-description">
                <?= Security::sanitize($course['description']) ?>
            </p>
        </section>
    <?php endif; ?>

    <!-- Fichier -->
    <?php if ($course['file_id']): ?>
        <section class="download-box">

            <div>
                <h3 class="course-card-title">Support de cours (Partition / Tablature)</h3>
                <p class="page-subtitle file-name">
                    <?= Security::sanitize($course['original_name']) ?>
                </p>
            </div>

            <a href="<?= BASE_URL ?>/download/<?= $course['file_id'] ?>" 
               class="btn btn-primary download-btn">
                Télécharger
            </a>

        </section>
    <?php endif; ?>

</div>
