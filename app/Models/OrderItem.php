<?php
namespace app\Models;
class OrderItem
{
    private ?int $id = null;
    private ?int $order_id;
    private ?int $product_id;
    private float $unit_price;
    private ?int $quantity;
    private float $total;

    public function __construct(
        int $order_id,
        int $product_id,
        float $unit_price,
        int $quantity
    ) {
        $this->order_id = $order_id;
        $this->product_id = $product_id;
        $this->unit_price = $unit_price;
        $this->quantity = $quantity;
        $this->total = $unit_price * $quantity; // Calcul automatique du total
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): int
    {
        return $this->order_id;
    }

    public function getProductId(): int
    {
        return $this->product_id;
    }

    public function getUnitPrice(): float
    {
        return $this->unit_price;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setOrderId(?int $order_id): void
    {
        $this->order_id = $order_id;
    }

    public function setProductId(?int $product_id): void
    {
        $this->product_id = $product_id;
    }

    public function setUnitPrice(float $unit_price): void
    {
        $this->unit_price = $unit_price;
        $this->recalculateTotal();
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->recalculateTotal();
    }

    private function recalculateTotal(): void
    {
        $this->total = $this->unit_price * $this->quantity;
    }
}
