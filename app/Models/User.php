<?php

enum UserRole: string
{
    case Admin = 'Admin';
    case Producer = 'Producteur';
    case Buyer = 'Acheteur';
}

class User
{
    private ?int $id = null;
    private string $first_name;
    private string $last_name;
    private string $email;
    private UserRole $role;
    private string $password;
    private DateTime $created_at;
    private ?DateTime $updated_at = null;
    private ?DateTime $deleted_at = null;

    public function __construct(
        string $first_name,
        string $last_name,
        string $email,
        string $password,
        UserRole $role = UserRole::Buyer
    ) {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->created_at = new DateTime();
        // updated_at et deleted_at restent null à la création
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deleted_at;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt(?DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function setDeletedAt(?DateTime $deleted_at): void
    {
        $this->deleted_at = $deleted_at;
    }
}


