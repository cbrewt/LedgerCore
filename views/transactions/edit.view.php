<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div>
    <form action="/transactions/update" method="POST">
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="id" value="<?= (int) ($transaction['id'] ?? 0); ?>">

        <input type="date" id="transaction_date" name="transaction_date"
               value="<?= htmlspecialchars((string) ($transaction['transaction_date'] ?? '')); ?>"
               required>

        <input type="date" id="posted_at" name="posted_at"
               value="<?= htmlspecialchars(!empty($transaction['posted_at']) ? substr((string) $transaction['posted_at'], 0, 10) : ''); ?>">

        <select name="rpaccount_id" id="rpaccount_id" required>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= (int) $account['id']; ?>" <?= ((int) $transaction['rpaccount_id'] === (int) $account['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($account['account_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="type_id" id="type_id" required>
            <?php foreach ($types as $type): ?>
                <option value="<?= (int) $type['id']; ?>" <?= ((int) $transaction['type_id'] === (int) $type['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($type['type_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="payee_id" id="payee_id" required>
            <?php foreach ($payees as $payee): ?>
                <option value="<?= (int) $payee['id']; ?>" <?= ((int) $transaction['payee_id'] === (int) $payee['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($payee['payee_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="category_id" id="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id']; ?>" <?= ((int) $transaction['category_id'] === (int) $category['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($category['category_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" id="amount" name="amount" step="0.01"
               value="<?= htmlspecialchars((string) ($transaction['amount'] ?? '')); ?>"
               required>

        <br>

        <div class="edit_page_buttons">
            <input type="button" value="Cancel"
                   onclick="window.location.href='/transactions/show?id=<?= urlencode((string) ($transaction['id'] ?? '')); ?>';">
            <input type="submit" name="submit" value="Update Transaction">
        </div>
    </form>
</div>

<?php partial('footer'); ?>
