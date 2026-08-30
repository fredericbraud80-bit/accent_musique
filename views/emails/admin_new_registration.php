<?php

/**
 * Modèle d'email : Notification nouvelle inscription pour l'administrateur
 * 
 * Variables disponibles :
 * - $fullname : Nom complet de l'élève
 * - $email    : Adresse e-mail du nouvel élève
 * - $dateStr  : Date et heure de l'inscription
 * - $adminUrl : URL du tableau de bord admin
 */
$escapedName = htmlspecialchars($fullname ?? '', ENT_QUOTES, 'UTF-8');
$escapedEmail = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
?>
<p style="font-size: 16px; margin-bottom: 20px;">Bonjour Administrateur,</p>

<p style="margin-bottom: 16px;">Un nouvel élève vient de créer un compte sur la plateforme et attend votre validation :</p>

<ul style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 30px; margin: 20px 0; line-height: 1.8;">
    <li><strong>Nom complet :</strong> <?= $escapedName ?></li>
    <li><strong>Adresse e-mail :</strong> <a href="mailto:<?= $escapedEmail ?>" style="color: #2563eb;"><?= $escapedEmail ?></a></li>
    <li><strong>Date d'inscription :</strong> <?= htmlspecialchars($dateStr ?? date('d/m/Y à H:i'), ENT_QUOTES, 'UTF-8') ?></li>
</ul>

<p style="margin-bottom: 24px;">Vous pouvez valider ou rejeter cette demande directement depuis votre tableau de bord administrateur.</p>