<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div>
    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="/types/type" method="POST">
        <input type="text" id="type_name" name="type_name" value="<?= htmlspecialchars($oldName ?? ''); ?>" autofocus required>
        <input type="button" value="Cancel" onclick="window.location.href='/types/type'">
        <input type="submit" name="submit" value="Add Transaction Type">
    </form>
</div>

<?php partial('footer'); ?>

