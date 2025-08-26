<?php
session_start();

// charge l'autoload de composer
require __DIR__ . "/vendor/autoload.php";
// charge le contenu du .env dans $_ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Détermine la route demandée
$route = $_GET['route'] ?? 'home';

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
        break;

    case 'checkLogin':
        $controller = new AuthController();
        $controller->checkLogin();
        break;

    case 'listUsers':
        $controller = new UserController();
        $controller->list();
        break;

    case 'detailsUser':
        $controller = new UserController();
        $controller->detailsUser();
        break;

    default:
        // Page d'accueil
        echo "<h1>Bienvenue sur AgriMai</h1>";
        break;
}



