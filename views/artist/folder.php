<div class="main-container">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/artiste" class="page-subtitle">← Retour aux dossiers</a>
        <h1 class="page-title">📁 <?= \Core\Security::sanitize($folder['name']) ?></h1>
    </div>
    <?php if (empty($files)): ?>
        <div class="info-box"><p class="course-desc">Ce dossier ne contient aucun fichier.</p></div>
    <?php else: ?>
        <ul class="artist-folder-files">
            <?php foreach ($files as $file): ?>
                <li class="artist-file-item">
                    <span class="artist-file-name"><?= \Core\Security::sanitize($file->getName()) ?></span>
                    <a class="btn btn-primary artist-file-download"
                       href="<?= BASE_URL ?>/artiste/dossier/<?= $folder['id'] ?>/fichier/<?= rawurlencode($file->getId()) ?>"
                       download>Télécharger</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>