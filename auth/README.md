# PHP Auth System (MVC + Design Patterns)

This is a lightweight MVC authentication system built **without a framework**, using SOLID-friendly layering and a few classic patterns.

## Goals

- **Frameworkless** PHP to show how the pieces fit together.
- **Repository + Service + Factory** to keep responsibilities narrow.
- **Middleware + Validation** to keep controllers slim and DRY.
- **Dynamic routing** to show parameterized routes without a framework.

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

- `http://localhost:8000/login`
- `http://localhost:8000/register`

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

### Request flow

1. `public/index.php` receives the request.
2. `Router` matches the request (static or dynamic).
3. `Middleware` runs before the controller action.
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
Middleware runs **before** the controller. It’s used to guard routes (auth/guest) without repeating checks in each action.

### Validation
The `Validator` centralizes input rules (required, email, min length) so controllers don’t re-implement the same checks repeatedly.

## Dynamic routing (how it works)

The router supports parameterized paths using `{name}` placeholders. Internally, it converts them to regex patterns and extracts the values in the same order you declared them.

Example route registration:

```php
$router->get('/users/{id}', [$controller, 'showUser'], [$auth]);
```

When the user visits `/users/42`, the router matches the pattern and calls the controller method with the extracted value:

```php
public function showUser(string $id): void
{
    $user = $this->auth->findUserById((int) $id);
    // ...
}
```

## Middleware usage

Two simple middleware classes are included:

- `GuestMiddleware` redirects logged-in users away from login/register.
- `AuthMiddleware` blocks unauthenticated users from protected routes.

They are registered per route in `public/index.php`:

```php
$router->get('/login', [$controller, 'showLogin'], [$guest]);
$router->get('/dashboard', [$controller, 'dashboard'], [$auth]);
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
