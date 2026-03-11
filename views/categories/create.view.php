<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div>
    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="/categories/category" method="POST">
        <input type="text" id="category_name" name="category_name" value="<?= htmlspecialchars($oldName ?? ''); ?>" autofocus required>
        <input type="button" value="Cancel" onclick="window.location.href='/categories/category'">
        <input type="submit" name="submit" value="Add Category">
    </form>
</div>

<?php partial('footer'); ?>

