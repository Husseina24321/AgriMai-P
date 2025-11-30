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

    private function handleImageUpload(array $file): ?string
    {
        if (empty($file['name'])) return null;

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . './public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '-' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);

        return $fileName;
    }

    // Tableau de bord du producteur
    public function listProductsByUserDashboard(): void
    {
        $this->requireLogin(); // sécurisation
        $userId = $_SESSION['user']['id'];

        $products = $this->pm->findByUser($userId);

        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/admin/productsByUser.html.twig", [
            "products" => $products,
            "csrfToken" => $csrfToken
        ]);
    }

    public function listProducts(): void
    {
        $products = $this->pm->findAll();
        $this->render("/front/products.html.twig", ["products" => $products]);
    }

    public function listProductsByUser(int $userId): void
    {
        $this->requireLogin(); // sécurisation
        $products = $this->pm->findByUser($userId);

        $this->render("/admin/productsByUser.html.twig", ["products" => $products]);
    }

    public function showProduct(): void
    {
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $this->render("/front/productDetails.html.twig", ["product" => $product]);
    }

    public function createProduct(): void
    {
        $this->requireLogin(); // sécurisation
        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/admin/createProduct.html.twig", ["csrfToken" => $csrfToken]);
    }

    public function storeProduct(): void
    {
        $this->requireLogin(); // sécurisation

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $userId = $_SESSION['user']['id'];

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo "Erreur : vous devez télécharger une image valide.";
            return;
        }

        if (empty($_POST['title']) || empty($_POST['producer']) || empty($_POST['price'])) {
            echo "Veuillez remplir tous les champs obligatoires.";
            return;
        }

        $imageName = $this->handleImageUpload($_FILES['image']);
        if (!$imageName) {
            echo "Erreur lors du téléchargement de l'image.";
            return;
        }

        $validLocations = ['Normandie', 'Loire', 'Alsace'];
        $locationValue = $_POST['location'] ?? 'Normandie';
        if (!in_array($locationValue, $validLocations)) {
            $locationValue = 'Normandie';
        }
        $locationEnum = ProductLocation::tryFrom($locationValue) ?? ProductLocation::Normandy;

        $product = new Product(
            $_POST['title'],
            $_POST['description'] ?? '',
            $_POST['producer'],
            (float)$_POST['price'],
            (int)($_POST['quantity'] ?? 0),
            $imageName,
            $userId,
            $locationEnum
        );

        $this->pm->createProduct($product);
        $this->redirect("index.php?route=list-products-by-user&user_id=$userId");
    }

    public function editProduct(): void
    {
        $this->requireLogin(); //  sécurisation
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/admin/editProducts.html.twig", [
            "product" => $product,
            "csrfToken" => $csrfToken
        ]);
    }

    public function updateProduct(): void
    {
        $this->requireLogin(); //sécurisation

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (!isset($_POST['id'])) {
            echo "ID produit manquant.";
            return;
        }

        $productId = (int)$_POST['id'];
        $product = $this->pm->findById($productId);
        if (!$product) {
            echo "Produit introuvable.";
            return;
        }

        $product->setTitle($_POST['title'] ?? $product->getTitle());
        $product->setDescription($_POST['description'] ?? $product->getDescription());
        $product->setProducteur($_POST['producer'] ?? $product->getProducteur());
        $product->setPrice((float)($_POST['price'] ?? $product->getPrice()));
        $product->setQuantity((int)($_POST['quantity'] ?? $product->getQuantity()));

        if (!empty($_POST['location'])) {
            $product->setLocation(ProductLocation::tryFrom($_POST['location']) ?? $product->getLocation());
        }

        $imageName = $this->handleImageUpload($_FILES['image'] ?? []);
        if ($imageName) {
            $product->setImage($imageName);
        }

        $this->pm->updateProduct($product);
        $this->redirect("index.php?route=list-products-by-user&user_id=" . $_SESSION['user']['id']);
    }

    public function deleteProduct(): void
    {
        $this->requireLogin(); // sécurisation
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $this->pm->delete($product);
        $this->redirect("index.php?route=list-products-by-user&user_id=" . $_SESSION['user']['id']);
    }

}