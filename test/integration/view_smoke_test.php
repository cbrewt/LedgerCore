<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$router = null;
require __DIR__ . '/../../routes.php';

if (!$router instanceof \Core\Router) {
    throw new RuntimeException('Router did not initialize correctly.');
}

$expectedViews = [
    'GET /' => 'transactions/create.view.php',
    'GET /transactions/create' => 'transactions/create.view.php',
    'GET /transactions' => 'transactions/index.view.php',
    'GET /transactions/show' => 'transactions/show.view.php',
    'GET /transactions/edit' => 'transactions/edit.view.php',
    'GET /accounts' => 'accounts/index.view.php',
    'GET /accounts/manage' => 'accounts/index.view.php',
    'GET /accounts/show' => 'accounts/show.view.php',
    'GET /accounts/balances' => 'accounts/balances.view.php',
    'GET /accounts/create' => 'accounts/create.view.php',
    'GET /accounts/edit' => 'accounts/edit.view.php',
    'GET /accounts/overview' => 'accounts/accounts_overview.view.php',
    'GET /payees/payee' => 'payees/index.view.php',
    'GET /payees/payee_create' => 'payees/payee_create.view.php',
    'GET /payees/payee_show' => 'payees/payee_show.view.php',
    'GET /payees/payee_edit' => 'payees/payee_edit.view.php',
    'GET /payees/payee_destroy' => 'payees/payee_destroy.view.php',
    'GET /categories/category' => 'categories/index.view.php',
    'GET /categories/category_create' => 'categories/create.view.php',
    'GET /categories/category_show' => 'categories/show.view.php',
    'GET /categories/edit' => 'categories/edit.view.php',
    'GET /categories/destroy' => 'categories/destroy.view.php',
    'GET /types/type' => 'types/index.view.php',
    'GET /types/type_create' => 'types/create.view.php',
    'GET /types/type_show' => 'types/type_show.view.php',
    'GET /types/type_edit' => 'types/type_edit.view.php',
    'GET /types/type_destroy' => 'types/type_destroy.view.php',
    'GET /reconcile' => 'reconcile/index.view.php',
    'GET /creditcards/creditcard' => 'creditcards/index.view.php',
];

$index = [];
foreach ($router->routes as $route) {
    $key = strtoupper((string) $route['method']) . ' ' . $route['uri'];
    $index[$key] = $route['controller'];
}

foreach ($expectedViews as $routeKey => $viewPath) {
    if (!isset($index[$routeKey])) {
        throw new RuntimeException("Missing route {$routeKey}");
    }

    $controller = $index[$routeKey];
    $needle = "view('{$viewPath}'";

    if (is_array($controller) && count($controller) === 2) {
        [$className, $methodName] = $controller;
        if (is_subclass_of($className, \Core\Controllers\NamedEntityCrudController::class)) {
            $refClass = new ReflectionClass($className);
            $instance = $refClass->newInstanceWithoutConstructor();
            $configMethod = $refClass->getMethod('config');
            $configMethod->setAccessible(true);
            $config = $configMethod->invoke($instance);

            $map = [
                'index' => 'indexView',
                'create' => 'createView',
                'show' => 'showView',
                'edit' => 'editView',
                'destroyForm' => 'destroyView',
            ];

            $configKey = $map[$methodName] ?? null;
            if ($configKey === null || !isset($config[$configKey]) || $config[$configKey] !== $viewPath) {
                throw new RuntimeException("Route {$routeKey} is not wired to expected view {$viewPath}");
            }
        } else {
            $ref = new ReflectionMethod($className, $methodName);
            $source = file_get_contents($ref->getFileName());
            if ($source === false || strpos($source, $needle) === false) {
                throw new RuntimeException("Route {$routeKey} is not wired to expected view {$viewPath}");
            }
        }
    } elseif (is_string($controller)) {
        $file = __DIR__ . '/../../' . $controller;
        $source = is_file($file) ? file_get_contents($file) : false;
        if ($source === false || strpos($source, $needle) === false) {
            throw new RuntimeException("File-controller route {$routeKey} is not wired to expected view {$viewPath}");
        }
    } else {
        throw new RuntimeException("Unsupported controller type for {$routeKey}");
    }

    $absoluteViewPath = __DIR__ . '/../../views/' . $viewPath;
    if (!is_file($absoluteViewPath)) {
        throw new RuntimeException("Expected view file not found: {$viewPath}");
    }
}

echo "view_smoke_test: OK\n";
