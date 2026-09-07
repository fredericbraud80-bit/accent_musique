<?php
/** @var array $users */
/** @var array $artistFolders */
/** @var array $artistSpaces */
/** @var array $driveContentsBySpace */
/** @var bool $driveConnected */
?>
<div class="main-container">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/admin" class="page-subtitle">← Retour au dashboard</a>
        <h1 class="page-title">Gestion artiste</h1>
        <p class="page-subtitle">Attribuez les espaces et contrôlez les dossiers partagés.</p>
    </div>

    <div class="course-grid">
        <div class="course-card">
            <div class="course-card-title"><?= count($users) ?></div>
            <p class="course-desc">Utilisateurs validés</p>
        </div>
        <div class="course-card">
            <div class="course-card-title"><?= count($artistFolders) ?></div>
            <p class="course-desc">Dossiers artistes</p>
        </div>
        <div class="course-card">
            <div class="course-card-title" style="color: <?= $driveConnected ? 'var(--success-text)' : 'var(--danger)' ?>;">
                <?= $driveConnected ? 'Connecté' : 'Non connecté' ?>
            </div>
            <p class="course-desc">Google Drive</p>
        </div>
    </div>

    <div class="artist-toolbar">
        <form method="POST" action="<?= BASE_URL ?>/admin/google-drive/sync">
            <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <button class="btn btn-primary" type="submit">Synchroniser Google Drive</button>
        </form>
        <form method="POST" action="<?= BASE_URL ?>/admin/google-drive/check">
            <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <button class="btn btn-secondary" type="submit">Vérifier Google Drive</button>
        </form>
        <form method="POST" action="<?= BASE_URL ?>/admin/artist-spaces" class="artist-root-form">
            <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <input class="form-control" type="text" name="name" placeholder="Nom du groupe ou artiste" required>
            <button class="btn btn-primary" type="submit">Créer la racine Drive</button>
        </form>
    </div>

    <div class="page-header">
        <h2 class="page-title" style="font-size:1.5rem;">Ajouter à l’arborescence</h2>
    </div>
    <div class="info-box">
        <?php if (!$driveConnected): ?>
            <p class="course-desc">Google Drive doit être connecté avant de créer un dossier.</p>
            <a href="<?= BASE_URL ?>/admin/google-drive/connect" class="btn btn-primary">☁ Connecter Google Drive</a>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/artist-folders">
                <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                <div class="form-group">
                    <label class="form-label" for="artist-folder-name">Nom du dossier ou sous-dossier</label>
                    <input class="form-control" id="artist-folder-name" type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="artist-space-id">Groupe ou artiste</label>
                    <select class="form-control" id="artist-space-id" name="space_id" required>
                        <option value="">Choisir un groupe ou artiste</option>
                        <?php foreach ($artistSpaces as $space): ?>
                            <option value="<?= $space['id'] ?>"><?= \Core\Security::sanitize($space['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="artist-parent-id">Dossier parent (facultatif)</label>
                    <select class="form-control" id="artist-parent-id" name="parent_id">
                        <option value="">Racine du groupe</option>
                        <?php foreach ($artistFolders as $parentFolder): ?>
                            <option value="<?= $parentFolder['id'] ?>"><?= \Core\Security::sanitize($parentFolder['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Créer le dossier</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="page-header">
        <h2 class="page-title" style="font-size:1.5rem;">Arborescence artiste</h2>
    </div>
    <?php if (empty($artistFolders) && empty($artistSpaces)): ?>
        <div class="info-box"><p class="course-desc">Aucun dossier artiste.</p></div>
    <?php else: ?>
        <?php
        $foldersByParent = [];
        foreach ($artistFolders as $folder) {
            $foldersByParent[$folder['space_id'] . ':' . ($folder['parent_id'] ?? 0)][] = $folder;
        }
        $renderFolderTree = function (int $spaceId, ?int $parentId) use (&$renderFolderTree, $foldersByParent, $users): void {
            $key = $spaceId . ':' . ($parentId ?? 0);
            foreach ($foldersByParent[$key] ?? [] as $folder):
                $childrenKey = $folder['space_id'] . ':' . $folder['id'];
                $hasChildren = !empty($foldersByParent[$childrenKey]);
        ?>
                <details class="artist-tree-node">
                    <summary><span class="artist-tree-toggle">+</span> 📁 <?= \Core\Security::sanitize($folder['name']) ?></summary>
                    <div class="artist-tree-content">
                        <div class="artist-folder-actions">
                            <strong><?= (int)$folder['assigned_count'] ?> accès</strong>
                            <form method="POST" action="<?= BASE_URL ?>/admin/artist-folders/<?= $folder['id'] ?>/delete" onsubmit="return confirm('Supprimer ce dossier et ses sous-dossiers ?')">
                                <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <button class="btn btn-danger-outline" type="submit">Supprimer</button>
                            </form>
                        </div>
                        <?php if (!empty($folder['drive_error'])): ?>
                            <p class="artist-warning"><?= \Core\Security::sanitize($folder['drive_error']) ?></p>
                        <?php elseif (empty($folder['google_folder_id'])): ?>
                            <p class="course-desc">Ce dossier n’est pas encore relié à Google Drive.</p>
                            <form method="POST" action="<?= BASE_URL ?>/admin/artist-folders/<?= $folder['id'] ?>/drive" class="artist-link-form">
                                <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <input class="form-control" type="text" name="google_folder_id" placeholder="ID du dossier Google Drive" required>
                                <button class="btn btn-primary" type="submit">Associer ce dossier</button>
                            </form>
                        <?php elseif (empty($folder['drive_contents'])): ?>
                            <p class="course-desc">Ce dossier Drive est vide.</p>
                        <?php else: ?>
                            <div class="artist-drive-contents">
                                <span class="form-label">Contenu du dossier Drive</span>
                                <?php foreach ($folder['drive_contents'] as $driveItem): ?>
                                    <?php $driveUrl = $driveItem->getWebViewLink() ?: $driveItem->getWebContentLink() ?: '#'; ?>
                                    <a class="artist-drive-item" href="<?= \Core\Security::sanitize($driveUrl) ?>" target="_blank" rel="noopener">
                                        <span><?= $driveItem->getMimeType() === 'application/vnd.google-apps.folder' ? '📁' : '🎵' ?> <?= \Core\Security::sanitize($driveItem->getName()) ?></span>
                                        <small><?= $driveItem->getMimeType() === 'application/vnd.google-apps.folder' ? 'Dossier' : 'Fichier' ?></small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($hasChildren): ?>
                            <div class="artist-tree-children">
                                <?php $renderFolderTree((int)$folder['space_id'], (int)$folder['id']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>
        <?php endforeach;
        };
        ?>
        <div class="artist-tree">
            <?php foreach ($artistSpaces as $space): ?>
                <details class="artist-tree-root">
                    <summary><span class="artist-tree-toggle">+</span> 🎙️ <?= \Core\Security::sanitize($space['name']) ?>
                        <small class="artist-root-status"><?= $space['google_folder_id'] ? 'Drive relié' : 'Drive non relié' ?></small>
                    </summary>
                    <div class="artist-tree-content">
                        <?php if (!$space['google_folder_id']): ?>
                            <p class="artist-warning">Cette racine n’est pas reliée à Google Drive. Crée une nouvelle racine ou renseigne son ID Drive avant d’ajouter des dossiers.</p>
                            <form method="POST" action="<?= BASE_URL ?>/admin/artist-spaces/<?= $space['id'] ?>/drive" class="artist-link-form">
                                <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <input class="form-control" type="text" name="google_folder_id" placeholder="ID du dossier racine Google Drive" required>
                                <button class="btn btn-primary" type="submit">Associer la racine</button>
                            </form>
                        <?php endif; ?>
                                                <?php $rootContents = $driveContentsBySpace[(int)$space['id']] ?? []; ?>
                                                <?php if ($space['google_folder_id'] && empty($rootContents)): ?>
                                                    <p class="course-desc">La racine Drive est vide.</p>
                                                <?php elseif (!empty($rootContents)): ?>
                                                    <div class="artist-drive-contents">
                                                        <span class="form-label">Contenu Drive de la racine</span>
                                                        <?php foreach ($rootContents as $driveItem): ?>
                                                            <a class="artist-drive-item" href="<?= \Core\Security::sanitize($driveItem->getWebViewLink() ?: '#') ?>" target="_blank" rel="noopener">
                                                                <span><?= $driveItem->getMimeType() === 'application/vnd.google-apps.folder' ? '📁' : '🎵' ?> <?= \Core\Security::sanitize($driveItem->getName()) ?></span>
                                                                <small><?= $driveItem->getMimeType() === 'application/vnd.google-apps.folder' ? 'Dossier' : 'Fichier' ?></small>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="artist-space-access">
                                                    <span class="form-label">Utilisateurs autorisés sur toute la racine</span>
                                                    <?php if (empty($space['users'])): ?>
                                                        <p class="course-desc">Aucun utilisateur autorisé.</p>
                                                    <?php else: ?>
                                                        <?php foreach ($space['users'] as $spaceUser): ?>
                                                            <div class="artist-user-access">
                                                                <span><?= \Core\Security::sanitize($spaceUser['fullname']) ?> <small><?= \Core\Security::sanitize($spaceUser['email']) ?></small></span>
                                                                <form method="POST" action="<?= BASE_URL ?>/admin/artist-spaces/<?= $space['id'] ?>/users/<?= $spaceUser['id'] ?>/remove">
                                                                    <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                                                    <button class="btn btn-danger-outline" type="submit">Retirer</button>
                                                                </form>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                    <div class="artist-user-search" data-space-id="<?= $space['id'] ?>">
                                                        <label class="form-label" for="space-user-search-<?= $space['id'] ?>">Ajouter un utilisateur à la racine</label>
                                                        <input class="form-control artist-user-search-input" id="space-user-search-<?= $space['id'] ?>" type="search" placeholder="Rechercher par nom ou email" autocomplete="off">
                                                        <div class="artist-user-search-results"></div>
                                                    </div>
                                                </div>
                        <?php $renderFolderTree((int)$space['id'], null); ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.artist-user-search').forEach(function (searchBox) {
    const input = searchBox.querySelector('.artist-user-search-input');
    const results = searchBox.querySelector('.artist-user-search-results');
    const folderId = searchBox.dataset.folderId;
    const spaceId = searchBox.dataset.spaceId;
    const targetType = spaceId ? 'space' : 'folder';
    const targetId = spaceId || folderId;
    let timer;

    input.addEventListener('input', function () {
        window.clearTimeout(timer);
        const query = input.value.trim();
        results.innerHTML = '';
        if (query.length < 2) return;
        timer = window.setTimeout(function () {
            fetch('<?= BASE_URL ?>/admin/artist-' + targetType + 's/' + targetId + '/users/search?q=' + encodeURIComponent(query))
                .then(function (response) { return response.json(); })
                .then(function (users) {
                    results.innerHTML = '';
                    if (!users.length) {
                        results.innerHTML = '<p class="course-desc">Aucun utilisateur trouvé.</p>';
                        return;
                    }
                    users.forEach(function (user) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '<?= BASE_URL ?>/admin/artist-' + targetType + 's/' + targetId + '/users/add';
                        form.className = 'artist-user-result';
                        form.innerHTML = '<input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">' +
                            '<input type="hidden" name="user_id" value="' + user.id + '">' +
                            '<span>' + user.fullname + ' <small>' + user.email + '</small></span>' +
                            '<button class="btn btn-primary" type="submit">Ajouter</button>';
                        results.appendChild(form);
                    });
                });
        }, 250);
    });
});
</script>