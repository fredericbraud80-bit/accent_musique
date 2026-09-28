<div class="main-container">

    <!-- En-tête -->
    <div class="page-header">
        <h1 class="page-title">Tableau de Bord Admin</h1>
        <p class="page-subtitle">Gérez les demandes d'inscription et les utilisateurs</p>
    </div>

    <!-- Statistiques -->
    <div class="course-grid">
        <div class="course-card">
            <div class="course-card-title"><?= $stats['total'] ?? 0 ?></div>
            <p class="course-desc">Utilisateurs total</p>
        </div>

        <div class="course-card">
            <div class="course-card-title" style="color: var(--primary);">
                <?= $stats['pending'] ?? 0 ?>
            </div>
            <p class="course-desc">En attente de validation</p>
        </div>

        <div class="course-card">
            <div class="course-card-title" style="color: var(--success-text);">
                <?= $stats['validated'] ?? 0 ?>
            </div>
            <p class="course-desc">Utilisateurs validés</p>
        </div>

        <div class="course-card">
            <div class="course-card-title" style="color: var(--primary);">
                <?= $coursesStats['count'] ?? 0 ?>
            </div>
            <p class="course-desc">Cours créés</p>
        </div>
    </div>

    <!-- Bouton de navigation -->
    <div style="margin: 2rem 0;">
        <a href="<?= BASE_URL ?>/admin/courses" class="btn btn-primary">📚 Gérer les cours</a>
        <a href="<?= BASE_URL ?>/admin/artistes" class="btn btn-primary">🎙️ Gérer les artistes</a>
        <a href="<?= BASE_URL ?>/admin/google-drive/connect" class="btn btn-primary">☁ Connecter Google Drive</a>
    </div>

    <div class="page-header">
        <h2 class="page-title" style="font-size: 1.5rem;">Espaces utilisateurs</h2>
    </div>

    <!-- Inscription d'un artiste par invitation -->
    <div class="page-header">
        <h2 class="page-title" style="font-size: 1.5rem;">🎙️ Inscrire un artiste (invitation)</h2>
    </div>

    <div class="info-box">
        <form method="POST" action="<?= BASE_URL ?>/admin/artists/invite" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
            <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <div style="flex:1; min-width:200px;">
                <label class="page-subtitle" for="invite-fullname" style="display:block; margin-bottom:0.25rem;">Nom et prénom</label>
                <input type="text" id="invite-fullname" name="fullname" required placeholder="Ex : Jean Dupont"
                       style="width:100%; padding:0.5rem; border:1px solid var(--border-color); border-radius:6px;">
            </div>
            <div style="flex:1; min-width:220px;">
                <label class="page-subtitle" for="invite-email" style="display:block; margin-bottom:0.25rem;">Email</label>
                <input type="email" id="invite-email" name="email" required placeholder="artiste@exemple.com"
                       style="width:100%; padding:0.5rem; border:1px solid var(--border-color); border-radius:6px;">
            </div>
            <button type="submit" class="btn btn-primary">✉ Inviter l'artiste</button>
        </form>
        <p class="page-subtitle" style="margin-top:0.75rem;">L'artiste est ajouté directement aux utilisateurs validés (artiste ✓, élève ✗) et reçoit un email l'invitant à accéder à ses enregistrements, mixages, arrangements et masters.</p>
    </div>

    <!-- Demandes en attente -->
    <div class="page-header">
        <h2 class="page-title" style="font-size: 1.5rem;">📋 Demandes d'Inscription en Attente</h2>
    </div>

    <?php if (empty($pendingUsers)): ?>
        <div class="info-box">
            <p class="course-desc">✓ Aucune demande en attente</p>
        </div>
    <?php else: ?>
        <?php foreach ($pendingUsers as $user): ?>
            <div class="info-box" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 class="course-card-title" style="font-size:1.1rem;">
                        <?= \Core\Security::sanitize($user['fullname']) ?>
                    </h3>
                    <p class="course-desc"><?= \Core\Security::sanitize($user['email']) ?></p>
                    <p class="page-subtitle" style="margin-top:0.5rem;">
                        Inscrit le <?= date('d/m/Y à H:i', strtotime($user['created_at'])) ?>
                    </p>
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <form method="POST" action="<?= BASE_URL ?>/admin/validate/<?= $user['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                        <button class="btn btn-primary">✓ Valider</button>
                    </form>

                    <form method="POST" action="<?= BASE_URL ?>/admin/reject/<?= $user['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                        <button class="btn btn-danger-outline" onclick="return confirm('Êtes-vous sûr ?')">
                            ✕ Rejeter
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Utilisateurs validés -->
    <div class="page-header">
        <h2 class="page-title" style="font-size: 1.5rem;">✓ Utilisateurs Validés</h2>
    </div>

    <?php if (empty($validatedUsers)): ?>
        <div class="info-box">
            <p class="course-desc">Aucun utilisateur validé pour l'instant</p>
        </div>
    <?php else: ?>
        <div class="info-box" style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <th class="page-subtitle">Nom</th>
                        <th class="page-subtitle">Email</th>
                        <th class="page-subtitle">Rôle</th>
                        <th class="page-subtitle">Espaces</th>
                        <th class="page-subtitle">Date</th>

                        <th class="page-subtitle">Licence</th>

                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($validatedUsers as $user): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td class="course-desc"><?= \Core\Security::sanitize($user['fullname']) ?></td>
                            <td class="course-desc"><?= \Core\Security::sanitize($user['email']) ?></td>

                            <td>
                                <span class="badge">
                                    <?= $user['role'] === 'admin' ? '👤 Admin' : '📚 Élève' ?>
                                </span>
                            </td>

                            <td>
                                <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/spaces">
                                    <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                    <?php $spaces = $user['spaces'] === '' ? [] : explode(',', $user['spaces']); ?>
                                    <label><input type="checkbox" name="spaces[]" value="student" <?= in_array('student', $spaces, true) ? 'checked' : '' ?>> Élève</label>
                                    <label><input type="checkbox" name="spaces[]" value="artist" <?= in_array('artist', $spaces, true) ? 'checked' : '' ?>> Artiste</label>
                                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                                </form>
                            </td>

                            <td class="page-subtitle"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>

                            <!-- Licence -->
                            <td>
                                <?php if ($user['is_license_active']): ?>
                                    <span class="badge" style="background: var(--success); color:white;">
                                        Active<?= !empty($user['license_expires_at']) ? ' jusqu’au ' . date('d/m/Y', strtotime($user['license_expires_at'])) : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--danger); color:white;">
                                        Expirée
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>


            </table>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h2 class="page-title" style="font-size: 1.5rem; color: var(--danger);">⚠ Licences Expirées</h2>
    </div>

    <?php if (empty($expiredLicenses)): ?>
        <div class="info-box">
            <p class="course-desc">Aucune licence expirée</p>
        </div>
    <?php else: ?>
        <div class="info-box" style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <th class="page-subtitle">Nom</th>
                        <th class="page-subtitle">Email</th>
                        <th class="page-subtitle">Expirée le</th>
                        <th class="page-subtitle">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expiredLicenses as $user): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td class="course-desc"><?= \Core\Security::sanitize($user['fullname']) ?></td>
                            <td class="course-desc"><?= \Core\Security::sanitize($user['email']) ?></td>
                            <td class="page-subtitle"><?= date('d/m/Y', strtotime($user['license_expires_at'])) ?></td>
                            <td>
                                <form method="POST" action="<?= BASE_URL ?>/admin/renewLicense/<?= $user['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                    <button class="btn btn-success">Renouveler</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Déconnexion -->
    <div style="text-align:center; margin-top:2rem;">
        <form action="<?= BASE_URL ?>/logout" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <button class="page-subtitle" style="background:none; border:none; cursor:pointer;">
                ← Retour à l'accueil
            </button>
        </form>
    </div>

</div>