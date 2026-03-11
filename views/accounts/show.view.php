<?php

$heading = $account->account_name;
partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]);

if (empty($transactions)): ?>
    <p>No transactions found for this account.</p>
<?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Transaction Date</th>
                <th>Account Name</th>
                <th>Transaction Type</th>
                <th>Payee Name</th>
                <th>Category Name</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td>
                        <a href="/transactions/show?id=<?= htmlspecialchars((string) ($transaction->id ?? '')); ?>">
                            <?= htmlspecialchars((string) ($transaction->id ?? '')); ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars((string) ($transaction->transaction_date ?? '')); ?></td>
                    <td><?= htmlspecialchars((string) ($transaction->account_name ?? '')); ?></td>
                    <td><?= htmlspecialchars((string) ($transaction->type_name ?? '')); ?></td>
                    <td><?= htmlspecialchars((string) ($transaction->payee_name ?? '')); ?></td>
                    <td><?= htmlspecialchars((string) ($transaction->category_name ?? '')); ?></td>
                    <td><?= number_format((float) ($transaction->amount ?? 0), 2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        $queryParams = $_GET;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams);
        ?>

        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1; ?>&<?= $queryString; ?>">&laquo; Previous</a>
        <?php endif; ?>

        <span>Page <?= $currentPage; ?> of <?= $totalPages; ?></span>

        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1; ?>&<?= $queryString; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="delete-account-container">
    <form action="/accounts/delete" method="POST"
          onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.')">
        <input type="hidden" name="_method" value="DELETE">
        <input type="button" value="Cancel" class="delete-button" onclick="window.location.href='/accounts';">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($account->id ?? '')); ?>">
        <input type="submit" value="Delete Account" class="delete-button">
        <input type="button" value="Edit Account" class="delete-button"
               onclick="window.location.href='/accounts/edit?id=<?= htmlspecialchars((string) ($account->id ?? '')); ?>';">
    </form>
</div>

<?php partial('footer'); ?>
