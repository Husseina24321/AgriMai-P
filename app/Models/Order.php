<?php
    Enum OrderStatus: string
    {
        case Pending = 'En attente';
        case Approved = 'Validée';
        case Shipped = 'Expédiée';
    }
    class Order
    {
        private ?int $id = null;
        private int $buyer_id;
        private DateTime $created_at;
        private ?DateTime $updated_at = null;
        private orderStatus $status;
        public function __construct(
        int $buyer_id,
        DateTime $created_at,
        ?DateTime $updated_at,
        OrderStatus $status = orderStatus::Pending
    )
    {
        $this->buyer_id = $buyer_id;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->status = $status;

    }
        // Getters
        public function getId(): ?int
        {
            return $this->id;
        }
        public function getBuyerId(): ?int
        {
            return $this->buyer_id;
        }

        public function getCreatedAt(): DateTime
        {
            return $this->created_at;
        }
        public function getUpdatedAt(): ?DateTime
        {
            return $this->updated_at;
        }
        public function getStatus(): OrderStatus
        {
            return $this->status;
        }
        // Setters
        public function setId(?int $id): void
        {
            $this->id = $id;
        }
        public function setBuyerId(?int $buyer_id): void
        {
            $this->buyer_id = $buyer_id;
        }
        public function setCreatedAt(DateTime $created_at): void
        {
            $this->created_at = $created_at;
        }
        public function setUpdatedAt(DateTime $updated_at): void
        {
            $this->updated_at = $updated_at;
        }
        public function setStatus(OrderStatus $status): void
        {
            $this->status = $status;
        }
    }
