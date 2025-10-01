<?php
namespace app\Controllers;

use app\Managers\UserManager;
use app\Models\User;
use app\Enum\UserRole;



class UserController extends AbstractController
{
    private UserManager $um;

    public function __construct()
    {
        parent::__construct(); // Appel du constructeur parent
        $this->um = new UserManager();
    }

    // Récupère un utilisateur depuis l'ID passé en GET
    private function getUserFromRequest(): ?User
    {
        if (!isset($_GET["id"])) {
            $_SESSION["error-message"] = "ID utilisateur manquant.";
            $this->redirect("/Agrimai/index.php?route=list-users");
        }

        $user = $this->um->findById((int)$_GET["id"]);
        if (!$user) {
            $_SESSION["error-message"] = "Utilisateur introuvable.";
            $this->redirect("/Agrimai/index.php?route=list-users");
        }

        return $user;
    }

    // Liste tous les utilisateurs
    public function list(): void
    {
        $users = $this->um->findAll();
        $this->render('admin/users/list.html.twig', ["users" => $users]);
    }


    // Affiche les détails d’un utilisateur
    public function detailsUser(): void
    {
        $user = $this->getUserFromRequest();
        if (!$user) return;

        $this->render("user/details", ["user" => $user]);
    }

    // Valide un utilisateur
    public function validateUser(): void
    {
        $user = $this->getUserFromRequest();
        if (!$user) return;

        $this->um->validateUser($user);

        $_SESSION["success-message"] = "Utilisateur validé.";
        $this->redirect("/AgriMai/index.php?route=list-users");
    }
    public function createUser(): void
    {
        $this->render("user/create");
    }

    public function checkCreateUser(): void
    {
        if (!isset($_POST["first_name"], $_POST["last_name"], $_POST["email"], $_POST["password"], $_POST["role"])) {
            $_SESSION["error-message"] = "Champs manquants.";
            $this->redirect("index.php?route=create-user");
        }

        if ($this->um->findByEmail($_POST["email"]) !== null) {
            $_SESSION["error-message"] = "Un utilisateur avec cet email existe déjà.";
            $this->redirect("index.php?route=create-user");
        }

        $user = new User(
            htmlspecialchars($_POST["first_name"]),
            htmlspecialchars($_POST["last_name"]),
            htmlspecialchars($_POST["email"]),
            password_hash($_POST["password"], PASSWORD_BCRYPT),
            UserRole::from($_POST["role"])
        );

        $this->um->createUser($user);

        $_SESSION["success-message"] = "Utilisateur créé avec succès.";
        $this->redirect("index.php?route=list-users");
    }

    // Affiche le formulaire de mise à jour d’un utilisateur
    public function updateUser(): void
    {
        $user = $this->getUserFromRequest();
        if (!$user) return;

        $this->render("user/update", ["user" => $user]);
    }

    // Vérifie et applique les modifications d’un utilisateur

    public function checkUpdateUser(): void
    {
        if (!isset($_POST["id"], $_POST["first_name"], $_POST["last_name"], $_POST["email"], $_POST["role"])) {
            $_SESSION["error-message"] = "Champs manquants.";
            $this->redirect("index.php?route=list-users");
        }

        $user = $this->um->findById((int)$_POST["id"]);
        if (!$user) {
            $_SESSION["error-message"] = "Utilisateur introuvable.";
            $this->redirect("index.php?route=list-users");
        }

        $user->setFirstName(htmlspecialchars($_POST["first_name"]));
        $user->setLastName(htmlspecialchars($_POST["last_name"]));
        $user->setEmail(htmlspecialchars($_POST["email"]));
        $user->setRole(UserRole::from($_POST["role"]));

        if (!empty($_POST["password"])) {
            $user->setPassword(password_hash($_POST["password"], PASSWORD_BCRYPT));
        }

        $this->um->updateUser($user);

        $_SESSION["success-message"] = "Utilisateur mis à jour avec succès.";
        $this->redirect("index.php?route=list-users");
    }



    // Supprime un utilisateur en Soft delete car je veux garder des traces.
    // Les informations de l'utilisateur sont toujours visible dans la base de donné
    public function deleteUser(): void
    {
        $user = $this->getUserFromRequest();
        if (!$user) return;

        $this->um->deleteUser($user);

        $_SESSION["success-message"] = "Utilisateur supprimé.";
        $this->redirect("/Agrimai/index.php?route=list-users");
    }
}
