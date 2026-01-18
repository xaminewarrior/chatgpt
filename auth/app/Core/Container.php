<?php

declare(strict_types=1);

namespace App\Core;

use App\Factories\UserFactory;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use ReflectionClass;
use ReflectionNamedType;

final class Container
{
    private array $instances = [];

    public function __construct(private array $config)
    {
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $object = $this->build($id);
        $this->instances[$id] = $object;

        return $object;
    }

    private function build(string $id): object
    {
        if ($id === Database::class) {
            return new Database($this->config['db']);
        }

        if ($id === UserRepositoryInterface::class) {
            return new UserRepository($this->get(Database::class));
        }

        if ($id === AuthService::class) {
            return new AuthService($this->get(UserRepositoryInterface::class), new UserFactory());
        }

        if ($id === Validator::class) {
            return new Validator();
        }

        return $this->resolve($id);
    }

    private function resolve(string $id): object
    {
        $reflection = new ReflectionClass($id);

        if (!$reflection->isInstantiable()) {
            throw new \RuntimeException("Class {$id} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $id();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof ReflectionNamedType) {
                throw new \RuntimeException("Cannot resolve untyped dependency for {$id}.");
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
