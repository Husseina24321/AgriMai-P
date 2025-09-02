<?php
namespace app\Controllers;
use services\CSRFTokenManager;
use app\Managers\UserManager;
use app\Enum\UserRole;
use app\Models\User;


class AuthController extends AbstractController
{
    public function login(): void
    {
        $csrfManager = new CSRFTokenManager();
        $csrfToken = $csrfManager->generateCSRFToken();

        $this->render("/front/login.html.twig", ["csrfToken" => $csrfToken]);
    }

    public function checkLogin(): void
    {
        if (!isset($_POST["email"], $_POST["password"], $_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Missing fields";
            $this->redirect("index.php?route=login");
        }

        $tokenManager = new CSRFTokenManager();
        if (!$tokenManager->validateCSRFToken($_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Invalid CSRF token";
            $this->redirect("index.php?route=login");
        }

        $um = new UserManager();
        $user = $um->findByEmail($_POST["email"]);

        if ($user && password_verify($_POST["password"], $user->getPassword())) {
            $_SESSION["user"] = $user->getId();
            unset($_SESSION["error-message"]);
            $this->redirect("index.php");
        } else {
            $_SESSION["error-message"] = "Invalid login information";
            $this->redirect("index.php?route=login");
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
      var_dump("yes");
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
        }

        // Vérifie que les mots de passe correspondent
        if ($_POST["password"] !== $_POST["confirm-password"]) {
            $_SESSION["error-message"] = "Les mots de passe ne correspondent pas.";
            $this->redirect("index.php?route=register");
        }

        //Vérifie la complexité du mot de passe
        $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/';
        if (!preg_match($password_pattern, $_POST["password"])) {
            $_SESSION["error-message"] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
            $this->redirect("index.php?route=register");
        }


        $um = new UserManager();

        // Vérifie si l'email existe déjà
        if ($um->findByEmail($_POST["email"]) !== null) {
            $_SESSION["error-message"] = "Un utilisateur avec cet email existe déjà.";
            $this->redirect("index.php?route=register");
        }

        // Prépare les données sécurisées
        $firstName = htmlspecialchars($_POST["first_name"]);
        $lastName  = htmlspecialchars($_POST["last_name"]);
        $email     = htmlspecialchars($_POST["email"]);
        $password  = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $roleEnum  = UserRole::from($_POST["role"]);

        // Crée l’utilisateur
        $user = new User($firstName, $lastName, $email, $password, $roleEnum);
        $user = $um->createUser($user);

        // Stocke l'ID utilisateur en session
        $_SESSION["user"] = $user->getId();
        unset($_SESSION["error-message"]);

        // Redirige en fonction du rôle que l'user a choisi
        if ($roleEnum === UserRole::Buyer) {
            // Acheteur va dans page d'accueil
            $this->redirect("index.php");
        } elseif ($roleEnum === UserRole::Producer) {
            // Producteur va dans page pour renseigner les infos produit
            $this->redirect("index.php?route=producerDashboard");
        } else {
            // Par défaut va dans la page d'accueil
            $this->redirect("index.php");
        }
}
}

