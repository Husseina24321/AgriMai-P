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

        $stmt = $this->db->prepare("
        INSERT INTO messages (sender_id, receiver_id, content, sent_at)
        VALUES (:sender_id, :receiver_id, :content, :sent_at)
    ");

        $stmt->execute([
            'sender_id'   => $data['sender_id'] ?? null,
            'receiver_id' => $data['receiver_id'] ?? null,
            'content'     => $data['content'],
            'sent_at'     => $sentAt,
        ]);

        // Récupérer l'ID du message créé
        $id = (int) $this->db->lastInsertId();

        // Construire une "fake row" pour réutiliser createMessageFromRow()
        $row = [
            'id' => $id,
            'sender_id' => $data['sender_id'] ?? null,
            'receiver_id' => $data['receiver_id'] ?? null,
            'content' => $data['content'],
            'sent_at' => $sentAt,
        ];

        return $this->createMessageFromRow($row);
    }

    private function createMessageFromRow(array $row): Message
    {
        $sentAt = isset($row["sent_at"]) ? new DateTime($row["sent_at"]) : null;
        $message = new Message(
            (int)$row["sender_id"],
            (int)$row["receiver_id"],
            $row["content"],
            $sentAt
        );
        $message->setId((int)$row["id"]);
        return $message;
    }

    public function findById(int $id): ?Message
    {
        $query = $this->db->prepare("SELECT * FROM messages WHERE id = :id");
        $query->execute(["id" => $id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createMessageFromRow($row) : null;
    }
}
