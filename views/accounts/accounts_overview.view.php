<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<?php
$safeTotals = is_array($totals ?? null) ? $totals : [];
$accountsDetails = [
    'Date' => date('Y-m-d'),
    'Total Checking Balance' => $safeTotals['checking_total'] ?? 0,
    'Total Savings Balance' => $safeTotals['savings_total'] ?? 0,
    'Total Credit Card Balance' => $safeTotals['credit_card_balance'] ?? 0,
    'Total Available Credit' => $safeTotals['available_credit_total'] ?? 0,
    'Total Cash' => $safeTotals['cash_total'] ?? 0,
];
?>

<table class="account-table">
    <?php foreach ($accountsDetails as $label => $value): ?>
        <tr>
            <td><?= htmlspecialchars($label); ?></td>
            <td><?= is_numeric($value) ? number_format((float) $value, 2) : htmlspecialchars((string) $value); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php partial('footer'); ?>

