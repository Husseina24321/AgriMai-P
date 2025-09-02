<?php
namespace services;
use app\Controllers\UserController;
use app\Controllers\HomeController;
use app\Controllers\ContactController;
use app\Controllers\ProductController;
use app\Controllers\AuthController;
use app\Controllers\DashboardController;

class Router {
    public function handleRequest(array $get):void
    {
        if (!empty($get['route'])) {
            switch ($get['route']) {
                // Home
                case "home":
                    $controller = new HomeController();
                    $controller->index();
                    break;

                // Newsletter
                case "newsletter":
                    $controller = new HomeController();
                    $controller->subscribeNewsletter();
                    break;
                // USERS
                case "list-users":
                    $userController = new UserController();
                    $userController->list();
                    // UserController::list();
                    break;
                case "pending-user":
                    $userController = new UserController();
                    $userController->listPending();
                    // UserController::listPending();
                    break;
                case "details-user":
                    $userController = new UserController();
                    $userController->detailsUser();
                    //UserController::detailsUser();
                    break;
                case "update-user":
                    $userController = new UserController();
                    $userController->updateUser();
                    //UserController::updateUser();
                    break;

                case "check-update-user":
                    $userController = new UserController();
                    $userController->checkUpdateUser();

                case "create-user":
                    $userController = new UserController();
                    $userController->createUser();
                    //UserController::createUser();
                break;
                case "check-create-user":
                    $userController = new UserController();
                    $userController->checkCreateUser();
                    //UserController::checkCreateUser();
                case "validate-user":
                    $userController = new UserController();
                    $userController->validateUser();
                    // UserController::validateUser();
                    break;
                case "delete-user":
                    $userController = new UserController();
                    $userController->deleteUser();
                    //UserController::deleteUser();
                    break;

                // MESSAGES
                case "list-messages":
                    $ContactController = new ContactController();
                    $ContactController->listMessages();
                    //ContactController::listMessages();
                    break;
                case "list-messages-by-user":
                    $ContactController = new ContactController();
                    $userId = isset($_GET["user_id"]) ? (int) $_GET["user_id"] : 0; // récupère l'id utilisateur depuis l'URL
                    $ContactController->listMessagesByUser($userId);
                    break;

                case "delete-message":
                    $ContactController = new ContactController();
                    $messageId = isset($_GET["id"]) ? (int) $_GET["id"] : 0; // récupère l'id du message pour la suppression
                    $ContactController->deleteMessage($messageId);
                    break;


                // PRODUCTS
                case "list-products":
                    $controller = new ProductController();
                    $controller->listProducts();
                    break;

                case "list-products-by-user":
                    $controller = new ProductController();
                    $userId = $_GET["user_id"] ?? 0;
                    $controller->listProductsByUser((int)$userId);
                    break;

                case "list-products-by-location":
                    $controller = new ProductController();
                    $location = $_GET["location"] ?? '';
                    $controller->listProductsByLocation($location);
                    break;

                case "products-by-location":
                    $controller = new HomeController();
                    $region = $_GET['location'] ?? '';
                    $controller->showByRegion($region);
                    break;

                case "create-product":
                    $controller = new ProductController();
                    $controller->createProduct();
                    break;

                case "edit-product":
                    $controller = new ProductController();
                    $controller->editProduct();
                    break;

                case 'producerForm':
                    $controller = new ProductController();
                    $controller->showForm();
                    break;

                case "delete-product":
                    $controller = new ProductController();
                    $controller->deleteProduct();
                    break;

                //dashboard
                case 'dashboard':
                    $controller = new DashboardController();
                    $controller->index();
                    break;
                // ORDERS
                case "list-orders":
                    break;
                case "details-order":
                    break;
                case "list-orders-by-user":
                    break;
                case "update-order":
                    break;
                case "check-update-order":
                    break;
                case "delete-order":
                    break;

                // AUTH
                case "login":
                    $authController = new AuthController();
                    $authController->login();
                    break;
                case "check-login":
                    $authController = new AuthController();
                    $authController->checkLogin();
                    break;
                case "register":
                    $authController = new AuthController();
                    $authController->register();
                    break;
                case "check-register":
                    $authController = new AuthController();
                    $authController->checkRegister();
                    break;
                case "logout":
                    // AuthController::logout();
                    break;

                // -------- 404 --------
                default:
                    // PageController::error404();
                    break;
            }
        } else {
            // pas de route -> home
            // PageController::home();
        }
    }
}

