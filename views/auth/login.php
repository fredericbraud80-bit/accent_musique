<?php
use Core\Security;
?>

<div class="main-container">

    <!-- Carte de connexion -->
    <div class="auth-card">

        <h2 class="auth-title">Espace Élève</h2>

        <!-- Message d'erreur -->
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/login" method="POST">

            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">

            <!-- Email -->
            <div class="form-group">
                <label class="form-label">Adresse Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label class="form-label" style="margin-bottom: 0;">Mot de passe</label>
                    <a href="<?= BASE_URL ?>/forgot-password" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">
                        Mot de passe oublié ?
                    </a>
                </div>
                <input type="password" name="password" class="form-control" required>
            </div>

            <!-- Bouton -->
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">
                Se connecter
            </button>

        </form>

        <!-- Footer -->
        <p class="auth-footer">
            Pas encore d'accès ?
            <a href="<?= BASE_URL ?>/register" style="color: var(--primary);">
                Demander un compte
            </a>
        </p>

    </div>

</div>
