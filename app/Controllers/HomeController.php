<?php
namespace app\Controllers;

use app\Managers\ProductManager;
use app\Managers\UserManager;
use app\Managers\ContactManager;

class HomeController extends AbstractController
{
    public function index(): void
    {
        $productManager = new ProductManager();

        // Exemple : afficher les 5 derniers produits
        $recentProducts = $productManager->findRecent(5);

        // Récupérer les produits par région si besoin
        $regionProducts = [
            'Normandie' => $productManager->findByRegion('Normandie'),
            'Alsace' => $productManager->findByRegion('Alsace'),
            'Loire' => $productManager->findByRegion('Loire')
        ];

        // Affichage page d'accueil
        $this->render('front/home.html.twig', [
            'recentProducts' => $recentProducts,
            'regionProducts' => $regionProducts,
            'session' => $_SESSION
        ]);
    }

    public function subscribeNewsletter(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            // TODO : enregistre l'email dans une table newsletter
            $_SESSION['success-message'] = "Merci pour votre inscription à la newsletter, vous auriez bientot de nos nouvelles !";
            $this->redirect('index.php');
        }
    }
    public function showByRegion(string $region): void
    {
        $productManager = new ProductManager();
        $products = $productManager->findByRegion($region);

        $this->render('front/home.html.twig', [
            'regionProducts' => [$region => $products],
            'session' => $_SESSION
        ]);
    }
}
