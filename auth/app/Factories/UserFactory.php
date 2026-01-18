<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\User;

final class UserFactory
{
    public function createNew(string $name, string $email, string $passwordHash): User
    {
        return new User(
            null,
            $name,
            $email,
            $passwordHash,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        );
    }
}
