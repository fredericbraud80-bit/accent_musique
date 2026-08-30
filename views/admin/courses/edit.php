<?php
use Core\Security;
?>

<div class="main-container">

    <!-- En-tête -->
    <div class="page-header">
        <h1 class="page-title">✏️ Éditer le cours</h1>
        <p class="page-subtitle">Modifiez les informations du cours existant</p>
    </div>

    <!-- Message d'erreur -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Carte du formulaire -->
    <div class="auth-card">

        <form method="POST" action="<?= BASE_URL ?>/admin/courses/<?= $course['id']; ?>/update">

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">

            <!-- Titre -->
            <div class="form-group">
                <label for="title" class="form-label">Titre du cours</label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?= htmlspecialchars($course['title']); ?>" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" required><?=
                    htmlspecialchars($course['description']);
                ?></textarea>
            </div>

            <!-- Catégorie -->
            <div class="form-group">
                <label for="category_id" class="form-label">Catégorie</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id']; ?>"
                            <?= $cat['id'] === $course['category_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- URL YouTube -->
            <div class="form-group">
                <label for="youtube_url" class="form-label">URL vidéo YouTube</label>
                <input type="url" id="youtube_url" name="youtube_url" class="form-control"
                       value="<?= htmlspecialchars($course['youtube_url'] ?? ''); ?>"
                       placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <!-- Fichier actuel -->
            <?php if (!empty($course['stored_name'])): ?>
                <div class="info-box" style="margin-bottom:1.5rem;">
                    <p class="course-desc">
                        <strong>📁 Fichier actuel :</strong> <?= htmlspecialchars($course['original_name']); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="form-group" id="book-links-wrapper" style="display: <?= (($course['category_slug'] ?? '') === 'livres' || ($course['category_id'] ?? 0) > 0) ? 'block' : 'none'; ?>;">
                <label class="form-label">🔗 Liens de page pour les livres</label>
                <p class="page-subtitle" style="margin-top:0.35rem; margin-bottom:0.75rem;">
                    Les liens apparaîtront sous forme de boutons sur la page du PDF indiquée.
                </p>
                <div id="book-links-container">
                    <?php if (!empty($courseLinks)): ?>
                        <?php foreach ($courseLinks as $link): ?>
                            <div class="book-link-row">
                                <div class="book-link-grid">
                                    <div>
                                        <label class="form-label">Nom du bouton</label>
                                        <input type="text" name="book_links[][name]" class="form-control" value="<?= htmlspecialchars($link['name']); ?>" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Page</label>
                                        <input type="number" name="book_links[][page]" class="form-control" min="1" value="<?= (int)$link['page_number']; ?>" required>
                                    </div>
                                    <div>
                                        <label class="form-label">URL</label>
                                        <input type="text" name="book_links[][url]" class="form-control" inputmode="url" value="<?= htmlspecialchars($link['url']); ?>" required>
                                    </div>
                                    <button type="button" class="btn btn-danger-outline remove-book-link" style="height:fit-content;">Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-book-link" class="btn btn-primary-small" style="margin-top:0.75rem;">+ Ajouter un lien</button>
            </div>

            <!-- Boutons -->
            <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">✓ Mettre à jour</button>
                <a href="<?= BASE_URL ?>/admin/courses" class="btn btn-danger-outline">Annuler</a>
            </div>

        </form>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('category_id');
        const bookLinksWrapper = document.getElementById('book-links-wrapper');
        const bookLinksContainer = document.getElementById('book-links-container');
        const addBookLinkBtn = document.getElementById('add-book-link');
        let bookLinkIndex = 0;

        function toggleBookLinks() {
            const selected = categorySelect && categorySelect.options[categorySelect.selectedIndex];
            const selectedText = selected ? selected.textContent.trim().toLowerCase() : '';
            const isBooksCategory = selectedText === 'livres' || selectedText === 'livre';
            if (bookLinksWrapper) {
                bookLinksWrapper.style.display = isBooksCategory ? 'block' : 'none';
            }
        }

        function createBookLinkRow() {
            const row = document.createElement('div');
            row.className = 'book-link-row';
            const index = bookLinkIndex++;
            row.innerHTML = `
                <div class="book-link-grid">
                    <div>
                        <label class="form-label">Nom du bouton</label>
                        <input type="text" name="book_links[${index}][name]" class="form-control" placeholder="Ex : Partie 1" required>
                    </div>
                    <div>
                        <label class="form-label">Page</label>
                        <input type="number" name="book_links[${index}][page]" class="form-control" min="1" placeholder="12" required>
                    </div>
                    <div>
                        <label class="form-label">URL</label>
                        <input type="text" name="book_links[${index}][url]" class="form-control" inputmode="url" placeholder="https://..." required>
                    </div>
                    <button type="button" class="btn btn-danger-outline remove-book-link" style="height:fit-content;">Supprimer</button>
                </div>
            `;

            const removeButton = row.querySelector('.remove-book-link');
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    row.remove();
                });
            }

            return row;
        }

        if (bookLinksContainer) {
            const existingRows = bookLinksContainer.querySelectorAll('.book-link-row');
            bookLinkIndex = existingRows.length;
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', toggleBookLinks);
            toggleBookLinks();
        }

        if (addBookLinkBtn) {
            addBookLinkBtn.addEventListener('click', function () {
                if (bookLinksContainer) {
                    bookLinksContainer.appendChild(createBookLinkRow());
                }
            });
        }

        document.querySelectorAll('.remove-book-link').forEach(function (button) {
            button.addEventListener('click', function () {
                const row = button.closest('.book-link-row');
                if (row) row.remove();
            });
        });
    });
</script>
