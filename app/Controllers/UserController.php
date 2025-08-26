<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Managers/UserManager.php';
class UserController extends AbstractController
{
    // Méthode privée pour récupérer un utilisateur via l'id passé en GET
    // et éviter de doubler
    private function getUserFromRequest(): ?User
    {
        if (!isset($_GET["id"])) {
            echo "ID utilisateur manquant.";
            return null;
        }

        $um = new UserManager();
        $user = $um->findById((int)$_GET["id"]);

        if (!$user) {
            echo "Utilisateur introuvable.";
            return null;
        }

        return $user;
    }

    // Liste tous les utilisateurs
    public function list(): void
    {
        $um = new UserManager();
        $users = $um->findAll();

        $this->render("user/list", [
            "users" => $users
        ]);
    }

    // Affiche les détails d’un utilisateur
    public function detailsUser(): void
    {
        $user = $this->getUserFromRequest();
        if (!$user) return;

        $this->render("user/details", [
            "user" => $user
        ]);
    }

    // Affiche le formulaire de création d’utilisateur
    public function createUser(): void
    {
        $csrfManager = new CSRFTokenManager();
        $csrfToken = $csrfManager->generateCSRFToken();

        $this->render("user/create", ["csrfToken" => $csrfToken]);
    }

    // Vérifie et enregistre un nouvel utilisateur
    public function checkCreateUser(): void
    {
        if (!isset($_POST["first_name"], $_POST["last_name"], $_POST["email"], $_POST["password"], $_POST["role"], $_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Champs manquants.";
            $this->redirect("index.php?route=create-user");
        }

        $tokenManager = new CSRFTokenManager();
        if (!$tokenManager->validateCSRFToken($_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Token CSRF invalide.";
            $this->redirect("index.php?route=create-user");
        }

        $um = new UserManager();
        if ($um->findByEmail($_POST["email"]) !== null) {
            $_SESSION["error-message"] = "Un utilisateur avec cet email existe déjà.";
            $this->redirect("index.php?route=create-user");
        }

        $user = new User(
            htmlspecialchars($_POST["first_name"]),
            htmlspecialchars($_POST["last_name"]),
            htmlspecialchars($_POST["email"]),
            password_hash($_POST["password"], PASSWORD_BCRYPT),
            UserRole::from($_POST["role"]) // Enum
        );

        $um->createUser($user);

        $_SESSION["success-message"] = "Utilisateur créé avec succès.";
        $this->redirect("index.php?route=list-users");
    }

    // Affiche le formulaire de mise à jour d’un utilisateur
    public function updateUser(): void
    {
        $user = $this->getUserFromRequest();
        if (!$user) return;

        $csrfManager = new CSRFTokenManager();
        $csrfToken = $csrfManager->generateCSRFToken();

        $this->render("user/update", [
            "user" => $user,
            "csrfToken" => $csrfToken
        ]);
    }

    // Vérifie et applique les modifications d’un utilisateur
    public function checkUpdateUser(): void
    {
        if (!isset($_POST["id"], $_POST["first_name"], $_POST["last_name"], $_POST["email"], $_POST["role"], $_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Champs manquants.";
            $this->redirect("index.php?route=list-users");
        }

        $tokenManager = new CSRFTokenManager();
        if (!$tokenManager->validateCSRFToken($_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Token CSRF invalide.";
            $this->redirect("index.php?route=list-users");
        }

        $um = new UserManager();
        $user = $um->findById((int)$_POST["id"]);

        if (!$user) {
            $_SESSION["error-message"] = "Utilisateur introuvable.";
            $this->redirect("index.php?route=list-users");
        }

        $user->setFirstName(htmlspecialchars($_POST["first_name"]));
        $user->setLastName(htmlspecialchars($_POST["last_name"]));
        $user->setEmail(htmlspecialchars($_POST["email"]));
        $user->setRole(UserRole::from($_POST["role"])); // Enum

        if (!empty($_POST["password"])) {
            $user->setPassword(password_hash($_POST["password"], PASSWORD_BCRYPT));
        }

        $um->updateUser($user);

        $_SESSION["success-message"] = "Utilisateur mis à jour avec succès.";
        $this->redirect("index.php?route=list-users");
    }

    // Supprime un utilisateur
    public function deleteUser(): void
    {
        $user = $this->getUserFromRequest();
        if (!$user) return;

        $um = new UserManager();
        $um->deleteUser($user);

        $_SESSION["success-message"] = "Utilisateur supprimé.";
        $this->redirect("index.php?route=list-users");
    }
}

