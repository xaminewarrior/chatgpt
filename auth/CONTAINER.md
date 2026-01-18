# Container (Dependency Injection) — Teaching Notes

This document explains **why** the container exists, **what** it does, and **how** it works in this project.

## 1) The problem it solves (why it exists)

When you build an MVC app, classes depend on other classes:

- `AuthController` needs `AuthService` and `Validator`.
- `AuthService` needs a `UserRepositoryInterface` and `UserFactory`.
- `UserRepository` needs a `Database` connection.

If you create all of those objects manually, your `index.php` becomes a long chain of `new` calls, and you repeat wiring in multiple places. That is not DRY and makes code hard to change.

**The container centralizes object creation** so you:

- Create objects in one place.
- Reuse shared objects (like the DB connection).
- Inject dependencies automatically.

## 2) What the container does (simple definition)

A **dependency injection container** is a class that:

1. **Builds objects** for you.
2. **Supplies their dependencies automatically**.
3. **Reuses instances** so you don’t create duplicates.

In short: it removes manual wiring.

## 3) Where it is used in this project

In `public/index.php`, we create a container and give it to `Core`:

```php
$config = require __DIR__ . '/../config.php';
$container = new Container($config);

new Core($container);
```

From that point on, the `Core` dispatcher can ask the container for any controller, and the container will automatically inject its dependencies.

## 4) How it works (step‑by‑step)

### 4.1 The `get()` method is the entry point

```php
public function get(string $id): object
{
    if (isset($this->instances[$id])) {
        return $this->instances[$id];
    }

    $object = $this->build($id);
    $this->instances[$id] = $object;

    return $object;
}
```

**Why:**
- If the object was already created, return it (singleton‑style reuse).
- Otherwise, build it, store it, and return it.

This prevents creating multiple DB connections or multiple copies of the same service.

### 4.2 The `build()` method handles special cases

```php
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
```

**Why:**
- Some classes need **manual wiring** (like `Database` needing config).
- Interfaces (like `UserRepositoryInterface`) need to resolve to a real class (`UserRepository`).
- If it’s not a special case, delegate to `resolve()`.

### 4.3 The `resolve()` method uses Reflection

```php
private function resolve(string $id): object
{
    $reflection = new ReflectionClass($id);

    if (!$reflection->isInstantiable()) {
        throw new RuntimeException("Class {$id} is not instantiable.");
    }

    $constructor = $reflection->getConstructor();
    if ($constructor === null) {
        return new $id();
    }

    $dependencies = [];
    foreach ($constructor->getParameters() as $parameter) {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType) {
            throw new RuntimeException("Cannot resolve untyped dependency for {$id}.");
        }

        $dependencies[] = $this->get($type->getName());
    }

    return $reflection->newInstanceArgs($dependencies);
}
```

**Why:**
- **Reflection** lets PHP inspect the constructor and see what it needs.
- If a class needs `AuthService`, the container will call `get(AuthService::class)` automatically.
- This keeps constructors clean and avoids manual wiring everywhere.

## 5) A real example (AuthController creation)

### When the Core needs the controller:
```php
$controller = $this->container->get('App\\Controllers\\AuthController');
```

### The container sees:
```php
public function __construct(
    private AuthService $auth,
    private Validator $validator
) {}
```

So it automatically does:

1. Create `AuthService` → which creates `UserRepository` → which creates `Database`.
2. Create `Validator`.
3. Pass both into the `AuthController` constructor.

You never have to do manual wiring.

## 6) What happens if you remove the container?

You must manually build everything in `index.php`:

```php
$db = new Database($config['db']);
$repo = new UserRepository($db);
$service = new AuthService($repo, new UserFactory());
$validator = new Validator();
$controller = new AuthController($service, $validator);
```

This works but becomes messy as your app grows.

## 7) When to use a container (best practice)

Use a container when:

- You have **many dependencies** across controllers/services.
- You want **clean constructors** and easy wiring.
- You want to follow **SOLID** and **DRY**.

Avoid a container when:

- Your app is tiny (2–3 classes only).
- You want absolute minimum code.

## 8) Summary (short answer)

The container is a **factory + dependency resolver** in one place. It exists to:

- Keep your `index.php` clean.
- Automatically inject dependencies.
- Centralize wiring and reduce duplication.
- Make your app easier to expand without rewriting object creation.
