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


    public function normandie(): void
    {
        $products = $this->pm->findByRegionAndName(ProductLocation::Normandy->value, 'Reinette Grise du Canada');
        $this->render("/front/productsN.html.twig", [
            "categorie" => "Pommes Grise du Canada",
            "products" => $products
        ]);
    }


    // Liste des pommes Grise du Canada en Loire
    public function loire(): void
    {
        $products = $this->pm->findByRegionAndName('Loire', 'Pommes Grise du Canada');
        $this->render("/front/productsL.html.twig", [
            "categorie" => "Pommes Grise du Canada",
            "products" => $products
        ]);
    }

    // Liste des pommes Grise du Canada en Alsace
    public function alsace(): void
    {
        $products = $this->pm->findByRegionAndName('Alsace', 'Pommes Grise du Canada');
        $this->render("/front/productsA.html.twig", [
            "categorie" => "Pommes Grise du Canada",
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
