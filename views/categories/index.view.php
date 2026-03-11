<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div class="category">
    <a class="form" href="/categories/category_create">Add Category</a>
</div>

<?php if (!empty($categories)): ?>

    <?php
    $columns = [
        ['title' => 'Category ID', 'field' => 'id', 'link' => '/categories/category_show?id='],
        ['title' => 'Category Name', 'field' => 'category_name']
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
            <?php foreach ($categories as $category): ?>
                <tr class="text-last-column">
                    <?php foreach ($columns as $column): ?>
                        <td>
                            <?php if (isset($column['link'])): ?>
                                <a href="<?= $column['link'] . urlencode($category->{$column['field']}) ?>">
                                    <?= htmlspecialchars($category->{$column['field']}) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($category->{$column['field']}) ?>
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
