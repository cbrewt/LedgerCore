<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$router = null;
require __DIR__ . '/../../routes.php';

if (!$router instanceof \Core\Router) {
    throw new RuntimeException('Router did not initialize correctly.');
}

$routes = $router->routes;

$hasRoute = static function (string $method, string $uri, string $class, string $action) use ($routes): bool {
    foreach ($routes as $route) {
        if (($route['method'] ?? '') !== strtoupper($method)) {
            continue;
        }
        if (($route['uri'] ?? '') !== $uri) {
            continue;
        }
        $controller = $route['controller'] ?? null;
        if (!is_array($controller) || count($controller) !== 2) {
            continue;
        }
        if ($controller[0] === $class && $controller[1] === $action) {
            return true;
        }
    }

    return false;
};

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

// Accounts critical flows
$assert($hasRoute('GET', '/accounts/create', \Core\Controllers\AccountsController::class, 'create'), 'Missing accounts create route.');
$assert($hasRoute('POST', '/accounts/store', \Core\Controllers\AccountsController::class, 'store'), 'Missing accounts store route.');
$assert($hasRoute('GET', '/accounts/edit', \Core\Controllers\AccountsController::class, 'edit'), 'Missing accounts edit route.');
$assert($hasRoute('PATCH', '/accounts/update', \Core\Controllers\AccountsController::class, 'update'), 'Missing accounts update route.');
$assert($hasRoute('DELETE', '/accounts/delete', \Core\Controllers\AccountsController::class, 'delete'), 'Missing accounts delete route.');
$assert($hasRoute('POST', '/accounts/restore', \Core\Controllers\AccountsController::class, 'restore'), 'Missing accounts restore route.');

// Payees critical flows
$assert($hasRoute('GET', '/payees/payee_create', \Core\Controllers\PayeesController::class, 'create'), 'Missing payees create route.');
$assert($hasRoute('POST', '/payees/payee', \Core\Controllers\PayeesController::class, 'store'), 'Missing payees store route.');
$assert($hasRoute('GET', '/payees/payee_edit', \Core\Controllers\PayeesController::class, 'edit'), 'Missing payees edit route.');
$assert($hasRoute('PATCH', '/payees/payee', \Core\Controllers\PayeesController::class, 'update'), 'Missing payees update route.');
$assert($hasRoute('DELETE', '/payees/payee', \Core\Controllers\PayeesController::class, 'delete'), 'Missing payees delete route.');

// Categories critical flows
$assert($hasRoute('GET', '/categories/category_create', \Core\Controllers\CategoriesController::class, 'create'), 'Missing categories create route.');
$assert($hasRoute('POST', '/categories/category', \Core\Controllers\CategoriesController::class, 'store'), 'Missing categories store route.');
$assert($hasRoute('GET', '/categories/edit', \Core\Controllers\CategoriesController::class, 'edit'), 'Missing categories edit route.');
$assert($hasRoute('PATCH', '/categories/update', \Core\Controllers\CategoriesController::class, 'update'), 'Missing categories update route.');
$assert($hasRoute('DELETE', '/categories/category', \Core\Controllers\CategoriesController::class, 'delete'), 'Missing categories delete route.');

// Types critical flows
$assert($hasRoute('GET', '/types/type_create', \Core\Controllers\TypesController::class, 'create'), 'Missing types create route.');
$assert($hasRoute('POST', '/types/type', \Core\Controllers\TypesController::class, 'store'), 'Missing types store route.');
$assert($hasRoute('GET', '/types/type_edit', \Core\Controllers\TypesController::class, 'edit'), 'Missing types edit route.');
$assert($hasRoute('PATCH', '/types/type', \Core\Controllers\TypesController::class, 'update'), 'Missing types update route.');
$assert($hasRoute('DELETE', '/types/type', \Core\Controllers\TypesController::class, 'delete'), 'Missing types delete route.');

// Transactions canonical + filters list route
$assert($hasRoute('GET', '/transactions/create', \Core\Controllers\TransactionsController::class, 'create'), 'Missing transactions create route.');
$assert($hasRoute('GET', '/transactions', \Core\Controllers\TransactionsController::class, 'index'), 'Missing transactions index/filter route.');
$assert($hasRoute('GET', '/transactions/edit', \Core\Controllers\TransactionsController::class, 'edit'), 'Missing transactions edit route.');
$assert($hasRoute('PATCH', '/transactions/update', \Core\Controllers\TransactionsController::class, 'update'), 'Missing transactions update route.');
$assert($hasRoute('GET', '/transactions/delete', \Core\Controllers\TransactionsController::class, 'delete'), 'Missing transactions delete route.');
$assert($hasRoute('DELETE', '/transactions/delete', \Core\Controllers\TransactionsController::class, 'delete'), 'Missing transactions DELETE route.');

// Ensure singular transaction routes are removed from canonical map
foreach ($routes as $route) {
    if (($route['uri'] ?? '') === '/transaction' || ($route['uri'] ?? '') === '/transaction/edit') {
        throw new RuntimeException('Found legacy singular transaction route; expected canonical /transactions/* routes only.');
    }
}

echo "route_contract_test: OK\n";

