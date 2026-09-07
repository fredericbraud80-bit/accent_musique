<div class="main-container">
    <div class="page-header">
        <h1 class="page-title">Espace artiste</h1>
        <p class="page-subtitle">Vos dossiers audio partagés</p>
    </div>
    <?php if (empty($folders)): ?>
        <div class="info-box"><p class="course-desc">Aucun dossier ne vous a encore été attribué.</p></div>
    <?php else: ?>
        <div class="course-grid">
            <?php foreach ($folders as $folder): ?>
                <a class="course-card" href="<?= BASE_URL ?>/artiste/dossier/<?= $folder['id'] ?>">
                    <div class="course-card-title">📁 <?= \Core\Security::sanitize($folder['name']) ?></div>
                    <p class="course-desc">Ouvrir le dossier</p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>