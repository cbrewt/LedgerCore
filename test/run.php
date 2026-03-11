<?php

declare(strict_types=1);

$tests = [
    __DIR__ . '/integration/route_contract_test.php',
    __DIR__ . '/integration/controller_contract_test.php',
    __DIR__ . '/integration/view_smoke_test.php',
    __DIR__ . '/integration/http_flow_test.php',
];

$failures = 0;

foreach ($tests as $test) {
    $output = [];
    $exitCode = 0;
    exec('php ' . escapeshellarg($test), $output, $exitCode);

    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }

    if ($exitCode !== 0) {
        $failures++;
        echo basename($test) . ": FAILED\n";
    }
}

if ($failures > 0) {
    exit(1);
}

echo "All integration tests passed.\n";
