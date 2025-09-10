<?php
namespace app\Controllers;

use app\Managers\ProductManager;


class HomeController extends AbstractController
{
    public function index(): void
    {
        $productManager = new ProductManager();

        // Exemple : afficher les 5 derniers produits
        $recentProducts = $productManager->findRecent(3);

        // Récupérer les produits par région avec findByLocation()
        $regionProducts = [
            'Normandie' => $productManager->findByLocation('Normandie'),
            'Alsace'    => $productManager->findByLocation('Alsace'),
            'Loire'     => $productManager->findByLocation('Loire')
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
            $_SESSION['success-message'] = "Merci pour votre inscription à la newsletter, vous aurez bientôt de nos nouvelles !";
            $this->redirect('index.php');
        }
    }

   /* public function showByRegion(string $region): void
    {
        $productManager = new ProductManager();
        $products = $productManager->findByLocation($region); // correction ici

        $this->render('front/home.html.twig', [
            'regionProducts' => [$region => $products],
            'session' => $_SESSION
        ]);
    }
   */
}

