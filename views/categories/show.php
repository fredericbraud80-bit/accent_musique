<?php
use Core\Security;
?>

<div class="main-container">

    <!-- En-tête -->
    <div class="page-header">
        <h1 class="page-title">
            <?php
                $icons = [
                    'cours' => '📚',
                    'harmonie' => '🎵',
                    'composition' => '✍️',
                    'mixage' => '🎚️',
                    'livres' => '📖',
                    'reparations' => '🔧'
                ];
                echo ($icons[$category['slug']] ?? '📚') . ' ' . Security::sanitize($category['name']);
            ?>
        </h1>
        <p class="page-subtitle">Explorez les ressources de cette catégorie</p>
    </div>

    <!-- Barre de recherche -->
    <div class="info-box">

        <form method="GET">

            <!-- Recherche -->
            <div class="form-group">
                <label class="form-label">Rechercher</label>
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Titre ou description..."
                    value="<?= htmlspecialchars($search ?? '') ?>"
                    class="form-control"
                >
            </div>

            <!-- Filtres par tags -->
            <?php if (!empty($availableTags)): ?>
                <div class="form-group">
                    <label class="form-label">Filtrer par tags</label>

                    <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                        <?php foreach ($availableTags as $tag): ?>
                            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                                <input 
                                    type="checkbox" 
                                    name="tags[]" 
                                    value="<?= $tag['id'] ?>"
                                    <?= in_array($tag['id'], $selectedTags ?? []) ? 'checked' : '' ?>
                                    style="width:16px; height:16px;"
                                >
                                <span class="badge">
                                    <?= Security::sanitize($tag['name']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Boutons -->
            <div style="display:flex; gap:1rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Rechercher</button>

                <a href="<?= BASE_URL ?>/categorie/<?= $category['slug'] ?>" 
                   class="btn btn-danger-outline">
                    Réinitialiser
                </a>
            </div>

        </form>

    </div>

    <!-- Liste des cours -->
    <?php if (empty($courses)): ?>

        <div class="info-box" style="text-align:center;">
            <p class="course-desc">Aucun cours trouvé</p>
        </div>

    <?php else: ?>

        <div class="course-grid">

            <?php foreach ($courses as $course): ?>

                <article class="course-card">

                    <div>

                        <!-- Catégorie -->
                        <span class="badge" style="color:var(--primary); border-color:var(--primary-hover);">
                            <?= Security::sanitize($course['category_name']) ?>
                        </span>

                        <div class="course-title-row">
                            <h2 class="course-card-title" style="margin-top:0.5rem; margin-bottom:0;">
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
                        <p class="course-desc" style="margin-bottom:1rem;">
                            <?= Security::sanitize($course['description'] ?? 'Aucune description disponible.') ?>
                        </p>

                        <!-- Tags -->
                        <?php if (!empty($course['tags'])): ?>
                            <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:1rem;">
                                <?php foreach ($course['tags'] as $tag): ?>
                                    <span class="badge">
                                        <?= Security::sanitize($tag['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Footer -->
                    <div style="
                        padding-top:1rem;
                        border-top:1px solid var(--border-color);
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        margin-top:1rem;
                    ">
                        <?php
                            $courseUrl = (($category['slug'] ?? '') === 'livres')
                                ? BASE_URL . '/books/' . (int)$course['id']
                                : BASE_URL . '/courses/' . (int)$course['id'];
                        ?>
                        <a href="<?= $courseUrl ?>" 
                           class="course-desc" 
                           style="color:var(--primary); font-weight:600;">
                            Consulter →
                        </a>

                        <?php if ($course['file_name']): ?>
                            <span class="badge">📎 Fichier</span>
                        <?php endif; ?>
                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <!-- Bouton retour -->
    <div style="text-align:center; margin-top:2rem;">
        <a href="<?= BASE_URL ?>/accueil" class="page-subtitle" style="cursor:pointer;">
            ← Retour aux catégories
        </a>
    </div>

</div>
