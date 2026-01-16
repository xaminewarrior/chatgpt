<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Core\Database;
use App\Core\Router;
use App\Core\Validator;
use App\Factories\UserFactory;
use App\Repositories\UserRepository;
use App\Services\AuthMiddleware;
use App\Services\AuthService;
use App\Services\GuestMiddleware;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$config = require __DIR__ . '/../config.php';

$database = new Database($config['db']);
$userRepository = new UserRepository($database);
$factory = new UserFactory();
$authService = new AuthService($userRepository, $factory);
$validator = new Validator();
$controller = new AuthController($authService, $validator);

$router = new Router();

$guest = new GuestMiddleware();
$auth = new AuthMiddleware();

$router->get('/', fn () => header('Location: /login'));
$router->get('/login', [$controller, 'showLogin'], [$guest]);
$router->post('/login', [$controller, 'login'], [$guest]);
$router->get('/register', [$controller, 'showRegister'], [$guest]);
$router->post('/register', [$controller, 'register'], [$guest]);
$router->get('/dashboard', [$controller, 'dashboard'], [$auth]);
$router->get('/users/{id}', [$controller, 'showUser'], [$auth]);
$router->post('/logout', [$controller, 'logout'], [$auth]);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
