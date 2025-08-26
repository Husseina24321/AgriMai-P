<?php
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
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($results as $result) {
            $user = new User(
                $result["first_name"],
                $result["last_name"],
                $result["email"],
                $result["password"],
                $result["role"]
            );
            $user->setId((int)$result["id"]);
            $users[] = $user;
        }

        return $users;
    }

    public function findByEmail(string $email): ?User
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL');
        $query->execute(['email' => $email]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $user = new User(
                $result["first_name"],
                $result["last_name"],
                $result["email"],
                $result["password"],
                $result["role"]
            );
            $user->setId((int)$result["id"]);
            return $user;
        }

        return null;
    }

    public function findById(int $id): ?User
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL');
        $query->execute(['id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $user = new User(
                $result["first_name"],
                $result["last_name"],
                $result["email"],
                $result["password"],
                $result["role"]
            );
            $user->setId((int)$result["id"]);
            return $user;
        }

        return null;
    }

    public function createUser(User $user): void
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $query = $this->db->prepare("
            INSERT INTO users (first_name, last_name, role, email, password, created_at, updated_at)
            VALUES (:firstname, :lastname, :role, :email, :password, :created_at, :updated_at)
        ");

        $parameters = [
            "firstname"   => $user->getFirstName(),
            "lastname"    => $user->getLastName(),
            "role"        => $user->getRole(),
            "email"       => $user->getEmail(),
            "password"    => $user->getPassword(),
            "created_at"  => $currentDateTime,
            "updated_at"  => $currentDateTime
        ];

        $query->execute($parameters);
        $user->setId((int)$this->db->lastInsertId());
    }

    public function updateUser(User $user): void
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $query = $this->db->prepare("
            UPDATE users 
            SET first_name = :firstname, 
                last_name = :lastname, 
                role = :role, 
                email = :email, 
                password = :password, 
                updated_at = :updated_at
            WHERE id = :id
        ");

        $parameters = [
            "firstname"  => $user->getFirstName(),
            "lastname"   => $user->getLastName(),
            "role"       => $user->getRole(),
            "email"      => $user->getEmail(),
            "password"   => $user->getPassword(),
            "updated_at" => $currentDateTime,
            "id"         => $user->getId()
        ];

        $query->execute($parameters);
    }

    public function deleteUser(User $user): void
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $query = $this->db->prepare("
            UPDATE users 
            SET deleted_at = :deleted_at 
            WHERE id = :id
        ");

        $query->execute([
            'deleted_at' => $currentDateTime,
            'id'         => $user->getId()
        ]);
    }
}
