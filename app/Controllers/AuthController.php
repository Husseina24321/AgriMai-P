<?php
namespace app\Controllers;
use services\CsrfTokenManager;
use app\Managers\UserManager;
use app\Managers\ProductManager;
use app\Enum\UserRole;
use app\Enum\UserStatus;
use app\Models\User;



class AuthController extends AbstractController
{
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // On récupère les messages flash
        $errorMessage = $_SESSION["error-message"] ?? null;
        $successMessage = $_SESSION["success-message"] ?? null;

        // On les supprime pour éviter qu'ils restent
        unset($_SESSION["error-message"], $_SESSION["success-message"]);

        // Génère le token CSRF
        $csrfManager = new CSRFTokenManager();
        $csrfToken = $csrfManager->generateCSRFToken();

        // Passe les messages à Twig
        $this->render("/front/login.html.twig", [
            "csrfToken" => $csrfToken,
            "errorMessage" => $errorMessage,
            "successMessage" => $successMessage,
        ]);
    }




    public function checkLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifie que tous les champs sont présents
        if (!isset($_POST["email"], $_POST["password"], $_POST["csrf-token"], $_POST["role"])) {
            $_SESSION["error-message"] = "Tous les champs sont obligatoires.";
            $this->redirect("index.php?route=login");
            return;
        }

        // Vérifie le CSRF token
        $tokenManager = new CSRFTokenManager();
        if (!$tokenManager->validateCSRFToken($_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Token CSRF invalide.";
            $this->redirect("index.php?route=login");
            return;
        }

        $um = new UserManager();
        $user = $um->findByEmail($_POST["email"]);

        // Récupère le rôle choisi dans le formulaire
        $roleEnum = UserRole::tryFrom($_POST["role"]);

        // Vérifie que l'utilisateur existe
        if (!$user) {
            $_SESSION["error-message"] = "Utilisateur inexistant.";
            $this->redirect("index.php?route=login");
            return;
        }

        // Vérifie le mot de passe
        if (!password_verify($_POST["password"], $user->getPassword())) {
            $_SESSION["error-message"] = "Mot de passe incorrect.";
            $this->redirect("index.php?route=login");
            return;
        }

        // Vérifie que le rôle choisi correspond au rôle réel
        if ($user->getRole() !== $roleEnum) {
            $_SESSION["error-message"] = "Rôle incorrect pour cet utilisateur.";
            $this->redirect("index.php?route=login");
            return;
        }

        // ⚡ Vérifie le statut du compte avant d'autoriser la connexion
        if ($user->getStatus() === UserStatus::Pending) {
            $_SESSION["error-message"] = "Votre compte est en attente de validation par l'administrateur.
            Veuillez vous connecter dans 1 minute";
            $this->redirect("index.php?route=login");
            return;
        }


        // Tout est correct → on stocke les infos en session
        $_SESSION["user"] = [
            "id"    => $user->getId(),
            "email" => $user->getEmail(),
            "role"  => $user->getRole()->value
        ];
        unset($_SESSION["error-message"]);

        // Redirection selon le rôle réel
        switch ($user->getRole()) {
            case UserRole::Buyer:
                $this->redirect("/AgriMai/index.php?route=home");
                break;

            case UserRole::Producer:
                $pm = new ProductManager();
                if ($pm->userLogProducts($user->getId())) {
                    $this->redirect("/AgriMai/index.php?route=list-products-by-user&user_id=" . $user->getId());
                } else {
                    $this->redirect("/AgriMai/index.php?route=create-product");
                }
                break;

            case UserRole::Admin:
                $this->redirect("/AgriMai/index.php?route=list-users");
                break;
        }
    }




    public function register(): void
    {
        $csrfManager = new CSRFTokenManager();
        $csrfToken = $csrfManager->generateCSRFToken();
        $roles = [UserRole::Buyer, UserRole::Producer];
        $this->render("/front/register.html.twig", ["csrfToken" => $csrfToken, "roles" => $roles, "session" => $_SESSION]);
    }

    public function checkRegister(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifie que tous les champs obligatoires sont présents
        if (!isset(
            $_POST["first_name"],
            $_POST["last_name"],
            $_POST["email"],
            $_POST["password"],
            $_POST["confirm-password"],
            $_POST["role"]
        )) {
            $_SESSION["error-message"] = "Tous les champs sont obligatoires.";
            $this->redirect("index.php?route=register");
            return;
        }

        // Vérifie que les mots de passe correspondent
        if ($_POST["password"] !== $_POST["confirm-password"]) {
            $_SESSION["error-message"] = "Les mots de passe ne correspondent pas.";
            $this->redirect("index.php?route=register");
            return;
        }

        // Vérifie la complexité du mot de passe
        $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/';
        if (!preg_match($password_pattern, $_POST["password"])) {
            $_SESSION["error-message"] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
            $this->redirect("index.php?route=register");
            return;
        }

        $um = new UserManager();
        // Vérifie si l'email existe déjà
        if ($um->findByEmail($_POST["email"]) !== null) {
            $_SESSION["error-message"] = "Un utilisateur avec cet email existe déjà.";
            $this->redirect("index.php?route=register");
            return;
        }

        // Prépare les données sécurisées
        $firstName = htmlspecialchars($_POST["first_name"]);
        $lastName  = htmlspecialchars($_POST["last_name"]);
        $email     = htmlspecialchars($_POST["email"]);
        $password  = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $roleEnum  = UserRole::from($_POST["role"]);

        // ⚡ Crée l’utilisateur avec statut Pending
        $user = new User(
            $firstName,
            $lastName,
            $email,
            $password,
            $roleEnum,
            UserStatus::Pending //  statut par défaut : attente de validation, utiliser pour la validation
        );

        $um->createUser($user);

        // Vérifie immédiatement le statut
        if ($user->getStatus() === UserStatus::Pending) {
            $_SESSION["success-message"] = "Votre inscription a été prise en compte. 
    Votre compte est en attente de validation par l'administrateur.";
            $this->redirect("index.php?route=login");
            return;
        }

        // Si jamais l'utilisateur est validé immédiatement
        $this->redirect("/AgriMai/index.php?route=login");
    }

    public function logout(): void
    {
        // démarre la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // supprime toutes les variables de session
        $_SESSION = [];

        // détruit complètement la session
        session_destroy();

        // redirige vers la page de connexion
        header("Location: /AgriMai/index.php?route=login");
        exit;
    }
}