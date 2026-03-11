<?php

require_once __DIR__ . '/../vendor/autoload.php';


use Core\Repositories\AccountRepository;

$accountRepo = new AccountRepository();

// 🔍 Test Case 1: Fetch an existing account
$account = $accountRepo->find(1);
if ($account) {
    echo "✅ Account Found: ID = {$account->id}, Name = {$account->account_name}\n";
} else {
    echo "❌ Account Not Found for ID 1\n";
}

// 🔍 Test Case 2: Try finding a non-existent account
$account = $accountRepo->find(99999); // Assuming this ID does not exist
if ($account) {
    echo "⚠️ Unexpected: Found an account with ID 99999\n";
} else {
    echo "✅ Correct: No account found for ID 99999\n";
}
