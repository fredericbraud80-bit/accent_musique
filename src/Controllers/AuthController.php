<?php
namespace Controllers;

use Core\Controller;
use Core\Mailer;
use Core\Security;
use Core\Session;
use Models\User;

class AuthController extends Controller {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showLogin(): void {
        $this->render('auth/login');
    }

    public function login(): void {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Session::setFlash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Adresse e-mail invalide.');
            $this->redirect('/login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::setFlash('error', 'Identifiants incorrects.');
            $this->redirect('/login');
        }

        if ((int)$user['is_validated'] !== 1) {
            Session::setFlash('error', 'Votre compte est en attente de validation par l\'administrateur.');
            $this->redirect('/login');
        }

        if ((int)$user['is_license_active'] !== 1) {
        Session::setFlash('error', 'Votre licence est expirée. Veuillez contacter l\'administrateur.');
        $this->redirect('/login');
        }


        Session::regenerate();
        Session::set('user_id', (int)$user['id']);
        Session::set('user_name', $user['fullname']);
        Session::set('user_role', $user['role']);
        Session::set('csrf_token', bin2hex(random_bytes(32)));

        Session::set('user_license_expires_at', $user['license_expires_at']);
        Session::set('user_license_active', $user['is_license_active']);


        Session::setFlash('success', 'Ravi de vous revoir, ' . $user['fullname'] . ' !');
        $this->redirect('/accueil');
    }

    public function showRegister(): void {
        $this->render('auth/register');
    }

    public function register(): void {
        $fullname = trim((string)($_POST['fullname'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['password_confirm'] ?? '');

        if ($fullname === '' || $email === '' || $password === '') {
            Session::setFlash('error', 'Tous les champs sont obligatoires.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Adresse e-mail invalide.');
            $this->redirect('/register');
        }

        if (strlen($password) < 12) {
            Session::setFlash('error', 'Le mot de passe doit contenir au moins 12 caractères.');
            $this->redirect('/register');
        }

        if ($password !== $confirm) {
            Session::setFlash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/register');
        }

        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('error', 'Cet adresse email est déjà utilisée.');
            $this->redirect('/register');
        }

        if ($this->userModel->create($fullname, $email, $password)) {
            $mailer = new Mailer();
            $mailer->sendRegistrationConfirmation($email, $fullname);
            $mailer->sendAdminNewRegistrationNotification($email, $fullname);

            Session::setFlash('success', 'Compte créé avec succès ! Un e-mail de confirmation vous a été envoyé. Votre accès doit être validé par l\'administrateur.');
            $this->redirect('/login');
        }

        Session::setFlash('error', 'Une erreur est survenue lors de l\'inscription.');
        $this->redirect('/register');
    }

    public function logout(): void {
        Session::destroy();
        Session::start();
        Session::setFlash('success', 'Vous êtes déconnecté.');
        $this->redirect('/login');
    }

    /**
     * Affiche le formulaire de demande de réinitialisation de mot de passe
     */
    public function showForgotPassword(): void {
        $this->render('auth/forgot_password');
    }

    /**
     * Traite la demande de réinitialisation et envoie l'email sécurisé
     */
    public function forgotPassword(): void {
        $email = trim((string)($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Veuillez saisir une adresse e-mail valide.');
            $this->redirect('/forgot-password');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        // Protection contre l'énumération des utilisateurs :
        // Le message est identique que le compte existe ou non.
        if ($user) {
            // Génération d'un token sécurisé de 64 caractères hexadécimaux
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expiryMinutes = 60;

            $this->userModel->createPasswordReset($user['email'], $tokenHash, $expiryMinutes);

            $resetLink = BASE_URL . '/reset-password?token=' . urlencode($rawToken);

            $mailer = new Mailer();
            $mailer->sendPasswordResetLink($user['email'], $user['fullname'], $resetLink, $expiryMinutes);
        }

        Session::setFlash('success', 'Si cette adresse e-mail correspond à un compte, un lien de réinitialisation sécurisé vous a été envoyé.');
        $this->redirect('/forgot-password');
    }

    /**
     * Affiche le formulaire de saisie du nouveau mot de passe
     */
    public function showResetPassword(): void {
        $token = (string)($_GET['token'] ?? '');

        if ($token === '') {
            Session::setFlash('error', 'Lien de réinitialisation invalide ou manquant.');
            $this->redirect('/login');
            return;
        }

        $tokenHash = hash('sha256', $token);
        $resetRequest = $this->userModel->findValidPasswordReset($tokenHash);

        if (!$resetRequest) {
            Session::setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré. Veuillez faire une nouvelle demande.');
            $this->redirect('/forgot-password');
            return;
        }

        $this->render('auth/reset_password', [
            'token' => $token
        ]);
    }

    /**
     * Enregistre le nouveau mot de passe après validation
     */
    public function resetPassword(): void {
        $token = (string)($_POST['token'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['password_confirm'] ?? '');

        if ($token === '') {
            Session::setFlash('error', 'Jeton de réinitialisation manquant.');
            $this->redirect('/login');
            return;
        }

        $tokenHash = hash('sha256', $token);
        $resetRequest = $this->userModel->findValidPasswordReset($tokenHash);

        if (!$resetRequest) {
            Session::setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.');
            $this->redirect('/forgot-password');
            return;
        }

        if ($password === '' || $confirm === '') {
            Session::setFlash('error', 'Tous les champs sont obligatoires.');
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if (strlen($password) < 12) {
            Session::setFlash('error', 'Le mot de passe doit contenir au moins 12 caractères.');
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirm) {
            Session::setFlash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        $user = $this->userModel->findByEmail($resetRequest['email']);
        if (!$user) {
            Session::setFlash('error', 'Utilisateur introuvable.');
            $this->redirect('/login');
            return;
        }

        if ($this->userModel->updatePassword((int)$user['id'], $password)) {
            // Suppression du jeton utilisé pour empêcher toute réutilisation
            $this->userModel->deletePasswordResetsForEmail($user['email']);

            Session::setFlash('success', 'Votre mot de passe a été modifié avec succès ! Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
            return;
        }

        Session::setFlash('error', 'Une erreur est survenue lors de la mise à jour du mot de passe.');
        $this->redirect('/reset-password?token=' . urlencode($token));
    }
}