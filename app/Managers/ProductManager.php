<?php
namespace app\Managers;

use PDO;
use app\Models\Product;
use app\Enum\ProductLocation;

class ProductManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    // Tous les produits
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE deleted_at IS NULL");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            $products[] = $this->createProductFromRow($row);
        }
        return $products;
    }

    // Produit par ID
    public function findById(int $id): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createProductFromRow($row) : null;
    }

    // Produits par utilisateur
    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE user_id = :user_id AND deleted_at IS NULL");
        $stmt->execute(['user_id' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            $products[] = $this->createProductFromRow($row);
        }
        return $products;
    }

    // Produits par localisation
    public function findByLocation(string $location): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE location = :location AND deleted_at IS NULL");
        $stmt->execute(['location' => $location]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            $products[] = $this->createProductFromRow($row);
        }
        return $products;
    }

    // Produit récent
    public function findRecent(int $limit = 5): array
    {
        $query = $this->db->prepare("
            SELECT * FROM products
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();

        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            $products[] = $this->createProductFromRow($row);
        }
        return $products;
    }

    // Crée un produit
    public function createProduct(Product $product): Product
    {
        $stmt = $this->db->prepare("
            INSERT INTO products (title, description, producer, price, quantity, image, user_id, location, created_at)
            VALUES (:title, :description, :producer, :price, :quantity, :image, :user_id, :location, NOW())
        ");
        $stmt->execute([
            'title'       => $product->getTitle(),
            'description' => $product->getDescription(),
            'producer'    => $product->getProducteur(),
            'price'       => $product->getPrice(),
            'quantity'    => $product->getQuantity(),
            'image'       => $product->getImage(),
            'user_id'     => $product->getUserId(),
            'location'    => $product->getLocation()->value
        ]);

        $product->setId((int)$this->db->lastInsertId());
        return $product;
    }
    public function userLogProducts(int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM products WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }

    // Met à jour un produit
    public function updateProduct(Product $product): void
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET title = :title,
                description = :description,
                producer = :producer,
                price = :price,
                quantity = :quantity,
                image = :image,
                location = :location,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'title'       => $product->getTitle(),
            'description' => $product->getDescription(),
            'producer'    => $product->getProducteur(),
            'price'       => $product->getPrice(),
            'quantity'    => $product->getQuantity(),
            'image'       => $product->getImage(),
            'location'    => $product->getLocation()->value,
            'id'          => $product->getId(),
        ]);
    }

    // Supprime un produit (soft delete)
    public function delete(Product $product): void
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $product->getId()]);
    }

    // Transforme une ligne DB en objet Product
    private function createProductFromRow(array $row): Product
    {
        $product = new Product(
            $row['title'],
            $row['description'],
            $row['producer'] ?? 'Producteur inconnu', // utiliser producer
            (float)$row['price'],
            (int)$row['quantity'],
            $row['image'],
            (int)$row['user_id'],
            ProductLocation::from($row['location'])
        );

        $product->setId((int)$row['id']);
        return $product;
    }

    // Récupère les produits par région et par nom
    public function findByRegionAndName(string $region, string $name): array
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM products 
            WHERE location = :region AND title = :name AND deleted_at IS NULL
            ORDER BY created_at DESC
        ");
        $stmt->execute([
            'region' => $region,
            'name'   => $name
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $products = [];
        foreach ($rows as $row) {
            $products[] = $this->createProductFromRow($row);
        }

        return $products;
    }
    // Dans ProductManager
    public function findByRegionAndNameAndProducer(string $region, string $title, string $producer): array
    {
        $stmt = $this->db->prepare("
        SELECT * 
        FROM products 
        WHERE location = :region AND title = :title AND producer = :producer AND deleted_at IS NULL
        ORDER BY created_at DESC
    ");
        $stmt->execute([
            'region'   => $region,
            'title'    => $title,
            'producer' => $producer
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($rows as $row) {
            $products[] = $this->createProductFromRow($row);
        }

        return $products;
    }

}
