<?php

/**
 * Modèle d'email : Confirmation d'inscription élève
 * 
 * Variables disponibles :
 * - $fullname : Nom complet de l'élève
 * - $loginUrl : URL de connexion
 */
?>
<p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong><?= htmlspecialchars($fullname ?? '', ENT_QUOTES, 'UTF-8') ?></strong>,</p>

<p style="margin-bottom: 16px;">Votre demande d'inscription sur la plateforme <strong>Accent-Musique</strong> a bien été enregistrée.</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #3b82f6; padding: 14px 18px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0; color: #1e293b; font-size: 14px;">
        ℹ️ <strong>Information importante :</strong> Votre compte est actuellement <em>en attente de validation</em> par un administrateur. Vous recevrez une notification dès que votre accès aura été approuvé.
    </p>
</div>

<p style="margin-bottom: 24px;">Une fois votre compte activé, vous pourrez vous connecter et accéder à tous vos cours, partitions et ressources musicales.</p>