<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();

    $dsn = sprintf(
        "pgsql:host=%s;port=5432;dbname=%s",
        $_ENV['POSTGRES_HOST'],
        $_ENV['POSTGRES_DB']
    );

    $builder = new ContainerBuilder();
    $builder->addDefinitions([
        PDO::class => fn () => new PDO(
            $dsn,
            $_ENV['POSTGRES_USER'],
            $_ENV['POSTGRES_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ),
    ]);
    $container = $builder->build();

    $dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/', [App\PasteController::class, 'create']);
        $r->addRoute('POST', '/', [App\PasteController::class, 'store']);
        $r->addRoute('GET', '/{token}', [App\PasteController::class, 'find']);
    });

    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);

    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            http_response_code(404);
            echo 'Not Found';
            break;

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            http_response_code(405);
            echo 'Method Not Allowed';
            break;

        case FastRoute\Dispatcher::FOUND:
            [$class, $method] = $routeInfo[1];
            $vars = $routeInfo[2];

            $controller = $container->get($class);
            $controller->$method(...array_values($vars));
            break;
    }
} catch (Throwable $e) {
    error_log((string)$e);

    http_response_code(500);
    echo 'Internal Server Error';
}
