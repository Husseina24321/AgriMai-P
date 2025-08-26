<?php

class Router {
    public function handleRequest(array $get)
    {
        if (!empty($get['route'])) {
            switch ($get['route']) {
                // USERS
                case "list-users":
                    $userController = new UserController();
                    $userController->list();
                    // UserController::list();
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
                    //UserController::checkUpdateUser();
                    break;
                case "create-user":
                    $userController = new UserController();
                    $userController->createUser();
                    //UserController::createUser();
                    break;
                case "check-create-user":
                    $userController = new UserController();
                    $userController->checkCreateUser();
                    //UserController::checkCreateUser();
                    break;
                case "delete-user":
                    $userController = new UserController();
                    $userController->deleteUser();
                    //UserController::deleteUser();
                    break;

                // MESSAGES
                case "list-messages":
                    ContactController::listMessages();
                    break;
                case "list-messages-by-user":
                    break;
                case "delete-message":
                    break;

                // PRODUCTS
                case "list-products":
                    break;
                case "details-product":
                    break;
                case "list-products-by-user":
                    break;
                case "create-product":
                    break;
                case "check-create-product":
                    break;
                case "update-product":
                    break;
                case "check-update-product":
                    break;
                case "delete-product":
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

                // DASHBOARD
                case "dashboard":
                    // DashboardController::index();
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
                    // AuthController::check-register();
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

