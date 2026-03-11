<?php
require __DIR__ . '/../partials/header.view.php';

/**
 * Build a URL query string while preserving current reconcile inputs.
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

/**
 * Backward-compatible balance variables.
 */
$beginningBalanceValue = isset($beginningBalance) && $beginningBalance !== null
    ? number_format((float) $beginningBalance, 2, '.', '')
    : '';

$endingBalanceRaw = $endingBalance ?? $statementBalance ?? null;
$endingBalanceValue = $endingBalanceRaw !== null
    ? number_format((float) $endingBalanceRaw, 2, '.', '')
    : '';

$beginningBalanceFloat = ($beginningBalanceValue !== '') ? (float) $beginningBalanceValue : null;
$endingBalanceFloat    = ($endingBalanceValue !== '') ? (float) $endingBalanceValue : null;

$postedTotalFloat = isset($postedTotal) ? (float) $postedTotal : 0.00;
$expectedEndingBalance = ($beginningBalanceFloat !== null)
    ? round($beginningBalanceFloat + $postedTotalFloat, 2)
    : null;

$reconcileDifference = ($endingBalanceFloat !== null && $expectedEndingBalance !== null)
    ? round($endingBalanceFloat - $expectedEndingBalance, 2)
    : null;

$isReconciled = ($reconcileDifference !== null && round($reconcileDifference, 2) === 0.00);
$windowIsFinalized = !empty($isFinalized);
?>

<form method="GET" action="/reconcile" class="filter-form">
    <div class="filter-group">
        <select name="rpaccount_id" id="rpaccount_id" required>
            <option value="">Account...</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= (int) $account->id; ?>" <?= ((string)($rpaccountId ?? '') === (string)$account->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($account->account_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>" required>
        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>" required>

        <input
            type="number"
            name="beginning_balance"
            id="beginning_balance"
            step="0.01"
            inputmode="decimal"
            placeholder="Beginning balance"
            value="<?= htmlspecialchars($beginningBalanceValue) ?>"
        >

        <input
            type="number"
            name="ending_balance"
            id="ending_balance"
            step="0.01"
            inputmode="decimal"
            placeholder="Ending balance"
            value="<?= htmlspecialchars($endingBalanceValue) ?>"
        >
    </div>

    <div class="filter-submit">
        <button type="submit">Load Statement Window</button>
        <a href="/reconcile" class="clear-filters">Clear</a>
    </div>
</form>

<?php if (!empty($table_rows)): ?>

    <p style="margin:8px 0 6px 0; font-size: 0.9rem; color: #666;">
        Showing <?= count($table_rows) ?> item<?= count($table_rows) === 1 ? '' : 's' ?>
        <?php if ($windowIsFinalized): ?>
            <span style="margin-left:10px; display:inline-block; padding:4px 10px; border-radius:999px; border:1px solid #2b7; font-size:12px; color:#185; background:#f4fff9;">
                Finalized
            </span>
        <?php endif; ?>
    </p>

    <div style="max-width: 1000px; margin: 10px auto 14px auto; padding: 12px 14px; border: 1px solid #ddd; border-radius: 8px; background: #fff;">
        <div style="display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:space-between;">
            <div style="display:flex; flex-wrap:wrap; gap:18px; align-items:baseline;">
                <div>
                    <div style="font-size:12px; color:#666;">Beginning Balance</div>
                    <div style="font-size:16px; font-weight:600;">
                        <?= $beginningBalanceFloat !== null ? number_format($beginningBalanceFloat, 2, '.', ',') : '—' ?>
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#666;">Posted Total</div>
                    <div style="font-size:16px; font-weight:600;">
                        <?= number_format($postedTotalFloat, 2, '.', ',') ?>
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#666;">Expected Ending Balance</div>
                    <div style="font-size:16px; font-weight:600;">
                        <?= $expectedEndingBalance !== null ? number_format($expectedEndingBalance, 2, '.', ',') : '—' ?>
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#666;">Ending Balance</div>
                    <div style="font-size:16px; font-weight:600;">
                        <?= $endingBalanceFloat !== null ? number_format($endingBalanceFloat, 2, '.', ',') : '—' ?>
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#666;">Difference</div>
                    <div style="font-size:16px; font-weight:700;">
                        <?= $reconcileDifference !== null ? number_format($reconcileDifference, 2, '.', ',') : '—' ?>
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#666;">Posted Items</div>
                    <div style="font-size:16px; font-weight:600;">
                        <?= (int)($postedCount ?? 0) ?> / <?= (int)($totalCount ?? 0) ?>
                    </div>
                </div>

                <?php if ($windowIsFinalized): ?>
                    <div>
                        <div style="font-size:12px; color:#666;">Reconciled Items</div>
                        <div style="font-size:16px; font-weight:600;">
                            <?= (int)($reconciledCount ?? 0) ?> / <?= (int)($totalCount ?? 0) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
                <div class="status-action-row">
                    <?php if ($windowIsFinalized): ?>
                        <span class="status-pill status-pill-finalized">
                            Statement finalized
                        </span>

                        <?php if (!empty($rpaccountId) && !empty($startDate) && !empty($endDate)): ?>
                            <form method="POST" action="/reconcile/undo-finalize" style="margin:0;">
                                <input type="hidden" name="rpaccount_id" value="<?= (int) $rpaccountId ?>">
                                <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                                <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                                <input type="hidden" name="beginning_balance" value="<?= htmlspecialchars($beginningBalanceValue) ?>">
                                <input type="hidden" name="ending_balance" value="<?= htmlspecialchars($endingBalanceValue) ?>">
                                <input type="hidden" name="statement_balance" value="<?= htmlspecialchars($endingBalanceValue) ?>">

                                <button type="submit" class="undo-finalize-btn">
                                    Undo Finalization
                                </button>
                            </form>
                        <?php endif; ?>

                    <?php elseif ($beginningBalanceFloat === null || $endingBalanceFloat === null): ?>
                        <span class="status-pill status-pill-neutral">
                            Enter beginning and ending balances
                        </span>
                    <?php elseif ($isReconciled): ?>
                        <span class="status-pill status-pill-reconciled">
                            Reconciled (difference is 0.00)
                        </span>
                    <?php else: ?>
                        <span class="status-pill status-pill-not-reconciled">
                            Not reconciled
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!$windowIsFinalized && $isReconciled && !empty($rpaccountId) && !empty($startDate) && !empty($endDate)): ?>
                    <form method="POST" action="/reconcile/finalize" style="margin:0;">
                        <input type="hidden" name="rpaccount_id" value="<?= (int) $rpaccountId ?>">
                        <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                        <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                        <input type="hidden" name="beginning_balance" value="<?= htmlspecialchars($beginningBalanceValue) ?>">
                        <input type="hidden" name="ending_balance" value="<?= htmlspecialchars($endingBalanceValue) ?>">
                        <input type="hidden" name="difference" value="<?= htmlspecialchars(number_format($reconcileDifference, 2, '.', '')) ?>">
                        <input type="hidden" name="statement_balance" value="<?= htmlspecialchars($endingBalanceValue) ?>">

                        <button type="submit" class="finalize-reconcile-btn">
                            Finalize Reconciliation
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="reconcile-table-tools">
        <div class="reconcile-table-hint">
            Latest statement activity is at the bottom of the list.
        </div>
        <div class="reconcile-table-buttons">
            <button type="button" class="reconcile-nav-btn" id="jump-earliest">Jump to earliest</button>
            <button type="button" class="reconcile-nav-btn" id="jump-latest">Jump to latest</button>
        </div>
    </div>

    <div style="max-width: 1000px; margin: 20px auto;">
        <div class="table-wrapper reconcile-wrapper" id="reconcile-wrapper">
            <table class="reconcile-table">
                <thead>
                <tr>
                    <th style="width:70px; text-align:center;">Posted</th>
                    <th style="width:120px;">Date</th>
                    <th>Payee</th>
                    <th style="width:140px; text-align:right;">Amount</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($table_rows as $row): ?>
                    <?php
                    $isUnposted = empty($row->posted_at);
                    $isRowReconciled = !empty($row->reconciled_at);
                    ?>
                    <tr class="<?= $isUnposted ? 'is-unposted' : '' ?> <?= $isRowReconciled ? 'is-reconciled' : '' ?>">
                        <td style="text-align:center;">
                            <form method="POST" action="/reconcile/toggle" style="margin:0;">
                                <input type="hidden" name="transaction_id" value="<?= (int)($row->id ?? 0) ?>">
                                <input type="hidden" name="rpaccount_id" value="<?= (int)$rpaccountId ?>">
                                <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                                <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                                <input type="hidden" name="beginning_balance" value="<?= htmlspecialchars($beginningBalanceValue) ?>">
                                <input type="hidden" name="ending_balance" value="<?= htmlspecialchars($endingBalanceValue) ?>">
                                <input type="hidden" name="statement_balance" value="<?= htmlspecialchars($endingBalanceValue) ?>">
                                <input type="hidden" name="is_posted" value="0">
                                <input
                                    type="checkbox"
                                    name="is_posted"
                                    value="1"
                                    <?= !empty($row->posted_at) ? 'checked' : '' ?>
                                    <?= $isRowReconciled ? 'disabled' : '' ?>
                                    onchange="this.form.submit()"
                                >
                            </form>
                        </td>

                        <td><?= htmlspecialchars(!empty($row->transaction_date) ? substr((string)$row->transaction_date, 0, 10) : '') ?></td>

                        <td>
                            <?= htmlspecialchars($row->payee_name ?? '') ?>

                            <?php if ($isUnposted): ?>
                                <span style="margin-left:8px; font-size:12px; color:#a33;">(unposted)</span>
                            <?php endif; ?>

                            <?php if ($isRowReconciled): ?>
                                <span style="margin-left:8px; font-size:12px; color:#185;">(reconciled)</span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align:right;"><?= number_format((float)($row->amount ?? 0), 2, '.', ',') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif (!empty($rpaccountId) && !empty($startDate) && !empty($endDate)): ?>
    <p>No transactions found for that statement window.</p>
<?php endif; ?>

<style>
.filter-form .filter-group select,
.filter-form .filter-group input[type="date"],
.filter-form .filter-group input[type="number"]{
    height: 44px;
    box-sizing: border-box;
}

.reconcile-wrapper{
    max-width: 1000px;
    margin: 20px auto;
    height: 60vh;
    overflow-y: auto;
    overflow-x: auto;
    scroll-behavior: smooth;
}

.reconcile-table-tools{
    max-width: 1000px;
    margin: 8px auto 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.reconcile-table-hint{
    font-size: 12px;
    color: #666;
}

.reconcile-table-buttons{
    display: flex;
    gap: 8px;
}

.reconcile-nav-btn{
    padding: 6px 10px;
    border: 1px solid #cfd6e4;
    border-radius: 6px;
    background: #fff;
    color: #2f4f7f;
    font-size: 12px;
    cursor: pointer;
}

.reconcile-nav-btn:hover{
    background: #f5f8ff;
}

.status-action-row{
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.status-pill{
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
}

.status-pill-finalized{
    border: 1px solid #2b7;
    color: #185;
    background: #f4fff9;
}

.status-pill-neutral{
    border: 1px solid #ccc;
    color: #555;
    background: #fff;
}

.status-pill-reconciled{
    border: 1px solid #2b7;
    color: #185;
    background: #fff;
}

.status-pill-not-reconciled{
    border: 1px solid #c66;
    color: #933;
    background: #fff;
}

table.reconcile-table form{
    width: auto;
    margin: 0;
}

table.reconcile-table input[type="checkbox"]{
    width: auto !important;
    display: inline-block !important;
    height: auto !important;
    margin: 0 !important;
    border: none !important;
}

table.reconcile-table input[type="checkbox"][disabled]{
    opacity: 0.65;
    cursor: not-allowed;
}

table.reconcile-table{
    width: 100%;
    max-width: 100%;
    table-layout: fixed;
    margin: 0 auto;
    position: static;
    font-size: 1rem;
}

table.reconcile-table thead,
table.reconcile-table tbody{
    position: static;
    top: auto;
}

table.reconcile-table th,
table.reconcile-table td{
    padding: 10px 12px;
    border-bottom: 1px solid #ddd;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    background: #fff;
}

table.reconcile-table th:nth-child(1),
table.reconcile-table td:nth-child(1){ width: 70px; text-align: center; }

table.reconcile-table th:nth-child(2),
table.reconcile-table td:nth-child(2){ width: 120px; }

table.reconcile-table th:nth-child(4),
table.reconcile-table td:nth-child(4){ width: 140px; text-align: right; }

table.reconcile-table tr.is-unposted td{
    background-color: rgba(220, 80, 80, 0.12);
}

table.reconcile-table tr.is-unposted:hover td{
    background-color: rgba(220, 80, 80, 0.18);
}

table.reconcile-table tr.is-reconciled td{
    background-color: rgba(70, 170, 110, 0.10);
}

table.reconcile-table tr.is-reconciled:hover td{
    background-color: rgba(70, 170, 110, 0.16);
}

.finalize-reconcile-btn{
    padding: 8px 14px;
    border: 1px solid #2b7;
    border-radius: 6px;
    background: #f7fffb;
    color: #185;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.finalize-reconcile-btn:hover{
    background: #eefcf5;
}

.undo-finalize-btn{
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid #c66;
    background: #fff7f7;
    color: #933;
    font-size: 12px;
    cursor: pointer;
}

.undo-finalize-btn:hover{
    background: #fff0f0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('reconcile-wrapper');
    const jumpLatest = document.getElementById('jump-latest');
    const jumpEarliest = document.getElementById('jump-earliest');

    if (!wrapper) {
        return;
    }

    function scrollToLatest() {
        wrapper.scrollTop = wrapper.scrollHeight;
    }

    function scrollToEarliest() {
        wrapper.scrollTop = 0;
    }

    if (jumpLatest) {
        jumpLatest.addEventListener('click', function () {
            scrollToLatest();
        });
    }

    if (jumpEarliest) {
        jumpEarliest.addEventListener('click', function () {
            scrollToEarliest();
        });
    }

    setTimeout(function () {
        scrollToLatest();
    }, 150);
});
</script>

<?php
require __DIR__ . '/../partials/footer.view.php';
?>