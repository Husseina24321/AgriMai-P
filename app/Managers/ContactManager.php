<?php
namespace app\Managers;

use app\Models\Message;
use PDO;
use DateTime;

class ContactManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

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

    public function delete(Message $message): void
    {
        $query = $this->db->prepare("DELETE FROM messages WHERE id = :id");
        $query->execute(["id" => $message->getId()]);
    }

    public function create(array $data): Message
    {
        $sentAt = $data['sent_at'] ?? (new DateTime())->format('Y-m-d H:i:s');

        // Insertion en base
        $stmt = $this->db->prepare("
            INSERT INTO messages (sender_id, receiver_id, product_id, content, sent_at)
            VALUES (:sender_id, :receiver_id, :product_id, :content, :sent_at)
        ");

        $stmt->execute([
            'sender_id'   => $data['sender_id'] ?? 0,
            'receiver_id' => $data['receiver_id'],
            'product_id'  => $data['product_id'] ?? null,
            'content'     => $data['content'],
            'sent_at'     => $sentAt,
        ]);

        $id = (int) $this->db->lastInsertId();

        $row = [
            'id'          => $id,
            'sender_id'   => $data['sender_id'] ?? 0,
            'receiver_id' => $data['receiver_id'],
            'product_id'  => $data['product_id'] ?? null,
            'content'     => $data['content'],
            'sent_at'     => $sentAt,
        ];

        $message = $this->createMessageFromRow($row);

        // Envoi email si l'email du producteur existe
        $receiver = $this->getUserById($data['receiver_id']);
        if (!empty($receiver['email'])) {
            $subject = "Nouveau message reçu pour votre produit";
            $body = "Vous avez reçu un nouveau message :\n\n" . $message->getContent();
            mail($receiver['email'], $subject, $body);
        }

        return $message;
    }



    private function createMessageFromRow(array $row): Message
    {
        $sentAt = null;
        if (!empty($row['sent_at'])) {
            try {
                $sentAt = new DateTime($row['sent_at']);
            } catch (\Exception) {
                $sentAt = new DateTime();
            }
        }

        $message = new Message(
            (int)$row["sender_id"],
            (int)$row["receiver_id"],
            isset($row["product_id"]) ? (int)$row["product_id"] : null, // Product ID correct
            $row["content"],                                            // Contenu
            $sentAt
        );
        $message->setId((int)$row["id"]);
        $message->setProductId($row["product_id"] ?? null);

        // Propriétés supplémentaires
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


    public function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
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
            // On prépare le tableau pour createMessageFromRow
            $messageRow = [
                'id'          => $row['message_id'],
                'sender_id'   => $row['sender_id'],
                'receiver_id' => $row['receiver_id'],
                'product_id'  => $row['product_id'],
                'content'     => $row['content'],
                'sent_at'     => $row['sent_at'],
                'sender_first_name' => $row['sender_first_name'],
                'sender_last_name'  => $row['sender_last_name'],
                'product_title'     => $row['product_title']
            ];

            $messages[] = $this->createMessageFromRow($messageRow);
        }

        return $messages;
    }
    public function findUnreadMessagesForProducer(int $producerId): array
    {
        $sql = "
        SELECT 
            m.id AS message_id,
            m.content,
            m.sent_at,
            m.sender_id,
            m.receiver_id,
            m.product_id,
            m.is_read,
            u.first_name AS sender_first_name,
            u.last_name AS sender_last_name,
            p.title AS product_title
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        LEFT JOIN products p ON m.product_id = p.id
        WHERE m.receiver_id = :producer_id AND m.is_read = 0
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
                'product_title' => $row['product_title'],
                'is_read' => $row['is_read']
            ];

            $messages[] = $this->createMessageFromRow($messageRow);
        }

        return $messages;
    }


}
