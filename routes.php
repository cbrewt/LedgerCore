<?php

use Core\Controllers\AccountsController;
use Core\Controllers\CategoriesController;
use Core\Controllers\PayeesController;
use Core\Controllers\ReconcileController;
use Core\Controllers\TransactionsController;
use Core\Controllers\TypesController;
use Core\Router;

$router = new Router();

// Account Routes
$router->get('/accounts', [AccountsController::class, 'index']);
$router->get('/accounts/show', [AccountsController::class, 'show']);
$router->get('/accounts/balances', [AccountsController::class, 'balances']);
$router->get('/accounts/manage', [AccountsController::class, 'index']);
$router->get('/accounts/create', [AccountsController::class, 'create']);
$router->get('/accounts/edit', [AccountsController::class, 'edit']);
$router->get('/accounts/overview', [AccountsController::class, 'overview']);
$router->post('/accounts/store', [AccountsController::class, 'store']);
$router->patch('/accounts/update', [AccountsController::class, 'update']);
$router->delete('/accounts/delete', [AccountsController::class, 'delete']);
$router->post('/accounts/restore', [AccountsController::class, 'restore']);

// Transaction Routes
$router->get('/', [TransactionsController::class, 'create']); // Legacy entry point
$router->get('/transactions/create', [TransactionsController::class, 'create']);
$router->get('/transactions', [TransactionsController::class, 'index']);
$router->get('/transactions/show', [TransactionsController::class, 'show']);
$router->get('/transactions/edit', [TransactionsController::class, 'edit']);
$router->get('/transactions/delete', [TransactionsController::class, 'delete']);
$router->post('/transactions/store', [TransactionsController::class, 'store']);
$router->patch('/transactions/update', [TransactionsController::class, 'update']);
$router->delete('/transactions/delete', [TransactionsController::class, 'delete']);

// Reconcile Routes
$router->get('/reconcile', [ReconcileController::class, 'index']);
$router->post('/reconcile/toggle', [ReconcileController::class, 'toggle']);
$router->post('/reconcile/finalize', [ReconcileController::class, 'finalize']);
$router->post('/reconcile/undo-finalize', [ReconcileController::class, 'undoFinalize']);

// Payee Routes
$router->get('/payees/payee', [PayeesController::class, 'index']);
$router->get('/payees/payee_create', [PayeesController::class, 'create']);
$router->get('/payees/payee_show', [PayeesController::class, 'show']);
$router->get('/payees/payee_edit', [PayeesController::class, 'edit']);
$router->get('/payees/payee_destroy', [PayeesController::class, 'destroyForm']);
$router->post('/payees/payee', [PayeesController::class, 'store']);
$router->patch('/payees/payee', [PayeesController::class, 'update']);
$router->delete('/payees/payee', [PayeesController::class, 'delete']);

// Category Routes
$router->get('/categories/category', [CategoriesController::class, 'index']);
$router->get('/categories/category_create', [CategoriesController::class, 'create']);
$router->get('/categories/category_show', [CategoriesController::class, 'show']);
$router->get('/categories/edit', [CategoriesController::class, 'edit']);
$router->get('/categories/destroy', [CategoriesController::class, 'destroyForm']);
$router->post('/categories/category', [CategoriesController::class, 'store']);
$router->patch('/categories/update', [CategoriesController::class, 'update']);
$router->delete('/categories/category', [CategoriesController::class, 'delete']);

// Transaction_type Routes
$router->get('/types/type', [TypesController::class, 'index']);
$router->get('/types/type_create', [TypesController::class, 'create']);
$router->get('/types/type_show', [TypesController::class, 'show']);
$router->get('/types/type_edit', [TypesController::class, 'edit']);
$router->get('/types/type_destroy', [TypesController::class, 'destroyForm']);
$router->post('/types/type', [TypesController::class, 'store']);
$router->patch('/types/type', [TypesController::class, 'update']);
$router->delete('/types/type', [TypesController::class, 'delete']);

// Credit Card Routes
$router->get('/creditcards/creditcard', 'controllers/creditcards/index.php');

return $router;