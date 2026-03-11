<?php

declare(strict_types=1);

$root = realpath(__DIR__ . '/../../');
if ($root === false) {
    throw new RuntimeException('Unable to resolve project root.');
}

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    echo "http_flow_test: SKIPPED (pdo_sqlite unavailable)\n";
    exit(0);
}

$dbPath = $root . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'tmp_integration.sqlite';
if (is_file($dbPath)) {
    unlink($dbPath);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$schema = [
    'CREATE TABLE account_types (id INTEGER PRIMARY KEY, account_type_name TEXT NOT NULL)',
    'CREATE TABLE rpaccounts (id INTEGER PRIMARY KEY AUTOINCREMENT, account_name TEXT NOT NULL, account_type_id INTEGER NOT NULL, is_archived INTEGER NOT NULL DEFAULT 0, archived_at TEXT NULL)',
    'CREATE TABLE payees (id INTEGER PRIMARY KEY AUTOINCREMENT, payee_name TEXT NOT NULL)',
    'CREATE TABLE categories (id INTEGER PRIMARY KEY AUTOINCREMENT, category_name TEXT NOT NULL)',
    'CREATE TABLE transaction_types (id INTEGER PRIMARY KEY AUTOINCREMENT, type_name TEXT NOT NULL)',
    'CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT, transaction_date TEXT NOT NULL, posted_at TEXT NULL, rpaccount_id INTEGER NOT NULL, type_id INTEGER NOT NULL, payee_id INTEGER NOT NULL, category_id INTEGER NOT NULL, amount REAL NOT NULL)',
    'CREATE TABLE credit_cards (id INTEGER PRIMARY KEY AUTOINCREMENT, rpaccount_id INTEGER NOT NULL, balance REAL NOT NULL DEFAULT 0, credit_limit REAL NOT NULL DEFAULT 0, available_credit REAL NOT NULL DEFAULT 0, due_date TEXT NULL, closing_date TEXT NULL, utilization_percentage REAL NOT NULL DEFAULT 0)',
    'CREATE TABLE account_balances (id INTEGER PRIMARY KEY AUTOINCREMENT, rpaccount_id INTEGER NOT NULL, account_name TEXT NOT NULL, account_type_id INTEGER NOT NULL, balance REAL NOT NULL DEFAULT 0)'
];
foreach ($schema as $sql) {
    $pdo->exec($sql);
}

$pdo->exec("INSERT INTO account_types (id, account_type_name) VALUES (1, 'Checking'), (2, 'Savings')");
$pdo->exec("INSERT INTO rpaccounts (id, account_name, account_type_id, is_archived) VALUES (1, 'Primary Checking', 1, 0)");
$pdo->exec("INSERT INTO payees (id, payee_name) VALUES (1, 'Seed Payee')");
$pdo->exec("INSERT INTO categories (id, category_name) VALUES (1, 'Seed Category')");
$pdo->exec("INSERT INTO transaction_types (id, type_name) VALUES (1, 'Expense'), (18, 'Payment'), (20, 'Transfer')");
$pdo->exec("INSERT INTO account_balances (id, rpaccount_id, account_name, account_type_id, balance) VALUES (1, 1, 'Primary Checking', 1, 0)");

$port = 8093;
$host = "127.0.0.1:{$port}";
$serverCommand = PHP_BINARY . ' -S ' . $host . ' -t public';

$env = $_ENV;
$env['DB_DSN'] = 'sqlite:' . $dbPath;
$env['DB_USER'] = '';
$env['DB_PASS'] = '';
$env['APP_ENV'] = 'test';

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($serverCommand, $descriptorSpec, $pipes, $root, $env);
if (!is_resource($process)) {
    throw new RuntimeException('Failed to start built-in PHP server.');
}

stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);

$request = static function (string $method, string $url, array $data = []): array {
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Connection: close',
    ];

    $contextMethod = $method;
    $body = '';
    if ($method === 'PATCH' || $method === 'DELETE') {
        $contextMethod = 'POST';
        $data['_method'] = $method;
    }
    if ($contextMethod !== 'GET') {
        $body = http_build_query($data);
    }

    $context = stream_context_create([
        'http' => [
            'method' => $contextMethod,
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'ignore_errors' => true,
            'follow_location' => 0,
            'timeout' => 10,
        ]
    ]);

    $responseBody = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];
    $status = 0;
    if (isset($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $m)) {
        $status = (int) $m[1];
    }

    $location = null;
    foreach ($responseHeaders as $header) {
        if (stripos($header, 'Location:') === 0) {
            $location = trim(substr($header, 9));
            break;
        }
    }

    return [
        'status' => $status,
        'body' => $responseBody === false ? '' : $responseBody,
        'location' => $location,
        'headers' => $responseHeaders,
    ];
};

$waitForServer = static function () use ($request, $host): void {
    $url = "http://{$host}/404-not-found-check";
    $deadline = microtime(true) + 10;
    while (microtime(true) < $deadline) {
        $res = $request('GET', $url);
        if ($res['status'] > 0) {
            return;
        }
        usleep(100_000);
    }
    throw new RuntimeException('Server did not become ready in time.');
};

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

try {
    $waitForServer();

    $base = "http://{$host}";

    // Accounts create/edit/archive/restore
    $res = $request('GET', "{$base}/accounts/create");
    $assert($res['status'] === 200, 'GET /accounts/create failed');

    $res = $request('POST', "{$base}/accounts/store", [
        'account_name' => 'Flow Account',
        'account_type_id' => 1,
    ]);
    $assert($res['status'] === 302 && $res['location'] === '/accounts', 'POST /accounts/store failed');
    $accountId = (int) $pdo->query("SELECT id FROM rpaccounts WHERE account_name = 'Flow Account'")->fetchColumn();
    $assert($accountId > 0, 'Stored account not found');

    $res = $request('PATCH', "{$base}/accounts/update", [
        'id' => $accountId,
        'account_name' => 'Flow Account Updated',
        'account_type_id' => 2,
        'submit' => '1',
    ]);
    $assert($res['status'] === 302 && $res['location'] === '/accounts', 'PATCH /accounts/update failed');
    $updatedName = (string) $pdo->query("SELECT account_name FROM rpaccounts WHERE id = {$accountId}")->fetchColumn();
    $assert($updatedName === 'Flow Account Updated', 'Account update not persisted');

    $res = $request('DELETE', "{$base}/accounts/delete", ['id' => $accountId]);
    $assert($res['status'] === 302 && str_starts_with((string) $res['location'], '/accounts?success='), 'DELETE /accounts/delete failed');
    $isArchived = (int) $pdo->query("SELECT is_archived FROM rpaccounts WHERE id = {$accountId}")->fetchColumn();
    $assert($isArchived === 1, 'Account archive flag not set');

    $res = $request('POST', "{$base}/accounts/restore", ['id' => $accountId]);
    $assert($res['status'] === 302 && str_starts_with((string) $res['location'], '/accounts?archived=1'), 'POST /accounts/restore failed');
    $isArchived = (int) $pdo->query("SELECT is_archived FROM rpaccounts WHERE id = {$accountId}")->fetchColumn();
    $assert($isArchived === 0, 'Account restore flag not reset');

    // Payees create/edit/delete
    $res = $request('POST', "{$base}/payees/payee", ['payee_name' => 'Flow Payee']);
    $assert($res['status'] === 302, 'POST /payees/payee failed');
    $payeeId = (int) $pdo->query("SELECT id FROM payees WHERE payee_name = 'Flow Payee'")->fetchColumn();
    $assert($payeeId > 0, 'Stored payee not found');

    $res = $request('PATCH', "{$base}/payees/payee", ['id' => $payeeId, 'payee_name' => 'Flow Payee Updated']);
    $assert($res['status'] === 302, 'PATCH /payees/payee failed');
    $payeeName = (string) $pdo->query("SELECT payee_name FROM payees WHERE id = {$payeeId}")->fetchColumn();
    $assert($payeeName === 'Flow Payee Updated', 'Payee update not persisted');

    $res = $request('DELETE', "{$base}/payees/payee", ['id' => $payeeId]);
    $assert($res['status'] === 302, 'DELETE /payees/payee failed');
    $deletedPayee = $pdo->query("SELECT COUNT(*) FROM payees WHERE id = {$payeeId}")->fetchColumn();
    $assert((int) $deletedPayee === 0, 'Payee was not deleted');

    // Categories create/edit/delete
    $res = $request('POST', "{$base}/categories/category", ['category_name' => 'Flow Category']);
    $assert($res['status'] === 302, 'POST /categories/category failed');
    $categoryId = (int) $pdo->query("SELECT id FROM categories WHERE category_name = 'Flow Category'")->fetchColumn();
    $assert($categoryId > 0, 'Stored category not found');

    $res = $request('PATCH', "{$base}/categories/update", ['id' => $categoryId, 'category_name' => 'Flow Category Updated']);
    $assert($res['status'] === 302, 'PATCH /categories/update failed');
    $categoryName = (string) $pdo->query("SELECT category_name FROM categories WHERE id = {$categoryId}")->fetchColumn();
    $assert($categoryName === 'Flow Category Updated', 'Category update not persisted');

    $res = $request('DELETE', "{$base}/categories/category", ['id' => $categoryId]);
    $assert($res['status'] === 302, 'DELETE /categories/category failed');
    $deletedCategory = $pdo->query("SELECT COUNT(*) FROM categories WHERE id = {$categoryId}")->fetchColumn();
    $assert((int) $deletedCategory === 0, 'Category was not deleted');

    // Types create/edit/delete
    $res = $request('POST', "{$base}/types/type", ['type_name' => 'Flow Type']);
    $assert($res['status'] === 302, 'POST /types/type failed');
    $typeId = (int) $pdo->query("SELECT id FROM transaction_types WHERE type_name = 'Flow Type'")->fetchColumn();
    $assert($typeId > 0, 'Stored type not found');

    $res = $request('PATCH', "{$base}/types/type", ['id' => $typeId, 'type_name' => 'Flow Type Updated']);
    $assert($res['status'] === 302, 'PATCH /types/type failed');
    $typeName = (string) $pdo->query("SELECT type_name FROM transaction_types WHERE id = {$typeId}")->fetchColumn();
    $assert($typeName === 'Flow Type Updated', 'Type update not persisted');

    $res = $request('DELETE', "{$base}/types/type", ['id' => $typeId]);
    $assert($res['status'] === 302, 'DELETE /types/type failed');
    $deletedType = $pdo->query("SELECT COUNT(*) FROM transaction_types WHERE id = {$typeId}")->fetchColumn();
    $assert((int) $deletedType === 0, 'Type was not deleted');

    // Transaction create/edit/filter/delete
    $res = $request('POST', "{$base}/transactions/store", [
        'transaction_date' => '2026-01-15',
        'rpaccount_id' => 1,
        'type_id' => 1,
        'payee_id' => 1,
        'category_id' => 1,
        'amount' => '12.34',
    ]);
    $assert($res['status'] === 302 && $res['location'] === '/transactions', 'POST /transactions/store failed');
    $transactionId = (int) $pdo->query("SELECT MAX(id) FROM transactions")->fetchColumn();
    $assert($transactionId > 0, 'Stored transaction not found');

    $res = $request('PATCH', "{$base}/transactions/update", [
        'id' => $transactionId,
        'transaction_date' => '2026-01-16',
        'posted_at' => '2026-01-16',
        'rpaccount_id' => 1,
        'type_id' => 1,
        'payee_id' => 1,
        'category_id' => 1,
        'amount' => '99.99',
        'submit' => '1',
    ]);
    $assert($res['status'] === 302 && $res['location'] === '/accounts/show?id=1', 'PATCH /transactions/update failed');
    $amount = (float) $pdo->query("SELECT amount FROM transactions WHERE id = {$transactionId}")->fetchColumn();
    $assert(abs($amount - 99.99) < 0.001, 'Transaction update not persisted');

    $res = $request('GET', "{$base}/transactions?rpaccount_id=1&start_date=2026-01-01&end_date=2026-12-31");
    $assert($res['status'] === 200, 'GET /transactions filtered failed');
    $assert(strpos($res['body'], 'Transactions') !== false, 'Filtered transactions page did not render expected content');

    $res = $request('DELETE', "{$base}/transactions/delete", ['id' => $transactionId]);
    $assert($res['status'] === 302 && $res['location'] === '/transactions', 'DELETE /transactions/delete failed');
    $deletedTx = $pdo->query("SELECT COUNT(*) FROM transactions WHERE id = {$transactionId}")->fetchColumn();
    $assert((int) $deletedTx === 0, 'Transaction was not deleted');

    echo "http_flow_test: OK\n";
} finally {
    proc_terminate($process);
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    @unlink($dbPath);
}
