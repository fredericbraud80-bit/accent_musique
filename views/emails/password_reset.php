<?php

/**
 * Modèle d'email : Réinitialisation de mot de passe
 * 
 * Variables disponibles :
 * - $fullname       : Nom complet de l'élève
 * - $resetLink      : URL sécurisée de réinitialisation
 * - $expiryMinutes  : Durée de validité en minutes (ex: 60)
 */
$escapedLink = htmlspecialchars($resetLink ?? '', ENT_QUOTES, 'UTF-8');
$expiry = (int)($expiryMinutes ?? 60);
?>
<p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong><?= htmlspecialchars($fullname ?? '', ENT_QUOTES, 'UTF-8') ?></strong>,</p>

<p style="margin-bottom: 16px;">Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte sur <strong>Accent-Musique</strong>.</p>

<p style="margin-bottom: 20px;">Pour choisir un nouveau mot de passe sécurisé, cliquez sur le bouton ci-dessous :</p>

<div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 14px 18px; margin: 24px 0; border-radius: 4px;">
    <p style="margin: 0; color: #92400e; font-size: 14px;">
        ⏱️ <strong>Attention :</strong> Ce lien de réinitialisation est à usage unique et expirera dans <strong><?= $expiry ?> minutes</strong>.
    </p>
</div>

<p style="font-size: 13px; color: #64748b; margin-top: 20px; line-height: 1.5;">
    Si le bouton ne fonctionne pas, copiez et collez l'adresse suivante dans votre navigateur :<br>
    <a href="<?= $escapedLink ?>" style="color: #2563eb; word-break: break-all;"><?= $escapedLink ?></a>
</p>

<p style="font-size: 13px; color: #64748b; margin-top: 16px; margin-bottom: 0;">
    Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité. Votre mot de passe restera inchangé.
</p>