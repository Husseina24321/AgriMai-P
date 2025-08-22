<?php
enum ProductLocation: string
{
    case Normandy = 'Normandie';
    case Loire = 'Loire';
    case Alsace = 'Alsace';
}

class Product
{
    private ?int $id = null;
    private string $title;
    private string $description;
    private float $price;
    private int $quantity;
    private string $image;
    private int $user_id;
    private ProductLocation $location;

    public function __construct(
        string $title,
        string $description,
        float $price,
        int $quantity,
        string $image,
        int $user_id,
        ProductLocation $location = ProductLocation::Normandy
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->image = $image;
        $this->user_id = $user_id;
        $this->location = $location;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getLocation(): ProductLocation
    {
        return $this->location;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setLocation(ProductLocation $location): void
    {
        $this->location = $location;
    }
}

