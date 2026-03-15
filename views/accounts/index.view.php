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
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($accounts as $account): ?>
                <?php
                $accountId = htmlspecialchars((string) $account->id);
                $accountName = htmlspecialchars((string) $account->account_name);
                $showUrl = '/accounts/show?id=' . urlencode((string) $account->id);
                $editUrl = '/accounts/edit?id=' . urlencode((string) $account->id);

                $isArchived = false;
                if (is_object($account) && property_exists($account, 'is_archived')) {
                    $isArchived = ((int) $account->is_archived) === 1;
                }
                ?>
                <tr>
                    <td><?= $accountId ?></td>

                    <td>
                        <a href="<?= $showUrl ?>">
                            <?= $accountName ?>
                        </a>
                    </td>

                    <td><?= $isArchived ? 'Yes' : 'No' ?></td>

                    <td>
                        <form method="GET" action="/accounts/show" style="display:inline-block; margin:0 4px 4px 0;">
                            <input type="hidden" name="id" value="<?= $accountId ?>">
                            <button type="submit">Show</button>
                        </form>

                        <form method="GET" action="/accounts/edit" style="display:inline-block; margin:0 4px 4px 0;">
                            <input type="hidden" name="id" value="<?= $accountId ?>">
                            <button type="submit">Edit</button>
                        </form>

                        <?php if ($isArchived): ?>
                            <form method="POST" action="/accounts/restore" style="display:inline-block; margin:0 4px 4px 0;">
                                <input type="hidden" name="id" value="<?= $accountId ?>">
                                <button type="submit">Restore</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="/accounts/delete" style="display:inline-block; margin:0 4px 4px 0;">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="id" value="<?= $accountId ?>">
                                <button
                                    type="submit"
                                    onclick="return confirm('Archive this account? Transactions will remain intact.');"
                                >
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