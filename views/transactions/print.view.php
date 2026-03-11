<?php
/** @var string $title */
/** @var string $heading */
/** @var array  $table_rows */
/** @var float  $totalSum */
/** @var int    $totalTransactions */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Print') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body { font-family: Arial, sans-serif; margin: 16px; }
        h1 { margin: 0 0 12px 0; font-size: 18px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; font-size: 12px; }
        thead th { background: #eee; }
        tfoot th { background: #f6f6f6; }

        /* Alternating row background */
        tbody tr:nth-child(even) {
            background: #6e95f0;
        }

        .table-wrapper { overflow: visible; }

        @media print {
            .no-print { display: none !important; }

            /* Ensure backgrounds print in many browsers */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<h1><?= htmlspecialchars($heading ?? 'Transactions') ?></h1>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Posted Date</th>
                <th>Account</th>
                <th>Type</th>
                <th>Payee</th>
                <th>Category</th>
                <th style="text-align:right;">Amount</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($table_rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars(!empty($row->transaction_date) ? substr((string) $row->transaction_date, 0, 10) : '') ?></td>
                <td><?= htmlspecialchars(!empty($row->posted_at) ? substr((string) $row->posted_at, 0, 10) : '') ?></td>
                <td><?= htmlspecialchars($row->account_name ?? '') ?></td>
                <td><?= htmlspecialchars($row->type_name ?? '') ?></td>
                <td><?= htmlspecialchars($row->payee_name ?? '') ?></td>
                <td><?= htmlspecialchars($row->category_name ?? '') ?></td>
                <td style="text-align:right;"><?= number_format((float)($row->amount ?? 0), 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>

        <tfoot>
            <tr>
               <th colspan="6" style="text-align:right;">Total (<?= (int)$totalTransactions ?>)</th>
               <th style="text-align:right;"><?= number_format((float)$totalSum, 2) ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<script>
    window.addEventListener('load', () => window.print());
</script>

</body>
</html>
