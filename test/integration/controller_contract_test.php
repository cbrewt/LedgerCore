<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$assert(is_subclass_of(\Core\Controllers\AccountsController::class, \Core\Controllers\Controller::class), 'AccountsController must extend base Controller.');
$assert(is_subclass_of(\Core\Controllers\NamedEntityCrudController::class, \Core\Controllers\Controller::class), 'NamedEntityCrudController must extend base Controller.');
$assert(is_subclass_of(\Core\Controllers\TransactionsController::class, \Core\Controllers\Controller::class), 'TransactionsController must extend base Controller.');

$controller = new ReflectionClass(\Core\Controllers\Controller::class);
foreach (['requireMethod', 'requirePositiveIdFromQuery', 'requirePositiveIdFromPost', 'redirect', 'abort'] as $method) {
    $assert($controller->hasMethod($method), "Base Controller missing {$method}.");
}

$transactionRepo = new ReflectionClass(\Core\Repositories\TransactionRepository::class);
foreach (['findDetailed', 'getLookupOptions', 'getCreditCardOptions', 'getFiltered', 'countFiltered', 'update'] as $method) {
    $assert($transactionRepo->hasMethod($method), "TransactionRepository missing {$method}.");
}

echo "controller_contract_test: OK\n";

