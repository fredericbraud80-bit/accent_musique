<?php
namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Throwable;

class Mailer {
    /**
     * Crée et configure une instance de PHPMailer
     */
    private function createMailer(): PHPMailer {
        $mail = new PHPMailer(true);

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Timeout = 10;

        $driver = defined('MAIL_DRIVER') ? strtolower(MAIL_DRIVER) : 'smtp';

        if ($driver === 'smtp') {
            $mail->isSMTP();
            $mail->Host = defined('MAIL_HOST') ? MAIL_HOST : '127.0.0.1';
            $mail->Port = defined('MAIL_PORT') ? (int)MAIL_PORT : 1025;

            $username = defined('MAIL_USERNAME') ? trim(MAIL_USERNAME) : '';
            $password = defined('MAIL_PASSWORD') ? trim(MAIL_PASSWORD) : '';

            if ($username !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = $password;
            } else {
                $mail->SMTPAuth = false;
            }

            $encryption = defined('MAIL_ENCRYPTION') ? strtolower(trim(MAIL_ENCRYPTION)) : '';
            if ($encryption === 'ssl' || $encryption === 'smtps') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls' || $encryption === 'starttls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        } else {
            $mail->isMail();
        }

        $fromAddress = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'contact@accent-musique.fr';
        $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Accent-Musique';
        $mail->setFrom($fromAddress, $fromName);

        return $mail;
    }

    /**
     * Envoie un email générique (HTML + texte brut)
     */
    public function send(string $to, string $subject, string $htmlBody, string $altBody = '', ?string $toName = null): bool {
        try {
            $mail = $this->createMailer();

            if ($toName !== null && $toName !== '') {
                $mail->addAddress($to, $toName);
            } else {
                $mail->addAddress($to);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            if ($altBody === '') {
                $altBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], ["\n", "\n", "\n", "\n\n", "\n"], $htmlBody));
                $altBody = trim(preg_replace("/[\r\n]+/", "\n\n", $altBody));
            }
            $mail->AltBody = $altBody;

            return $mail->send();
        } catch (Throwable $e) {
            error_log("Mailer error while sending email to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rend une vue d'email (depuis views/emails/{$view}.php)
     */
    private function renderView(string $view, array $data = []): string {
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../../views/emails/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException('Vue email introuvable : ' . $view);
        }

        ob_start();
        require $viewFile;
        return ob_get_clean();
    }

    /**
     * Construit le contenu HTML complet en injectant la vue dans views/emails/layout.php
     */
    public function renderEmail(
        string $view,
        array $data,
        string $title,
        string $preheader = '',
        ?string $actionUrl = null,
        ?string $actionText = null
    ): string {
        $content = $this->renderView($view, $data);

        return $this->renderView('layout', array_merge($data, [
            'title'      => $title,
            'preheader'  => $preheader,
            'content'    => $content,
            'actionUrl'  => $actionUrl,
            'actionText' => $actionText,
        ]));
    }

    /**
     * Envoie un email de confirmation d'inscription à l'utilisateur
     */
    public function sendRegistrationConfirmation(string $email, string $fullname): bool {
        $subject = 'Bienvenue sur Accent-Musique - Inscription reçue';
        $loginUrl = BASE_URL . '/login';

        $html = $this->renderEmail(
            'register_confirmation',
            [
                'fullname' => $fullname,
                'loginUrl' => $loginUrl,
            ],
            'Inscription enregistrée',
            'Votre demande d\'inscription sur Accent-Musique est en cours de traitement.',
            $loginUrl,
            'Aller à la page de connexion'
        );

        return $this->send($email, $subject, $html, '', $fullname);
    }

    /**
     * Envoie un email de réinitialisation de mot de passe sécurisé
     */
    public function sendPasswordResetLink(string $email, string $fullname, string $resetLink, int $expiryMinutes = 60): bool {
        $subject = 'Réinitialisation de votre mot de passe - Accent-Musique';

        $html = $this->renderEmail(
            'password_reset',
            [
                'fullname'      => $fullname,
                'resetLink'     => $resetLink,
                'expiryMinutes' => $expiryMinutes,
            ],
            'Réinitialisation de mot de passe',
            'Demande de réinitialisation de votre mot de passe Accent-Musique.',
            $resetLink,
            'Réinitialiser mon mot de passe'
        );

        return $this->send($email, $subject, $html, '', $fullname);
    }

    /**
     * Envoie une notification à l'administrateur pour signaler une nouvelle inscription
     */
    public function sendAdminNewRegistrationNotification(string $newUserEmail, string $newUserFullname): bool {
        $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : '';
        if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $dateStr = date('d/m/Y à H:i');
        $adminUrl = BASE_URL . '/admin';
        $subject = "[Accent-Musique] Nouvelle inscription : {$newUserFullname}";

        $html = $this->renderEmail(
            'admin_new_registration',
            [
                'fullname' => $newUserFullname,
                'email'    => $newUserEmail,
                'dateStr'  => $dateStr,
                'adminUrl' => $adminUrl,
            ],
            'Nouvelle inscription d\'un élève',
            "Nouvel utilisateur en attente de validation : {$newUserFullname}",
            $adminUrl,
            'Accéder au tableau de bord'
        );

        return $this->send($adminEmail, $subject, $html, '', 'Administrateur Accent-Musique');
    }

    /**
     * Envoie un email à l'élève lorsque son compte a été validé par un administrateur
     */
    public function sendAccountValidated(string $email, string $fullname): bool {
        $subject = 'Votre compte Accent-Musique a été validé !';
        $loginUrl = BASE_URL . '/login';

        $html = $this->renderEmail(
            'account_validated',
            [
                'fullname' => $fullname,
                'loginUrl' => $loginUrl,
            ],
            'Compte validé avec succès',
            'Votre accès à Accent-Musique est maintenant actif.',
            $loginUrl,
            'Se connecter maintenant'
        );

        return $this->send($email, $subject, $html, '', $fullname);
    }
}

