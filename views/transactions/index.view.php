<?php
partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]);

/**
 * Build a URL query string while preserving current filters.
 * - $overrides: keys to set/replace
 * - $removeKeys: keys to remove
 */
function buildQuery(array $overrides = [], array $removeKeys = []): string
{
    $params = $_GET;

    foreach ($removeKeys as $key) {
        unset($params[$key]);
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }

    $qs = http_build_query($params);
    return $qs ? ('?' . $qs) : '';
}

// Determine current sort state (defaults if controller did not set them)
$currentSortField = $sortField ?? 'posted_at';
$currentSortOrder = strtoupper($sortOrder ?? 'DESC'); // current state shown on screen

// Flip order for the next click
$toggleSortOrder  = ($currentSortOrder === 'ASC') ? 'DESC' : 'ASC';

// Build toggle URL: preserve filters; flip order; keep same field; reset page
$toggleHref = buildQuery([
    'sortField' => $currentSortField,
    'sortOrder' => $toggleSortOrder,
    'page'      => 1,
], ['page']);

// Detect whether any filters are applied (for the sum modal)
$filtersApplied = !empty(array_filter([
    $filters['start_date'] ?? null,
    $filters['end_date'] ?? null,
    $filters['rpaccount_id'] ?? null,
    $filters['type_id'] ?? null,
    $filters['payee_id'] ?? null,
    $filters['category_id'] ?? null,
    !empty($includeUnposted) ? 1 : null,
]));
?>

<div class="toolbar no-print">
    <a href="<?= htmlspecialchars($printUrl) ?>"
       target="_blank"
       class="btn btn-secondary">
        Print
    </a>

   <a href="<?= htmlspecialchars($toggleHref) ?>"
   class="btn btn-secondary">
    Order: <?= ($toggleSortOrder === 'ASC') ? 'Oldest → Newest' : 'Newest → Oldest' ?>
    </a>    
</div>



<!-- Filter Form (Inline) -->
<form method="GET" action="/transactions" class="filter-form">
    <div class="filter-group">
        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? ''); ?>">
        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? ''); ?>">

        <select name="rpaccount_id" id="rpaccount_id">
            <option value="">Account...</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= (int) $account->id; ?>" <?= (($filters['rpaccount_id'] ?? '') == $account->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($account->account_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="type_id" id="type_id">
            <option value="">Type...</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= (int) $type->id; ?>" <?= (($filters['type_id'] ?? '') == $type->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type->type_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="payee_id" id="payee_id">
            <option value="">Payee...</option>
            <?php foreach ($payees as $payee): ?>
                <option value="<?= (int) $payee->id; ?>" <?= (($filters['payee_id'] ?? '') == $payee->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($payee->payee_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="category_id" id="category_id">
            <option value="">Category...</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category->id; ?>" <?= (($filters['category_id'] ?? '') == $category->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category->category_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- ✅ Checkbox: include unposted (assumes controller passes $includeUnposted) -->
            <label class="filter-checkbox">
            <input type="checkbox" name="include_unposted" value="1" <?= !empty($includeUnposted) ? 'checked' : '' ?>>
            Include unposted
            </label>

    </div>

    <div class="filter-submit">
        <button type="submit">Apply Filters</button>
        <a href="/transactions" class="clear-filters">Clear Filters</a>
    </div>
</form>

<!-- Modal -->
<div id="modalOverlay" class="modal-overlay"></div>

<div id="sumModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeModal()">&times;</span>
        <?php if ($filtersApplied): ?>
            <p>Total of Filtered Transactions: <strong>$<?= number_format((float) $totalSum, 2, '.', ','); ?></strong></p>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($table_rows)): ?>

    <?php
    $columns = [
        ['title' => 'Transaction ID',   'field' => 'id',               'link' => '/transactions/show?id='],
        ['title' => 'Transaction Date', 'field' => 'transaction_date', 'sortable' => true],
        ['title' => 'Posted Date',      'field' => 'posted_at',        'sortable' => true],
        ['title' => 'Account Name',     'field' => 'account_name'],
        ['title' => 'Transaction Type', 'field' => 'type_name'],
        ['title' => 'Payee Name',       'field' => 'payee_name'],
        ['title' => 'Category Name',    'field' => 'category_name'],
        ['title' => 'Amount',           'field' => 'amount',           'sortable' => true],
    ];
    ?>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th>
                        <?php if (!empty($column['sortable'])): ?>
                            <?php
                            $newOrder = ($sortField === $column['field'] && strtoupper($sortOrder) === 'ASC')
                                ? 'DESC'
                                : 'ASC';

                            $sortHref = buildQuery([
                                'sortField' => $column['field'],
                                'sortOrder' => $newOrder,
                                'page'      => 1,
                            ], ['page']);
                            ?>
                            <a href="<?= htmlspecialchars($sortHref); ?>">
                                <?= htmlspecialchars($column['title']); ?>
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars($column['title']); ?>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($table_rows as $row): ?>
                    <?php $isUnposted = empty($row->posted_at); ?>
                    <tr class="<?= $isUnposted ? 'is-unposted' : '' ?>">
                    <?php foreach ($columns as $column): ?>
                        <td>
                            <?php
                            $value = $row->{$column['field']};

                            if ($column['field'] === 'amount') {
                                $value = number_format((float) $value, 2, '.', ',');
                            }

                            if ($column['field'] === 'posted_at') {
                                $value = !empty($value) ? substr((string) $value, 0, 10) : '';
                            }

                            if (!empty($column['link'])):
                                $href = $column['link'] . urlencode($row->{$column['field']});
                                ?>
                                <a href="<?= htmlspecialchars($href); ?>">
                                    <?= htmlspecialchars((string) $value); ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars((string) $value); ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php if ($totalPages > 1): ?>
            <?php if ($currentPage > 1): ?>
                <?php $prevHref = buildQuery(['page' => $currentPage - 1]); ?>
                <a href="<?= htmlspecialchars($prevHref); ?>">&laquo; Previous</a>
            <?php endif; ?>

            <span>Page <?= (int) $currentPage; ?> of <?= (int) $totalPages; ?></span>

            <?php if ($currentPage < $totalPages): ?>
                <?php $nextHref = buildQuery(['page' => $currentPage + 1]); ?>
                <a href="<?= htmlspecialchars($nextHref); ?>">Next &raquo;</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php else: ?>
    <p>No results found.</p>
<?php endif; ?>

<script>
    function closeModal() {
        const modal = document.getElementById('sumModal');
        const overlay = document.getElementById('modalOverlay');

        if (modal) modal.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const filtersApplied = <?= $filtersApplied ? 'true' : 'false' ?>;

        if (filtersApplied) {
            const modal = document.getElementById('sumModal');
            const overlay = document.getElementById('modalOverlay');

            if (modal && overlay) {
                setTimeout(() => {
                    modal.classList.add('show');
                    overlay.classList.add('show');
                }, 100);
            }
        }

        const overlay = document.getElementById('modalOverlay');
        if (overlay) {
            overlay.addEventListener('click', closeModal);
        }

        // Select-all logic (safe / optional)
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                const boxes = document.querySelectorAll('.rowSelect');
                boxes.forEach(b => b.checked = selectAll.checked);
            });
        }
    });
</script>
<style>
    .modal {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        top: -150px;

        width: min(520px, calc(100vw - 40px));
        max-width: 520px;
        box-sizing: border-box;

        background: #ffc;
        padding: 20px;
        border: 2px solid #333;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);

        opacity: 0;
        visibility: hidden;
        z-index: 100;

        transition: top 0.6s ease, opacity 0.6s ease, visibility 0s linear 0.6s;
    }

    .modal.show {
        top: 20px;
        opacity: 1;
        visibility: visible;

        transition: top 0.6s ease, opacity 0.6s ease, visibility 0s;
    }

    .modal-content {
        text-align: center;
    }

    .close-button {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 2em;
        cursor: pointer;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);

        opacity: 0;
        visibility: hidden;
        z-index: 50;

        transition: opacity 0.9s ease, visibility 0.9s ease;
    }

    .modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }
</style>


<?php partial('footer'); ?>
