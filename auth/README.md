# PHP Auth System (MVC + Design Patterns)

This is a lightweight MVC authentication system built **without a framework**, using SOLID-friendly layering and a few classic patterns.

## Goals

- **Frameworkless** PHP to show how the pieces fit together.
- **Repository + Service + Factory** to keep responsibilities narrow.
- **Middleware + Validation** to keep controllers slim and DRY.
- **Convention-based routing** (controller/method/params) like a classic PHP MVC core.

## Requirements

- PHP 8.1+
- MySQL (or compatible)

## Setup

1. Create a database and run the schema:

   ```sql
   CREATE DATABASE auth_demo;
   USE auth_demo;
   SOURCE database/schema.sql;
   ```

2. Update database credentials in `config.php`.
3. Start a local server from the `auth/public` directory:

   ```bash
   php -S localhost:8000
   ```

4. Visit:

- `http://localhost:8000/auth/showLogin`
- `http://localhost:8000/auth/showRegister`

## Architecture Overview

```
app/
  Controllers/
  Core/
  Factories/
  Models/
  Repositories/
  Services/
  Views/
public/
```

## Routing style (like your Core)

The router is **convention-based**:

```
/{controller}/{method}/{param1}/{param2}
```

Example:

- `/auth/showLogin` → `AuthController::showLogin()`
- `/auth/register` → `AuthController::register()`
- `/auth/showUser/5` → `AuthController::showUser('5')`

This is handled by the `Core` class, which mirrors your style:

1. Read URL segments.
2. First segment → controller.
3. Second segment → method.
4. Remaining segments → params.
5. Call `call_user_func_array()`.

## Request flow

1. `public/index.php` creates a `Container` and then a `Core` instance.
2. `Core` reads the URL and resolves the controller/method/params.
3. `Core` runs middleware for the resolved method.
4. `AuthController` handles HTTP input/output.
5. `AuthService` performs business rules.
6. `UserRepository` reads/writes users.
7. `UserFactory` builds the `User` model.
8. `View` renders HTML.

## Why these layers?

### Controller
The controller does **HTTP-only** work: read input, pass it to services, set sessions, redirect, and render views. Keeping it thin makes it easier to change business logic without touching HTTP code.

### Service
The service contains the **business rules**: hashing passwords, checking credentials, and enforcing invariants. This prevents duplication across controllers and keeps rules in one place.

### Repository
Repositories isolate SQL from the rest of the system. If you change database structure, only the repository needs to change.

### Factory
Factories centralize object creation so you don’t repeat creation rules (like timestamps) across the codebase.

### Middleware
Middleware runs **before** the controller. It’s used to guard methods (auth/guest) without repeating checks in each action.

### Validation
The `Validator` centralizes input rules (required, email, min length) so controllers don’t re-implement the same checks repeatedly.

## Middleware usage

Middleware is resolved per controller method inside `AuthController::middlewareFor()`:

```php
public function middlewareFor(string $method): array
{
    $authMethods = ['dashboard', 'showUser', 'logout'];

    if (in_array($method, $authMethods, true)) {
        return [new AuthMiddleware()];
    }

    return [new GuestMiddleware()];
}
```

## Validation usage

The validator uses a simple rule string syntax:

```php
$errors = $validator->validate($_POST, [
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
```

This keeps validation rules **in one place** and returns error messages that can be flashed or rendered.
