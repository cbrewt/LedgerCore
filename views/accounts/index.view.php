<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div class="payee">
    <a class="form" href="/accounts/create">Add Account</a>

    <?php if (!empty($showArchived) && $showArchived): ?>
        <a class="form" href="/accounts">Hide Archived</a>
    <?php else: ?>
        <a class="form" href="/accounts?archived=1">Show Archived</a>
    <?php endif; ?>
</div>

<?php if (!empty($accounts)): ?>

    <div class="table-wrapper">
        <table class="accounts-table">
            <thead>
            <tr>
                <th>Account ID</th>
                <th>Account Name</th>
                <th>Archived</th>
                <th>Action</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($accounts as $account): ?>
                <?php
                $isArchived = false;
                if (is_object($account) && property_exists($account, 'is_archived')) {
                    $isArchived = ((int) $account->is_archived) === 1;
                }
                ?>
                <tr>
                    <td>
                        <a href="/accounts/show?id=<?= htmlspecialchars($account->id) ?>">
                            <?= htmlspecialchars($account->id) ?>
                        </a>
                    </td>

                    <td><?= htmlspecialchars($account->account_name) ?></td>

                    <td><?= $isArchived ? 'Yes' : 'No' ?></td>

                    <td>
                        <?php if ($isArchived): ?>
                            <form method="POST" action="/accounts/restore">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($account->id) ?>">
                                <button type="submit">Restore</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="/accounts/delete">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($account->id) ?>">
                                <button type="submit"
                                        onclick="return confirm('Archive this account? Transactions will remain intact.');">
                                    Archive
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

<?php else: ?>
    <p>No results found.</p>
<?php endif; ?>

<?php partial('footer'); ?>