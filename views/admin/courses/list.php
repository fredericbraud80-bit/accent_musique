<?php
use Core\Security;
?>

<div class="main-container">

    <!-- En-tête -->
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1 class="page-title">📚 Gestion des cours</h1>
            <p class="page-subtitle">Liste des cours créés et actions disponibles</p>
        </div>

        <a href="<?= BASE_URL ?>/admin/courses/create" class="btn btn-primary">+ Créer un cours</a>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Aucun cours -->
    <?php if (empty($courses)): ?>
        <div class="info-box">
            <p class="course-desc">
                Aucun cours créé.
                <a href="<?= BASE_URL ?>/admin/courses/create" class="btn btn-primary" style="margin-top:1rem;">Créer le premier cours</a>
            </p>
        </div>

    <?php else: ?>

        <!-- Tableau -->
        <div class="info-box" style="overflow-x:auto;">

            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <th class="page-subtitle">Titre</th>
                        <th class="page-subtitle">Catégorie</th>
                        <th class="page-subtitle">Vidéo</th>
                        <th class="page-subtitle">Fichiers</th>
                        <th class="page-subtitle">Créé le</th>
                        <th class="page-subtitle">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td class="course-desc">
                                <strong><?= htmlspecialchars($course['title']); ?></strong>
                            </td>

                            <td class="course-desc">
                                <?= htmlspecialchars($course['category_name']); ?>
                            </td>

                            <td>
                                <?php if (!empty($course['youtube_url'])): ?>
                                    <span class="badge" style="border-color: var(--danger-border); color: var(--danger-text);">
                                        🎥 Oui
                                    </span>
                                <?php else: ?>
                                    <span class="badge">Non</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge">
                                    <?= $course['file_count'] ?? 0; ?> fichier(s)
                                </span>
                            </td>

                            <td class="page-subtitle">
                                <?= date('d/m/Y', strtotime($course['created_at'])); ?>
                            </td>

                            <td style="display:flex; gap:0.5rem;">
                                <a href="<?= BASE_URL ?>/admin/courses/edit/<?= $course['id']; ?>"
                                   class="btn btn-primary" style="font-size:0.75rem;">
                                    ✏️ Éditer
                                </a>

                                <form method="POST" action="<?= BASE_URL ?>/admin/courses/<?= $course['id']; ?>/delete"
                                      onsubmit="return confirm('Êtes-vous sûr ?');">
                                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                                    <button type="submit" class="btn btn-danger-outline" style="font-size:0.75rem;">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>

        </div>

    <?php endif; ?>

    <!-- Retour -->
    <div style="margin-top:2rem;">
        <a href="<?= BASE_URL ?>/admin" class="btn btn-danger-outline">← Retour au tableau de bord</a>
    </div>

</div>
