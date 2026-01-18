<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(private Database $database)
    {
    }

    public function findByEmail(string $email): ?User
    {
        $statement = $this->database->connection()->prepare(
            'SELECT id, name, email, password_hash, created_at FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findById(int $id): ?User
    {
        $statement = $this->database->connection()->prepare(
            'SELECT id, name, email, password_hash, created_at FROM users WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function create(User $user): int
    {
        $statement = $this->database->connection()->prepare(
            'INSERT INTO users (name, email, password_hash, created_at) VALUES (:name, :email, :password_hash, :created_at)'
        );
        $statement->execute([
            'name' => $user->name,
            'email' => $user->email,
            'password_hash' => $user->passwordHash,
            'created_at' => $user->createdAt,
        ]);

        return (int) $this->database->connection()->lastInsertId();
    }

    private function mapRowToUser(array $row): User
    {
        return new User(
            (int) $row['id'],
            $row['name'],
            $row['email'],
            $row['password_hash'],
            $row['created_at']
        );
    }
}
