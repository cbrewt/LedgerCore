<?php

require 'vendor/autoload.php';
require 'Core/functions.php';
require 'bootstrap.php';

$_GET = [
    'rpaccount_id' => 1,
    'start_date' => '2026-02-03',
    'end_date' => '2026-03-03',
    'beginning_balance' => '115.30',
    'ending_balance' => '-247.93',
];

(new \Core\Controllers\ReconcileController())->index();