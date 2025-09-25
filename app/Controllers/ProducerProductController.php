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

    // Tableau de bord du producteur
    public function listProductsByUserDashboard(): void
    {
        // Vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            // Redirige vers la page de login si non connecté
            $this->redirect("index.php?route=login");
            return;
        }

        $userId = $_SESSION['user_id'];

        // Récupère tous les produits du producteur
        $products = $this->pm->findByUser($userId);

        // Rend le template productsByUser
        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/admin/productsByUser.html.twig", [
            "products" => $products,
            "csrfToken" => $csrfToken
        ]);
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

        // Vérifie que $products n'est pas vide
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
        // Vérifie si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            echo "Vous devez être connecté.";
            return;
        }

        $userId = $_SESSION['user_id'];

        // Important : ici, on ne bloque jamais l'accès au formulaire
        // même si le producteur a déjà des produits.
        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();

        $this->render("/admin/createProduct.html.twig", ["csrfToken" => $csrfToken]);
    }

    public function storeProduct(): void
    {
        // Vérifie que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        // Vérifie si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            echo "Vous devez être connecté pour créer un produit.";
            return;
        }

        $userId = $_SESSION['user_id'];

        // Vérifie si un fichier image a bien été uploadé
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo "Erreur : vous devez télécharger une image valide.";
            return;
        }

        // Vérifie que les champs obligatoires sont remplis
        if (empty($_POST['title']) || empty($_POST['producer']) || empty($_POST['price'])) {
            echo "Veuillez remplir tous les champs obligatoires.";
            return;
        }

        // Gère l'upload de l'image
        $imageName = $this->handleImageUpload($_FILES['image']);
        if (!$imageName) {
            echo "Erreur lors du téléchargement de l'image.";
            return;
        }

        // Gestion de la localisation (Normandie, Loire, Alsace)
        $validLocations = ['Normandie', 'Loire', 'Alsace'];
        $locationValue = $_POST['location'] ?? 'Normandie';
        if (!in_array($locationValue, $validLocations)) {
            $locationValue = 'Normandie';
        }
        $locationEnum = ProductLocation::tryFrom($locationValue) ?? ProductLocation::Normandy;

        // Création du produit avec toutes les informations fournies
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

        // Sauvegarde du produit en base
        $this->pm->createProduct($product);

        // Redirection vers la liste des produits du producteur
        $this->redirect("index.php?route=list-products-by-user&user_id=$userId");
    }


    // Affiche le formulaire d’édition
    public function editProduct(): void
    {
        $product = $this->getProductFromRequest();
        if (!$product) return;

        $csrfToken = (new CSRFTokenManager())->generateCSRFToken();
        $this->render("/admin/editProducts.html.twig", [
            "product" => $product,
            "csrfToken" => $csrfToken
        ]);
    }

    // Traite la mise à jour d’un produit
    public function updateProduct(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        // Vérifie que l'ID du produit est bien envoyé
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

        // Mise à jour des champs si fournis
        $product->setTitle($_POST['title'] ?? $product->getTitle());
        $product->setDescription($_POST['description'] ?? $product->getDescription());
        $product->setProducteur($_POST['producer'] ?? $product->getProducteur());
        $product->setPrice((float)($_POST['price'] ?? $product->getPrice()));
        $product->setQuantity((int)($_POST['quantity'] ?? $product->getQuantity()));

        // Gestion de la localisation avec sécurité
        if (!empty($_POST['location'])) {
            $product->setLocation(ProductLocation::tryFrom($_POST['location']) ?? $product->getLocation());
        }

        // Gestion de l'image si une nouvelle est uploadée
        $imageName = $this->handleImageUpload($_FILES['image'] ?? []);
        if ($imageName) {
            $product->setImage($imageName);
        }

        // Sauvegarde en base
        $this->pm->updateProduct($product);

        // Redirection vers la liste des produits du producteur
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