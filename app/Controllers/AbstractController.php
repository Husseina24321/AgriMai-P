<?php
namespace app\Controllers;
use app\Enum\UserStatus;
use app\Managers\UserManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;



abstract class AbstractController
{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader('app/views/templates');
        $this->twig = new Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new DebugExtension());
    }

    protected function render(string $name, array $context = []): void
    {
        // Démarre la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Ajoute automatiquement la session dans le contexte Twig
        $context['session'] = $_SESSION;

        try {
            echo $this->twig->render($name, $context);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            die("Erreur de rendu Twig : " . $e->getMessage());
        }
    }

    protected function redirect(string $route): void
    {
        header("Location: $route");
        exit();
    }
    protected function requireLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            $_SESSION["error-message"] = "Vous devez être connecté.";
            header("Location: ./index.php?route=login");
            exit;
        }

        // Récupérer l'utilisateur depuis la DB
        $um = new UserManager();
        $user = $um->findById($_SESSION['user']['id']);

        if (!$user) {
            // utilisateur introuvable, déconnecter
            unset($_SESSION['user']);
            $_SESSION["error-message"] = "Utilisateur introuvable.";
            header("Location: ./index.php?route=login");
            exit;
        }

        if ($user->getStatus() === UserStatus::Pending) {
            // compte en attente, bloquer l'accès
            unset($_SESSION['user']); // on déconnecte
            $_SESSION["error-message"] = "Votre compte est en attente de validation par l'administrateur.";
            header("Location: ./index.php?route=login");
            exit;
        }
    }

}
