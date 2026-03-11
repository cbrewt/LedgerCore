<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title); ?></title>
</head>
<body>

<div>
    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="/accounts/update" method="POST">
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="id" value="<?= htmlspecialchars($row_edit->id ?? ''); ?>">

        <input
            type="text"
            id="account_name"
            name="account_name"
            value="<?= htmlspecialchars($row_edit->account_name ?? ''); ?>"
            required
        >

        <select name="account_type_id" id="account_type_id" required>
            <option disabled hidden value="">Select account type...</option>
            <?php foreach ($accountTypes as $accountType): ?>
                <option
                    value="<?= htmlspecialchars($accountType->id); ?>"
                    <?= isset($row_edit->account_type_id) && (int) $row_edit->account_type_id === (int) $accountType->id ? 'selected' : ''; ?>
                >
                    <?= htmlspecialchars($accountType->account_type_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="button" value="Cancel" onclick="window.location.href='/accounts'">
        <input type="submit" name="submit" value="Update Account">
    </form>
</div>
</body>
</html>

<?php partial('footer'); ?>

