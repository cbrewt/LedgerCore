<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div class="grid-container-2">
    <div class="item1">Payee Name:</div>
    <div class="item2"><?= htmlspecialchars($row['payee_name']); ?></div>
</div>

<div class="edit_page_buttons">
    <input type="button" value="Cancel" class="button" onclick="window.location.href='/payees/payee';">
    <input type="button" value="Delete" class="button" onclick="window.location.href='<?= $destroyPath; ?>?id=<?= urlencode((string) $row['id']); ?>';">
    <input type="button" value="Edit" class="button" onclick="window.location.href='<?= $editPath; ?>?id=<?= urlencode((string) $row['id']); ?>';">
</div>

<?php partial('footer'); ?>

