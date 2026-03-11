<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div class="type">
    <a class="form" href="/types/type_create">Add Transaction Type</a>
</div>

<?php if (!empty($types)): ?>

    <?php
    $columns = [
        ['title' => 'Type ID', 'field' => 'id', 'link' => '/types/type_show?id='],
        ['title' => 'Transaction Type', 'field' => 'type_name']
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
            <?php foreach ($types as $type): ?>
                <tr class="text-last-column">
                    <?php foreach ($columns as $column): ?>
                        <td>
                            <?php
                            $fieldValue = $type->{$column['field']} ?? 'Unknown';
                            $safeValue = htmlspecialchars((string)$fieldValue, ENT_QUOTES, 'UTF-8');
                            ?>
                            <?php if (isset($column['link'])): ?>
                                <a href="<?= $column['link'] . urlencode($safeValue) ?>">
                                    <?= $safeValue ?>
                                </a>
                            <?php else: ?>
                                <?= $safeValue ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <p>No transaction types found.</p>
<?php endif; ?>

<?php partial('footer'); ?>
