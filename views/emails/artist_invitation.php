<?php

/**
 * Modèle d'email : Invitation d'un artiste
 *
 * Variables disponibles :
 * - $fullname      : Nom complet de l'artiste
 * - $email         : Email de l'artiste
 * - $loginUrl      : URL de connexion
 * - $setPasswordUrl : Lien direct de définition du mot de passe
 */
?>
<p style="font-size: 16px; margin-bottom: 20px;">Hey <strong><?= htmlspecialchars($fullname ?? '', ENT_QUOTES, 'UTF-8') ?></strong>,</p>

<p style="margin-bottom: 16px;">Vous pouvez désormais avoir accès à vos <strong>enregistrements, mixages, arrangements et masters</strong> sur <strong>Accent-Musique</strong>.</p>

<div style="background-color: #eef2ff; border-left: 4px solid #6366f1; padding: 14px 18px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0; color: #312e81; font-size: 14px;">
        🎙️ <strong>Votre espace artiste est actif :</strong> vos fichiers sont accessibles directement depuis votre espace personnel.
    </p>
</div>

<p style="margin-bottom: 8px;">Votre identifiant de connexion est l'adresse : <strong><?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>

<p style="margin-bottom: 24px;">Cliquez sur le bouton ci-dessous pour définir votre mot de passe et accéder à votre espace. Ce lien est valable 48 heures.</p>