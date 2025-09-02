<?php
namespace app\Managers;
use PDO;
class OrderManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }
    // Partie tableau de bord
    // Toutes les commandes
    public function findAll(): array
    {
        $query = $this->db->query("SELECT * FROM orders");
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $orders = [];
        foreach ($results as $row) {
            $orders[] = $this->createOrderFromRow($row);
        }
        return $orders;
    }

    // Commandes récentes
    public function findRecent(int $limit = 5): array
    {
        $query = $this->db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit");
        $query->bindValue(":limit", $limit, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $orders = [];
        foreach ($results as $row) {
            $orders[] = $this->createOrderFromRow($row);
        }
        return $orders;
    }

    // Méthode privée pour transformer une ligne DB en commande
    private function createOrderFromRow(array $row): array
    {
        return [
            "id"       => (int)$row["id"],
            "status"   => $row["status"],
            "total"    => $row["total_price"],
            "created"  => $row["created_at"]
            // ajouter d’autres champs si nécessaire
        ];
    }
}

