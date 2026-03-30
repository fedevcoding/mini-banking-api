<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Bootstrap Illuminate Database
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => $_ENV['DB_CHARSET'],
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create Slim app
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Register routes
(require __DIR__ . '/../src/routes.php')($app);

$app->run();