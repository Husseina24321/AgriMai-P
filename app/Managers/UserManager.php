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

    public function findAll(): array
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE deleted_at IS NULL");
        $query->execute();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->createUserFromRow($row);
        }
        return $users;
    }

    public function findById(int $id): ?User
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE id = :id AND deleted_at IS NULL");
        $query->execute(['id' => $id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createUserFromRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE email = :email AND deleted_at IS NULL");
        $query->execute(['email' => $email]);
        $row = $query->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createUserFromRow($row) : null;
    }

    public function findPending(): array
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE status = 'pending' AND deleted_at IS NULL");
        $query->execute();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->createUserFromRow($row);
        }
        return $users;
    }

    public function findActiveUsers(): array
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE status = 'active' AND deleted_at IS NULL");
        $query->execute();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->createUserFromRow($row);
        }
        return $users;
    }

    public function findBannedUsers(): array
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE status = 'banned' AND deleted_at IS NULL");
        $query->execute();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->createUserFromRow($row);
        }
        return $users;
    }

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
            'status'     => UserStatus::Pending->value
        ]);

        $user->setId((int)$this->db->lastInsertId());
        $user->setStatus(UserStatus::Pending);
        $user->setCreatedAt(new DateTime());

        return $user;
    }

    public function updateUser(User $user): void
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            UPDATE users SET 
                first_name = :firstname,
                last_name  = :lastname,
                role       = :role,
                status     = :status,
                email      = :email,
                password   = :password,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->execute([
            "firstname"  => $user->getFirstName(),
            "lastname"   => $user->getLastName(),
            "role"       => $user->getRole()->value,
            "status"     => $user->getStatus()->value,
            "email"      => $user->getEmail(),
            "password"   => $user->getPassword(),
            "updated_at" => $currentDateTime,
            "id"         => $user->getId()
        ]);
    }

    public function deleteUser(User $user): void
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("UPDATE users SET deleted_at = :deleted_at WHERE id = :id");
        $stmt->execute([
            "deleted_at" => $currentDateTime,
            "id"         => $user->getId()
        ]);
    }

    public function validateUser(User $user): void
    {
        $stmt = $this->db->prepare("UPDATE users SET status = 'active', updated_at = :updated_at WHERE id = :id");
        $stmt->execute([
            "updated_at" => date('Y-m-d H:i:s'),
            "id"         => $user->getId()
        ]);
        $user->setStatus(UserStatus::Active);
    }

    // Statistiques
    public function getTotalUsers(): int
    {
        $query = $this->db->query("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL");
        return (int)$query->fetchColumn();
    }

    public function getPendingUsers(): int
    {
        $query = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'pending' AND deleted_at IS NULL");
        return (int)$query->fetchColumn();
    }

    public function getBannedUsers(): int
    {
        $query = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'banned' AND deleted_at IS NULL");
        return (int)$query->fetchColumn();
    }

    private function createUserFromRow(array $row): User
    {
        $user = new User(
            $row["first_name"],
            $row["last_name"],
            $row["email"],
            $row["password"],
            UserRole::from($row["role"]),
            UserStatus::from($row["status"])
        );
        $user->setId((int)$row["id"]);
        return $user;
    }
}

