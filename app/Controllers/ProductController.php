<?php
namespace app\Controllers;

use app\Managers\ProductManager;
use app\Models\Product;
use app\Enum\ProductLocation;
use services\CsrfTokenManager;

class ProductController extends AbstractController
{
    private ProductManager $pm;

    public function __construct()
    {
        parent::__construct();
        $this->pm = new ProductManager();
    }

    // Méthode privée pour récupérer un produit depuis l'ID passé en GET
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

    // Liste des pommes Grise du Canada en Normandie


    public function byRegion(): void
    {
        if (!isset($_GET['region'])) {
            echo "Région manquante.";
            return;
        }

        $region = $_GET['region'];

        // Vérifie que la région est valide
        $validRegions = ['Normandie', 'Loire', 'Alsace'];
        if (!in_array($region, $validRegions)) {
            echo "Région invalide.";
            return;
        }

        $products = $this->pm->findByRegionAndName($region, 'Reinette Grise du Canada');

        $this->render("/front/productsByRegion.html.twig", [
            "region"   => $region,
            "products" => $products
        ]);
    }


    // Affiche les détails d'un produit
    public function detail(): void
    {
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $this->render("/front/productDetail.html.twig", [
            "product" => $product
        ]);
    }
}
