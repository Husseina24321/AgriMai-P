<?php
namespace app\Managers;

use app\Enum\UserRole;
use app\Enum\UserStatus;
use app\Models\User;
use PDO;
use DateTime;

class UserManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    // Récupère tous les utilisateurs non supprimés
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($results as $row) {
            $users[] = $this->createUserFromRow($row);
        }
        return $users;
    }

    // Récupère les utilisateurs en attente (pending)
    public function findPending(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE status = 'Pending' AND deleted_at IS NULL ORDER BY created_at ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($results as $row) {
            $users[] = $this->createUserFromRow($row);
        }
        return $users;
    }

    // Récupère un utilisateur par ID
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createUserFromRow($row) : null;
    }

    // Récupère un utilisateur par email
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND deleted_at IS NULL");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createUserFromRow($row) : null;
    }

    // Crée un nouvel utilisateur
    public function createUser(User $user): User
    {
        $stmt = $this->db->prepare("
        INSERT INTO users (first_name, last_name, email, password, role, status, created_at)
        VALUES (:first_name, :last_name, :email, :password, :role, :status, NOW())
    ");
        $stmt->execute([
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName(),
            'email'      => $user->getEmail(),
            'password'   => $user->getPassword(),
            'role'       => $user->getRole()->value,
            'status'     => $user->getStatus()->value,
        ]);

        $user->setId((int)$this->db->lastInsertId());
        return $user;
    }

    // Met à jour un utilisateur
    public function updateUser(User $user): void
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET first_name = :first_name,
                last_name = :last_name,
                email = :email,
                password = :password,
                role = :role,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName(),
            'email'      => $user->getEmail(),
            'password'   => $user->getPassword(),
            'role'       => $user->getRole()->value,
            'status'     => $user->getStatus()->value,
            'id'         => $user->getId()
        ]);
    }

    // Supprime un utilisateur (soft delete)
    public function deleteUser(User $user): void
    {
        $stmt = $this->db->prepare("UPDATE users SET deleted_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user->getId()]);
    }

    // Valide un utilisateur (changer le statut)
    public function validateUser(User $user): void
    {
        $user->setStatus(UserStatus::Active);
        $this->updateUser($user);
    }

    // Transforme une ligne DB en objet User
    private function createUserFromRow(array $row): User
    {
        $user = new User(
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['password'],
            UserRole::from($row['role']),
            UserStatus::from($row['status'])
        );

        $user->setId((int)$row['id']);

        // Création sécurisée des dates
        try {
            $user->setCreatedAt(!empty($row['created_at']) ? new DateTime($row['created_at']) : new DateTime());
        } catch (\Exception) {
            $user->setCreatedAt(new DateTime());
        }

        try {
            $user->setUpdatedAt(!empty($row['updated_at']) ? new DateTime($row['updated_at']) : null);
        } catch (\Exception) {
            $user->setUpdatedAt(null);
        }

        try {
            $user->setDeletedAt(!empty($row['deleted_at']) ? new DateTime($row['deleted_at']) : null);
        } catch (\Exception) {
            $user->setDeletedAt(null);
        }

        return $user;
    }
    public function savePasswordReset(string $email, string $token, string $expires): void
    {
        $stmt = $this->db->prepare("
        INSERT INTO password_resets (email, token, expires_at)
        VALUES (:email, :token, :expires_at)
    ");
        $stmt->execute([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires
        ]);
    }

    public function getResetByToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
        SELECT * FROM password_resets
        WHERE token = :token
    ");
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function updatePasswordByEmail(string $email, string $hashedPassword): void
    {
        $stmt = $this->db->prepare("
        UPDATE users
        SET password = :password
        WHERE email = :email
    ");
        $stmt->execute([
            'password' => $hashedPassword,
            'email' => $email
        ]);
    }

    public function deleteResetByEmail(string $email): void
    {
        $stmt = $this->db->prepare("
        DELETE FROM password_resets
        WHERE email = :email
    ");
        $stmt->execute(['email' => $email]);
    }


}
