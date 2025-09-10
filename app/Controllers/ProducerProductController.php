<?php
namespace app\Controllers;
use app\Managers\ProductManager;
use app\Models\Product;
use Services\CSRFTokenManager;
//list d'un produit d'un producteur
//une page (show) qui va montrer les détails d'un produit
// une page de creation d'un produit
// Une page d'édition d'un produit
//une page ou méthode de suppression d'un produit

class ProducerProductController extends AbstractController
{
    private ProductManager $pm;

    public function __construct()
    {
        parent::__construct();
        $this->pm = new ProductManager();
    }

    // Récupère un produit depuis l'ID passé en GET
    private function getProductFromRequest(): ?Product
    {
        if (!isset($_GET["id"])) {
            echo "ID produit manquant.";
            return null;
        }

        $product = $this->pm->findById((int)$_GET["id"]);
        if (!$product) {
            echo "Le produit n'existe pas.";
            return null;
        }

        return $product;
    }

    // Liste tous les produits disponibles
    public function listProducts(): void
    {
        $products = $this->pm->findAll();
        $this->render("/front/products.html.twig", ["products" => $products]);
    }

    // Liste tous les produits créés par un utilisateur spécifique
    public function listProductsByUser(int $userId): void
    {
        $products = $this->pm->findByUser($userId);
        $this->render("/front/productsByUser.html.twig", ["products" => $products]);
    }

    // Liste les produits disponibles dans une localisation donnée
    public function listProductsByLocation(string $location): void
    {
        $products = $this->pm->findByLocation($location);
        $this->render("/front/productsByLocation.html.twig", ["products" => $products]);
    }

    // Affiche le formulaire de création d’un nouveau produit avec token CSRF
    public function createProduct(): void
    {
        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/front/producerDashboard.html.twig", ["csrfToken" => $csrfToken]);
    }

    // Affiche le formulaire d’édition pour un produit existant
    public function editProduct(): void
    {
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/admin/productsEdit.html.twig", [
            "product" => $product,
            "csrfToken" => $csrfToken
        ]);
    }

    // Supprime un produit existant
    public function deleteProduct(): void
    {
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $this->pm->delete($product);
        $this->redirect("index.php?route=list-products");
    }

    // Affiche le formulaire spécifique au producteur
    public function showForm(): void
    {
        var_dump('coco');
        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/front/producerDashboard.html.twig", ["csrfToken" => $csrfToken]);
    }
}
