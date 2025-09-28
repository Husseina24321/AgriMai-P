<?php
namespace app\Managers;

use app\Models\Message;
use PDO;
use DateTime;
use Exception;
class ContactManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    // --- Méthodes existantes ---

    public function findAll(): array
    {
        $query = $this->db->query("SELECT * FROM messages ORDER BY sent_at DESC");
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($results as $row) {
            $messages[] = $this->createMessageFromRow($row);
        }
        return $messages;
    }

    public function findByReceiverId(int $receiverId): array
    {
        $query = $this->db->prepare("SELECT * FROM messages WHERE receiver_id = :receiver_id ORDER BY sent_at DESC");
        $query->execute(["receiver_id" => $receiverId]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($results as $row) {
            $messages[] = $this->createMessageFromRow($row);
        }
        return $messages;
    }

    public function findBySenderId(int $senderId): array
    {
        $query = $this->db->prepare("SELECT * FROM messages WHERE sender_id = :sender_id ORDER BY sent_at DESC");
        $query->execute(["sender_id" => $senderId]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($results as $row) {
            $messages[] = $this->createMessageFromRow($row);
        }
        return $messages;
    }

    public function findById(int $id): ?Message
    {
        $query = $this->db->prepare("SELECT * FROM messages WHERE id = :id");
        $query->execute(["id" => $id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createMessageFromRow($row) : null;
    }

    public function findMessagesForProducer(int $producerId): array
    {
        $sql = "
            SELECT 
                m.id AS message_id,
                m.content,
                m.sent_at,
                m.sender_id,
                m.receiver_id,
                m.product_id,
                u.first_name AS sender_first_name,
                u.last_name AS sender_last_name,
                p.title AS product_title
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            LEFT JOIN products p ON m.product_id = p.id
            WHERE m.receiver_id = :producer_id
            ORDER BY m.sent_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['producer_id' => $producerId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($results as $row) {
            $messageRow = [
                'id' => $row['message_id'],
                'sender_id' => $row['sender_id'],
                'receiver_id' => $row['receiver_id'],
                'product_id' => $row['product_id'],
                'content' => $row['content'],
                'sent_at' => $row['sent_at'],
                'sender_first_name' => $row['sender_first_name'],
                'sender_last_name' => $row['sender_last_name'],
                'product_title' => $row['product_title']
            ];
            $messages[] = $this->createMessageFromRow($messageRow);
        }

        return $messages;
    }

    public function create(array $data): Message
    {
        $sentAt = $data['sent_at'] ?? (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO messages (sender_id, receiver_id, product_id, content, sent_at)
            VALUES (:sender_id, :receiver_id, :product_id, :content, :sent_at)
        ");

        $stmt->execute([
            'sender_id' => $data['sender_id'] ?? 0,
            'receiver_id' => $data['receiver_id'],
            'product_id' => $data['product_id'] ?? null,
            'content' => $data['content'],
            'sent_at' => $sentAt
        ]);

        $id = (int)$this->db->lastInsertId();

        $row = [
            'id' => $id,
            'sender_id' => $data['sender_id'] ?? 0,
            'receiver_id' => $data['receiver_id'],
            'product_id' => $data['product_id'] ?? null,
            'content' => $data['content'],
            'sent_at' => $sentAt
        ];

        return $this->createMessageFromRow($row);
    }

    public function updateMessage(int $messageId, string $newContent): bool
    {
        $stmt = $this->db->prepare("UPDATE messages SET content = :content WHERE id = :id");
        return $stmt->execute([
            'content' => $newContent,
            'id' => $messageId
        ]);
    }

    public function deleteMessage(int $messageId)
    {
        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute(['id' => $messageId]);
    }

    public function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }


    private function createMessageFromRow(array $row): Message
    {
        // Conversion de sent_at en DateTime
        $sentAt = null;
        if (!empty($row['sent_at'])) {
            try {
                if ($row['sent_at'] instanceof \DateTime) {
                    $sentAt = $row['sent_at'];
                } else {
                    $sentAt = new \DateTime($row['sent_at']);
                }
            } catch (\Exception $e) {
                $sentAt = new \DateTime(); // fallback si la date est invalide
            }
        }

        $message = new Message(
            (int)$row["sender_id"],
            (int)$row["receiver_id"],
            isset($row["product_id"]) ? (int)$row["product_id"] : null,
            $row["content"],
            $sentAt
        );

        $message->setId((int)$row["id"]);
        $message->setProductId($row["product_id"] ?? null);

        // Propriétés optionnelles
        if (isset($row['sender_first_name'])) {
            $message->setSenderFirstName($row['sender_first_name']);
        }
        if (isset($row['sender_last_name'])) {
            $message->setSenderLastName($row['sender_last_name']);
        }
        if (isset($row['product_title'])) {
            $message->setProductTitle($row['product_title']);
        }

        return $message;
    }


    // --- Nouvelle méthode : récupérer tous les messages entre deux utilisateurs ---
    public function findMessagesByUserPair(int $userId1, int $userId2): array
    {
        $sql = "
            SELECT 
                m.id AS message_id,
                m.sender_id,
                m.receiver_id,
                m.product_id,
                m.content,
                m.sent_at,
                u.first_name AS sender_first_name,
                u.last_name AS sender_last_name,
                p.title AS product_title
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            LEFT JOIN products p ON m.product_id = p.id
            WHERE 
                (m.sender_id = :user1 AND m.receiver_id = :user2) OR
                (m.sender_id = :user2 AND m.receiver_id = :user1)
            ORDER BY m.sent_at ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user1' => $userId1,
            'user2' => $userId2
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($results as $row) {
            $messageRow = [
                'id' => $row['message_id'],
                'sender_id' => $row['sender_id'],
                'receiver_id' => $row['receiver_id'],
                'product_id' => $row['product_id'],
                'content' => $row['content'],
                'sent_at' => $row['sent_at'],
                'sender_first_name' => $row['sender_first_name'],
                'sender_last_name' => $row['sender_last_name'],
                'product_title' => $row['product_title']
            ];
            $messages[] = $this->createMessageFromRow($messageRow);
        }

        return $messages;
    }
}
