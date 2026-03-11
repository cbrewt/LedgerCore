<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div>
    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($formAction); ?>" method="POST">
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $row['id']); ?>">
        <input type="text" id="<?= htmlspecialchars($field); ?>" name="<?= htmlspecialchars($field); ?>"
               value="<?= htmlspecialchars($row[$field]); ?>" required>
        <div class="edit_page_buttons">
            <input type="button" value="Cancel"
                   onclick="window.location.href='<?= $cancelPath; ?>?id=<?= urlencode((string) $row['id']); ?>';">
            <input type="submit" name="submit" value="Update">
        </div>
    </form>
</div>

<?php partial('footer'); ?>

