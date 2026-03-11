<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<?php
$transactionDetails = [
    'Date' => $table_row['transaction_date'],
    'Account' => $table_row['account_name'],
    'Transaction Type' => $table_row['type_name'],
    'Payee' => $table_row['payee_name'],
    'Category' => $table_row['category_name'],
    'Amount' => $table_row['amount'],
];
?>

<div class="grid-container">
    <?php
    $itemCounter = 1;
    foreach ($transactionDetails as $label => $value) {
        echo '<div class="item' . $itemCounter++ . '">' . $label . ':</div>';
        echo '<div class="item' . $itemCounter++ . '">' . htmlspecialchars((string) $value) . '</div>';
    }
    ?>
</div>

<div class="edit_page_buttons">
    <input type="button" value="Cancel" class="button" onclick="window.location.href='/transactions';">
    <?php if (!empty($transactionId)): ?>
        <input type="button" value="Delete Transaction" class="button"
               onclick="window.location.href='/transactions/delete?id=<?= (int) $transactionId; ?>';">
        <input type="button" value="Edit Transaction" class="button"
               onclick="window.location.href='/transactions/edit?id=<?= (int) $transactionId; ?>';">
    <?php endif; ?>
</div>

<?php partial('footer'); ?>
