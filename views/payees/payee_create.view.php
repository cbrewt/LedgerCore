<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div>
    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="/payees/payee" method="POST">
        <input type="text" id="payee_name" name="payee_name" value="<?= htmlspecialchars($oldName ?? ''); ?>" autofocus required>
        <input type="button" value="Cancel" onclick="window.location.href='/payees/payee'">
        <input type="submit" name="submit" value="Add Payee">
    </form>
</div>

<?php partial('footer'); ?>

