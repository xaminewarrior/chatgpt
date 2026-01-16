<?php

declare(strict_types=1);

namespace App\Services;

use App\Factories\UserFactory;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;

final class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private UserFactory $factory
    ) {
    }

    public function register(string $name, string $email, string $password): User
    {
        $this->assertEmailAvailable($email);

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $user = $this->factory->createNew($name, $email, $hash);
        $id = $this->users->create($user);

        return new User(
            $id,
            $user->name,
            $user->email,
            $user->passwordHash,
            $user->createdAt
        );
    }

    public function attempt(string $email, string $password): ?User
    {
        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user->passwordHash)) {
            return null;
        }

        return $user;
    }

    public function currentUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->users->findById((int) $_SESSION['user_id']);
    }

    public function findUserById(int $id): ?User
    {
        return $this->users->findById($id);
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    private function assertEmailAvailable(string $email): void
    {
        if ($this->users->findByEmail($email)) {
            throw new \RuntimeException('Email is already registered.');
        }
    }
}
