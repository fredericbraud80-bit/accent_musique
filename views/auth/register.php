<?php
use Core\Security;
?>

<div class="main-container">

    <!-- Carte d'inscription -->
    <div class="auth-card">

        <h2 class="auth-title">Inscription Élève</h2>

        <!-- Message d'erreur -->
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/register" method="POST">

            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">

            <!-- Nom complet -->
            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" name="fullname" class="form-control" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label">Adresse Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <!-- Confirmation -->
            <div class="form-group">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="password_confirm" class="form-control" required>
            </div>

            <!-- Bouton -->
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">
                Créer mon compte
            </button>

        </form>

        <!-- Footer -->
        <p class="auth-footer">
            Déjà un compte ?  
            <a href="<?= BASE_URL ?>/login" style="color: var(--primary);">
                Se connecter
            </a>
        </p>

    </div>

</div>
