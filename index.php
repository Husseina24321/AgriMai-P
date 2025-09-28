<?php
// charge l'autoload de composer
require __DIR__ . "/vendor/autoload.php";
use services\Router;

session_start();

$router = new Router();
$router->handleRequest($_GET);

// charge le contenu du .env dans $_ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

