<?php

declare(strict_types=1);

use App\Core\Container;
use App\Core\Core;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$config = require __DIR__ . '/../config.php';
$container = new Container($config);

new Core($container);
