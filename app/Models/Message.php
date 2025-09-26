<?php
namespace app\Models;
use DateTime;
class Message
{
    private ?int $id = null;
    private int $sender_id;
    private int $receiver_id;
    private ?int $product_id;
    private string $content;
    private ?DateTime $sent_at;

    public function __construct(
        int $sender_id,
        int $receiver_id,
        int $product_id,
        string $content,
        ?DateTime $sent_at = null
    ) {
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->product_id = $product_id;
        $this->content = $content;
        $this->sent_at = $sent_at ?? new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderId(): int
    {
        return $this->sender_id;
    }

    public function getReceiverId(): int
    {
        return $this->receiver_id;
    }
    public function getProductId(): ?int
    {
        return $this->product_id;
    }


    public function getContent(): string
    {
        return $this->content;
    }

    public function getSentAt(): DateTime
    {
        return $this->sent_at;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setSenderId(int $sender_id): void
    {
        $this->sender_id = $sender_id;
    }

    public function setReceiverId(int $receiver_id): void
    {
        $this->receiver_id = $receiver_id;
    }

    public function setProductId(?int $productId): void
    {
        $this->product_id = $productId;
    }
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setSentAt(DateTime $sent_at): void
    {
        $this->sent_at = $sent_at;
    }
}
