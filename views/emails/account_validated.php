<?php

/**
 * Modèle d'email : Compte élève validé
 * 
 * Variables disponibles :
 * - $fullname : Nom complet de l'élève
 * - $loginUrl : URL de connexion
 */
?>
<p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong><?= htmlspecialchars($fullname ?? '', ENT_QUOTES, 'UTF-8') ?></strong>,</p>

<p style="margin-bottom: 16px;">Excellente nouvelle ! Votre compte élève sur <strong>Accent-Musique</strong> a été validé par l'administrateur.</p>

<div style="background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 14px 18px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0; color: #065f46; font-size: 14px;">
        ✅ <strong>Licence active :</strong> Votre licence d'accès est désormais active. Vous pouvez dès maintenant consulter l'ensemble des cours, livres, vidéos et exercices.
    </p>
</div>

<p style="margin-bottom: 24px;">Cliquez sur le bouton ci-dessous pour vous connecter et commencer votre apprentissage musical.</p>