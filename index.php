<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
require"services/autoload.php";
$route = $_GET['route'] ?? 'default';
$router = new Router();
$router->handleRequest($route);


