<?php
class AuthController extends AbstractController
{
    public function login(): void
    {
        $csrfManager = new CSRFTokenManager();
        $csrfToken = $csrfManager->generateCSRFToken();

        $this->render("login", ["csrfToken" => $csrfToken]);
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

        $this->render("register", ["csrfToken" => $csrfToken]);
    }

    public function checkRegister(): void
    {
        // Vérifie tous les champs et le token CSRF
        if (!isset(
            $_POST["first_name"],
            $_POST["last_name"],
            $_POST["email"],
            $_POST["password"],
            $_POST["confirm-password"],
            $_POST["role"],
            $_POST["csrf-token"]
        )) {
            $_SESSION["error-message"] = "Missing fields";
            $this->redirect("index.php?route=register");
        }

        $tokenManager = new CSRFTokenManager();
        if (!$tokenManager->validateCSRFToken($_POST["csrf-token"])) {
            $_SESSION["error-message"] = "Invalid CSRF token";
            $this->redirect("index.php?route=register");
        }

        if ($_POST["password"] !== $_POST["confirm-password"]) {
            $_SESSION["error-message"] = "The passwords do not match";
            $this->redirect("index.php?route=register");
        }

        $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])[A-Za-z\d^\w\s]{8,}$/';
        if (!preg_match($password_pattern, $_POST["password"])) {
            $_SESSION["error-message"] = "Password is not strong enough";
            $this->redirect("index.php?route=register");
        }

        $um = new UserManager();
        $existUser = $um->findByEmail($_POST["email"]);
        if ($existUser !== null) {
            $_SESSION["error-message"] = "User already exists";
            $this->redirect("index.php?route=register");
        }

        $firstName = htmlspecialchars($_POST["first_name"]);
        $lastName = htmlspecialchars($_POST["last_name"]);
        $email = htmlspecialchars($_POST["email"]);
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

        $roleEnum = UserRole::from($_POST["role"]);
        $role = $roleEnum->value;

        $user = new User($firstName, $lastName, $email, $password, $role);

        $um->createUser($user);

        $_SESSION["user"] = $user->getId();
        unset($_SESSION["error-message"]);

        $this->redirect("index.php");
    }

}

