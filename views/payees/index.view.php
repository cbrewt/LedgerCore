<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div class="payee">
    <a class="form" href="/payees/payee_create">Add Payee</a>
</div>

<?php if (!empty($payees)): ?>

    <?php
    $columns = [
        ['title' => 'Payee ID', 'field' => 'id', 'link' => '/payees/payee_show?id='],
        ['title' => 'Payee Name', 'field' => 'payee_name']
    ];
    ?>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th><?= htmlspecialchars($column['title']) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payees as $payee): ?>
                <tr class="text-last-column">
                    <?php foreach ($columns as $column): ?>
                        <td>
                            <?php if (isset($column['link'])): ?>
                                <a href="<?= $column['link'] . urlencode($payee->{$column['field']}) ?>">
                                    <?= htmlspecialchars($payee->{$column['field']}) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($payee->{$column['field']}) ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <p>No results found.</p>
<?php endif; ?>

<?php partial('footer'); ?>
