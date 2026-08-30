<?php

/**
 * Layout principal des e-mails Accent-Musique
 * 
 * Variables disponibles :
 * - $title       : Titre du message
 * - $preheader   : Texte d'aperçu dans les boîtes de réception
 * - $content     : Contenu HTML spécifique de l'email
 * - $actionUrl   : (Optionnel) Lien du bouton d'action
 * - $actionText  : (Optionnel) Libellé du bouton d'action
 */
$appName = defined('APP_NAME') ? APP_NAME : 'Accent-Musique';
$currentYear = date('Y');
$escapedTitle = htmlspecialchars($title ?? $appName, ENT_QUOTES, 'UTF-8');
$escapedPreheader = htmlspecialchars($preheader ?? '', ENT_QUOTES, 'UTF-8');

$actionButtonHtml = '';
if (!empty($actionUrl) && !empty($actionText)) {
    $escapedUrl = htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8');
    $escapedBtnText = htmlspecialchars($actionText, ENT_QUOTES, 'UTF-8');
    $actionButtonHtml = "
        <table role='presentation' border='0' cellpadding='0' cellspacing='0' style='margin: 28px 0;'>
            <tr>
                <td align='center' style='border-radius: 6px; background-color: #f59e0b;'>
                    <a href='{$escapedUrl}' target='_blank' style='display: inline-block; padding: 14px 28px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 6px;'>
                        {$escapedBtnText}
                    </a>
                </td>
            </tr>
        </table>
    ";
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escapedTitle ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        table {
            border-collapse: collapse;
        }

        .preheader {
            display: none !important;
            visibility: hidden;
            opacity: 0;
            color: transparent;
            height: 0;
            width: 0;
        }

        @media only screen and (max-width: 620px) {
            .container-table {
                width: 100% !important;
                padding: 10px !important;
            }

            .content-card {
                padding: 24px 16px !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f8fafc;">
    <?php if ($escapedPreheader !== ''): ?>
        <span class="preheader"><?= $escapedPreheader ?></span>
    <?php endif; ?>
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table class="container-table" role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%;">
                    <!-- Header avec Logo -->
                    <tr>
                        <td align="center" style="padding: 20px 0 24px 0;">
                            <h1 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 1px; color: #0f172a;">
                                ACCENT<span style="color: #f59e0b;">MUSIQUE</span>
                            </h1>
                        </td>
                    </tr>
                    <!-- Card Body -->
                    <tr>
                        <td class="content-card" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 36px 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); color: #334155; font-size: 15px; line-height: 1.6;">
                            <h2 style="margin-top: 0; margin-bottom: 20px; font-size: 20px; color: #0f172a;"><?= $escapedTitle ?></h2>

                            <?= $content ?>

                            <?= $actionButtonHtml ?>

                            <p style="margin-top: 24px; margin-bottom: 0; font-size: 14px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                                Musicalement,<br>
                                <strong>L'équipe <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></strong>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 16px; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                            <p style="margin: 0 0 6px 0;">&copy; <?= $currentYear ?> <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?>. Tous droits réservés.</p>
                            <p style="margin: 0;">Ceci est un message automatique, merci de ne pas y répondre directement.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>