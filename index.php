<?php
// charge l'autoload de composer
require __DIR__ . "/vendor/autoload.php";
use services\Router;
use app\Controllers\AuthController;
use app\Controllers\UserController;
use app\Controllers\ContactController;
use app\Controllers\ProductController;

session_start();
$router = new Router();
$router->handleRequest($_GET);

// charge le contenu du .env dans $_ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Détermine la route demandée
/*$route = $_GET['route'] ?? 'home';
switch ($route) {
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;

    case 'register':
        $controller = new AuthController();
        $controller->register();
        break;

    case 'checkRegister':
        $controller = new AuthController();
        $controller->checkRegister();

    case 'checkLogin':
        $controller = new AuthController();
        $controller->checkLogin();


    case "list-users":
        $Controller = new UserController();
        $Controller->list();
        break;

    case "pending-user":
        $Controller = new UserController();
        $Controller->listPending();
        break;

    case "details-user":
        $Controller = new UserController();
        $Controller->detailsUser();
        break;

    case "update-user":
        $Controller = new UserController();
        $Controller->updateUser();
        break;

    case "check-update-user":
        $Controller = new UserController();
        $Controller->checkUpdateUser();

    case "create-user":
        $Controller = new UserController();
        $Controller->createUser();
        break;

    case "check-create-user":
        $Controller = new UserController();
        $Controller->checkCreateUser();

    case "validate-user":
        $Controller = new UserController();
        $Controller->validateUser();
        break;

    case "delete-user":
        $Controller = new UserController();
        $Controller->deleteUser();
        break;

    // MESSAGES
    case "list-messages":
        $Controller = new ContactController();
        $Controller->listMessages();
        break;
    case "list-messages-by-user":
        $Controller = new ContactController();
        $userId = isset($_GET["user_id"]) ? (int) $_GET["user_id"] : 0;
        $Controller->listMessagesByUser($userId);
        break;

    case "delete-message":
        $Controller = new ContactController();
        $messageId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
        $Controller->deleteMessage($messageId);
        break;

        //Produit
    case 'producerForm':
        $controller = new ProductController();
        $controller->showForm();
        break;

    default:
        // Page d'accueil
        echo "<h1>Bienvenue sur AgriMai</h1>";
        break;

}
*/


