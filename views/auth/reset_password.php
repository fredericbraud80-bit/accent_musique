<?php

use Core\Security;
?>

<div class="main-container">

    <!-- Carte Réinitialisation mot de passe -->
    <div class="auth-card">

        <h2 class="auth-title">Nouveau mot de passe</h2>

        <p style="text-align: center; color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem; line-height: 1.5;">
            Choisissez un nouveau mot de passe sécurisé d'au moins 12 caractères pour votre compte.
        </p>

        <!-- Message d'erreur -->
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/reset-password" method="POST">

            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <!-- Nouveau mot de passe -->
            <div class="form-group">
                <label class="form-label">Nouveau mot de passe (12 caractères min.)</label>
                <input type="password" name="password" class="form-control" minlength="12" required autofocus>
            </div>

            <!-- Confirmation -->
            <div class="form-group">
                <label class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" name="password_confirm" class="form-control" minlength="12" required>
            </div>

            <!-- Bouton -->
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">
                Mettre à jour mon mot de passe
            </button>

        </form>

        <!-- Footer -->
        <p class="auth-footer">
            <a href="<?= BASE_URL ?>/login" style="color: var(--primary);">
                Retour à la page de connexion
            </a>
        </p>

    </div>

</div>