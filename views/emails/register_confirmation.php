<?php

/**
 * Modèle d'email : Confirmation d'inscription élève
 *
 * Variables disponibles :
 * - $fullname : Nom complet de l'élève
 * - $loginUrl : URL de connexion
 * - $cguUrl : URL des CGU
 * - $iban : IBAN de l'association pour le paiement
 * - $ibanBic : BIC associé
 * - $ibanHolder : Titulaire du compte
 */
?>
<p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong><?= htmlspecialchars($fullname ?? '', ENT_QUOTES, 'UTF-8') ?></strong>,</p>

<p style="margin-bottom: 16px;">Votre demande d'inscription sur la plateforme <strong>Accent-Musique</strong> a bien été enregistrée.</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #3b82f6; padding: 14px 18px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0; color: #1e293b; font-size: 14px;">
        ℹ️ <strong>Information importante :</strong> Pour finaliser votre inscription, merci de régler votre adhésion par virement bancaire aux coordonnées ci-dessous. Votre compte est actuellement <em>en attente de validation</em> : dès réception du paiement, un administrateur validera votre inscription et vous recevrez une notification dès que votre accès aura été approuvé.
    </p>
</div>

<?php if (!empty($iban)) : ?>
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; padding: 18px 20px; margin: 20px 0; border-radius: 6px;">
        <p style="margin: 0 0 12px; color: #1e293b; font-size: 15px; font-weight: bold;">💳 Coordonnées bancaires pour le virement</p>
        <table cellpadding="0" cellspacing="0" style="width: 100%; font-size: 14px; color: #334155;">
            <tr>
                <td style="padding: 4px 0; color: #64748b; width: 110px;">Titulaire :</td>
                <td style="padding: 4px 0;"><strong><?= htmlspecialchars($ibanHolder ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: #64748b;">IBAN :</td>
                <td style="padding: 4px 0;"><strong style="font-family: monospace; font-size: 15px;"><?= htmlspecialchars($iban ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
            </tr>
            <?php if (!empty($ibanBic)) : ?>
                <tr>
                    <td style="padding: 4px 0; color: #64748b;">BIC :</td>
                    <td style="padding: 4px 0;"><strong style="font-family: monospace;"><?= htmlspecialchars($ibanBic, ENT_QUOTES, 'UTF-8') ?></strong></td>
                </tr>
            <?php endif; ?>
        </table>
        <p style="margin: 12px 0 0; color: #64748b; font-size: 13px;">
            Pensez à indiquer votre nom en référence du virement afin de faciliter le rapprochement avec votre inscription.
        </p>
    </div>
<?php endif; ?>

<p style="margin-bottom: 24px;">Une fois votre compte activé, vous pourrez vous connecter et accéder à tous vos cours, partitions et ressources musicales.</p>

<p style="margin-bottom: 24px; font-size: 14px; color: #475569;">
    En vous inscrivant, vous reconnaissez avoir pris connaissance des
    <a href="<?= htmlspecialchars($cguUrl ?? BASE_URL . '/cgu', ENT_QUOTES, 'UTF-8') ?>" style="color: #3b82f6; text-decoration: underline;">Conditions Générales d'Utilisation (CGU)</a>.
</p>