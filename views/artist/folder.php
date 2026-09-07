<div class="main-container">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/artiste" class="page-subtitle">← Retour aux dossiers</a>
        <h1 class="page-title">📁 <?= \Core\Security::sanitize($folder['name']) ?></h1>
    </div>
    <?php if (empty($files)): ?>
        <div class="info-box"><p class="course-desc">Ce dossier ne contient aucun fichier.</p></div>
    <?php else: ?>
        <div class="info-box" style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <?php foreach ($files as $file): ?>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="course-desc"><?= \Core\Security::sanitize($file->getName()) ?></td>
                        <td style="text-align:right;">
                            <a class="btn btn-primary" href="<?= BASE_URL ?>/artiste/dossier/<?= $folder['id'] ?>/fichier/<?= rawurlencode($file->getId()) ?>">Ouvrir / télécharger</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</div>