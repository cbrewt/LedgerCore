<?php
$title = 'Balances';
$heading = 'Account Balances';
partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]);
?>


<div class="table-container">
    <?php if (!empty($table_rows)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Account ID</th>
                    <th>Account Name</th>
                    <th>Balance</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($table_rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['account_name']) ?></td>
                        <td><?= htmlspecialchars($row['balance']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center;">0 results</p>
    <?php endif; ?>
</div>

<?php partial('footer'); ?>
