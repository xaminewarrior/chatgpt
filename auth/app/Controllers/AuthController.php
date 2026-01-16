<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\View;
use App\Services\AuthService;

final class AuthController
{
    public function __construct(
        private AuthService $auth,
        private Validator $validator
    ) {
    }

    public function showLogin(): void
    {
        View::render('login', ['error' => $_SESSION['flash_error'] ?? null]);
        unset($_SESSION['flash_error']);
    }

    public function showRegister(): void
    {
        View::render('register', ['error' => $_SESSION['flash_error'] ?? null]);
        unset($_SESSION['flash_error']);
    }

    public function login(): void
    {
        $errors = $this->validator->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($error = $this->validator->firstError($errors)) {
            $_SESSION['flash_error'] = $error;
            header('Location: /login');
            return;
        }

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $user = $this->auth->attempt($email, $password);

        if (!$user) {
            $_SESSION['flash_error'] = 'Invalid email or password.';
            header('Location: /login');
            return;
        }

        $_SESSION['user_id'] = $user->id;
        header('Location: /dashboard');
    }

    public function register(): void
    {
        $errors = $this->validator->validate($_POST, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($error = $this->validator->firstError($errors)) {
            $_SESSION['flash_error'] = $error;
            header('Location: /register');
            return;
        }

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        try {
            $user = $this->auth->register($name, $email, $password);
            $_SESSION['user_id'] = $user->id;
            header('Location: /dashboard');
        } catch (\RuntimeException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
            header('Location: /register');
        }
    }

    public function dashboard(): void
    {
        $user = $this->auth->currentUser();

        if (!$user) {
            header('Location: /login');
            return;
        }

        View::render('dashboard', ['user' => $user]);
    }

    public function showUser(string $id): void
    {
        $user = $this->auth->findUserById((int) $id);

        if (!$user) {
            http_response_code(404);
            View::render('user-not-found');
            return;
        }

        View::render('user', ['user' => $user]);
    }

    public function logout(): void
    {
        $this->auth->logout();
        header('Location: /login');
    }
}
