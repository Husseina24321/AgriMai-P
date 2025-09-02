<?php
namespace app\Controllers;
use app\Managers\UserManager;
use app\Managers\ProductManager;
use app\Managers\OrderManager;
use app\Managers\ContactManager;

class DashboardController extends AbstractController
{
    public function index(): void
    {
        $userManager = new UserManager();
        $productManager = new ProductManager();
        $orderManager = new OrderManager();
        $messageManager = new ContactManager();

        // Tableau de bord : récupération des données
        $data = [
            // Utilisateurs
            "totalUsers"       => $userManager->getTotalUsers(),
            "activeUsers"      => $userManager->getTotalUsers() - $userManager->getPendingUsers() - $userManager->getBannedUsers(),
            "pendingUsers"     => $userManager->getPendingUsers(),
            "bannedUsers"      => $userManager->getBannedUsers(),

            // Produits
            "totalProducts"    => count($productManager->findAll()),
            "recentProducts"   => $productManager->findRecent(),

            // Commandes
            "totalOrders"      => count($orderManager->findAll()),
            "recentOrders"     => $orderManager->findRecent(),

            // Messages
            "totalMessages"    => count($messageManager->findAll())
        ];

        // Affichage de la vue avec les données
        $this->render("admin/dashboard.html.twig", $data);
    }
}
