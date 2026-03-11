<?php

use Core\Repositories\CreditCardRepository;

$creditCardRepo = new CreditCardRepository();
$creditCards = $creditCardRepo->all();

error_log("Credit Cards Passed to View: " . print_r($creditCards, true)); // ✅ Debugging log

// Render the view with credit cards
view('creditcards/index.view.php', [
    'title' => 'Credit Cards',
    'heading' => 'Credit Cards',
    'creditCards' => $creditCards
]);
