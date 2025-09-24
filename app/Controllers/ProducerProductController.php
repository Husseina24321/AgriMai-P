<?php
namespace app\Controllers;
use app\Managers\ProductManager;
use app\Models\Product;
use services\CsrfTokenManager;
use app\Enum\ProductLocation;
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

    // Méthode privée pour gérer l’upload d’image
    private function handleImageUpload(array $file): ?string
    {
        if (empty($file['name'])) return null;

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/AgriMai/public/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '-' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);

        return $fileName;
    }

    // Liste tous les produits
    public function listProducts(): void
    {
        $products = $this->pm->findAll();
        $this->render("/front/products.html.twig", ["products" => $products]);
    }

    // Liste les produits d’un producteur
    public function listProductsByUser(int $userId): void
    {
        // Récupère les produits de l'utilisateur
        $products = $this->pm->findByUser($userId);

        // 🔹 Vérifie que $products n'est pas vide
        if (!empty($products)) {
            foreach ($products as $product) {
                echo "ID produit : " . $product->getId() . "<br>";
                echo "Image : " . $product->getImage() . "<br>";
            }
        } else {
            echo "Aucun produit trouvé pour l'utilisateur $userId";
        }


        // Affichage normal si debug passé
        $this->render("/admin/productsByUser.html.twig", ["products" => $products]);
    }

    // Affiche les détails d’un produit
    public function showProduct(): void
    {
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $this->render("/front/productDetails.html.twig", ["product" => $product]);
    }

    // Affiche le formulaire de création
    public function createProduct(): void
    {
        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/admin/producerDashboard.html.twig", ["csrfToken" => $csrfToken]);
    }

    // Traite la création d’un produit
    public function storeProduct(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (!isset($_SESSION['user_id'])) {
            echo "Vous devez être connecté pour créer un produit.";
            return;
        }

        $userId = $_SESSION['user_id'];

        if (empty($_FILES['image']['name'])) {
            echo "Erreur : vous devez télécharger une image pour ce produit.";
            return;
        }

        $imageName = $this->handleImageUpload($_FILES['image']);

        if (!$imageName) {
            echo "Erreur lors du téléchargement de l'image.";
            return;
        }

        // Gestion de la localisation
        $validLocations = ['Normandie', 'Loire', 'Alsace'];
        $locationValue = $_POST['location'] ?? 'Normandie';

        // Si la valeur n'est pas valide, on met Normandie par défaut
        if (!in_array($locationValue, $validLocations)) {
            $locationValue = 'Normandie';
        }

        // Conversion en enum pour éviter l'erreur TypeError
        $locationEnum = ProductLocation::tryFrom($locationValue) ?? ProductLocation::Normandy;

        // Création du produit
        $product = new Product(
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $_POST['producer'] ?? '',
            (float)($_POST['price'] ?? 0),
            (int)($_POST['quantity'] ?? 0),
            $imageName,
            $userId,
            $locationEnum
        );

        // Sauvegarde en base
        $this->pm->createProduct($product);

        // Redirection vers la liste des produits du producteur
        $this->redirect("index.php?route=list-products-by-user");
    }

    // Affiche le formulaire d’édition
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

    // Traite la mise à jour d’un produit
    public function updateProduct(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $product = $this->pm->findById((int)$_POST['id']);
        if (!$product) return;

        $product->setTitle($_POST['title'] ?? $product->getTitle());
        $product->setDescription($_POST['description'] ?? $product->getDescription());
        $product->setProducteur($_POST['producer'] ?? $product->getProducteur());
        $product->setPrice((float)($_POST['price'] ?? $product->getPrice()));
        $product->setQuantity((int)($_POST['quantity'] ?? $product->getQuantity()));
        $product->setLocation(ProductLocation::from($_POST['location'] ?? $product->getLocation()->value));

        $imageName = $this->handleImageUpload($_FILES['image'] ?? []);
        if ($imageName) $product->setImage($imageName);

        $this->pm->updateProduct($product);

        $this->redirect("index.php?route=list-products-by-user&user_id=" . $_SESSION['user_id']);
    }

    // Supprime un produit
    public function deleteProduct(): void
    {
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $this->pm->delete($product);
        $this->redirect("index.php?route=list-products-by-user&user_id=" . $_SESSION['user_id']);
    }
}