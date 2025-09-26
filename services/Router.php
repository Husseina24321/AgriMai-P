<?php
namespace services;
use app\Controllers\AboutController;
use app\Controllers\UserController;
use app\Controllers\FaqController;
use app\Controllers\HomeController;
use app\Controllers\ContactController;
use app\Controllers\ProductController;
use app\Controllers\AuthController;
use app\Controllers\ProducerProductController;
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

                    //about
                case "about":
                        $controller = new AboutController();
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

                case "sendMessage":
                    $contactController = new ContactController();
                    $contactController->sendMessage();
                    break;

                case "successMessage":
                    $contactController = new ContactController();
                    $contactController->successMessage();
                    break;


                case "list-messages":
                    $ContactController = new ContactController();
                    $ContactController->listMessages();
                    break;

                case "list-messages-by-user":
                    $ContactController = new ContactController();
                    $userId = isset($_GET["user_id"]) ? (int) $_GET["user_id"] : 0; // récupère l'id utilisateur depuis l'URL
                    $ContactController->listMessagesByUser($userId);
                    break;

                case "listMessagesByProducer":
                    // Vérifie que le producteur est connecté
                    $ContactController = new ContactController();

                    // Récupère l'ID depuis la session
                    $producerId = $_SESSION['user']['id'];

                    $ContactController->listMessagesByProducer($producerId);
                    break;

                case "producerMessages":
                    // Vérifie que le producteur est connecté
                    $ContactController = new ContactController();
                    $producerId = $_SESSION['user']['id']; // ID du producteur connecté
                    $ContactController->listMessagesByProducer($producerId);
                    break;

                case "buyerMessages":
                    // Vérifie que l'acheteur est connecté
                    $contactController = new ContactController();
                    $buyerId = $_SESSION['user']['id']; // ID de l'acheteur connecté
                    $contactController->listMessagesByBuyer($buyerId);
                    break;

                case "delete-message":
                    $ContactController = new ContactController();
                    $messageId = isset($_GET["id"]) ? (int) $_GET["id"] : 0; // récupère l'id du message pour la suppression
                    $ContactController->deleteMessage($messageId);
                    break;

                case "contactForm":
                    $contactController = new ContactController();
                    $contactController->showForm();
                    break;


                // PRODUCTS

                case "products-region":
                    $productController = new ProductController();
                    $productController->byRegion();
                    break;


                case "product-detail":
                    $productController = new ProductController();
                    $productController->detail();
                    break;


                //faq
                case 'faq':
                    $controller = new FaqController();
                    $controller->index();
                    break;

                // PRODUCTS CRUD - Producteur
                case "producer-dashboard":
                    $productController = new ProducerProductController();
                    $productController->listProductsByUserDashboard();
                    break;
                case "list-products":
                    $productController = new ProducerProductController();
                    $productController->listProducts();
                    break;

                case "list-products-by-user":
                    $productController = new ProducerProductController();

                    // Si user_id n'est pas passé dans l'URL, on prend celui de la session
                    if (isset($get["user_id"])) {
                        $userId = (int)$get["user_id"];
                    } elseif (isset($_SESSION['user_id'])) {
                        $userId = (int)$_SESSION['user_id'];
                    } else {
                        // Aucun utilisateur connecté -> redirige vers login
                        header("Location: index.php?route=login");
                        exit;
                    }
                    $productController->listProductsByUser($userId);
                    break;

                case "show-product":
                    $productController = new ProducerProductController();
                    $productController->showProduct();
                    break;

                case "create-product":
                    $productController = new ProducerProductController();
                    $productController->createProduct();
                    break;

                case "store-product":
                    $productController = new ProducerProductController();
                    $productController->storeProduct();
                    break;

                case "edit-product":
                    $productController = new ProducerProductController();
                    $productController->editProduct();
                    break;

                case "update-product":
                    $productController = new ProducerProductController();
                    $productController->updateProduct();
                    break;

                case "delete-product":
                    $productController = new ProducerProductController();
                    $productController->deleteProduct();
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
                    $authController = new AuthController();
                    $authController->logout();
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

