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

    <form action="/accounts/store" method="POST">
        <input
            type="text"
            id="account_name"
            name="account_name"
            placeholder="Enter account name"
            value="<?= htmlspecialchars($oldAccountName ?? ''); ?>"
            required
        >

        <select name="account_type_id" id="account_type_id" required>
            <option
                class="select"
                disabled
                hidden
                value=""
                <?= empty($oldAccountTypeId) ? 'selected' : ''; ?>
            >
                Select account type...
            </option>
            <?php foreach ($accountTypes as $accountType): ?>
                <option
                    value="<?= htmlspecialchars($accountType->id); ?>"
                    <?= isset($oldAccountTypeId) && (int) $oldAccountTypeId === (int) $accountType->id ? 'selected' : ''; ?>
                >
                    <?= htmlspecialchars($accountType->account_type_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="button" value="Cancel" onclick="window.location.href='/accounts'">
        <input type="submit" name="submit" value="Add Account">
    </form>
</div>
</body>
</html>

<?php partial('footer'); ?>
