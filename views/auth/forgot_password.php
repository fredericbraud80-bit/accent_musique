<?php

use Core\Security;
?>

<div class="main-container">

    <!-- Carte Mot de passe oublié -->
    <div class="auth-card">

        <h2 class="auth-title">Mot de passe oublié</h2>

        <p style="text-align: center; color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem; line-height: 1.5;">
            Entrez votre adresse e-mail ci-dessous. Si un compte y est associé, nous vous enverrons un lien sécurisé pour réinitialiser votre mot de passe.
        </p>

        <!-- Message d'erreur -->
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Message de succès -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/forgot-password" method="POST">

            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">

            <!-- Email -->
            <div class="form-group">
                <label class="form-label">Adresse Email</label>
                <input type="email" name="email" class="form-control" placeholder="exemple@domaine.com" required autofocus>
            </div>

            <!-- Bouton -->
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">
                Envoyer le lien de réinitialisation
            </button>

        </form>

        <!-- Footer -->
        <p class="auth-footer">
            Vous vous souvenez de votre mot de passe ?
            <a href="<?= BASE_URL ?>/login" style="color: var(--primary);">
                Retour à la connexion
            </a>
        </p>

    </div>

</div>