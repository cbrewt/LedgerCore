<?php

namespace Core\Controllers;

use Core\Repositories\AccountRepository;
use Core\Repositories\ReconciliationRepository;
use Core\Repositories\TransactionRepository;
use DateTime;
use Throwable;

class ReconcileController extends Controller
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private AccountRepository $accountRepository,
        private ReconciliationRepository $reconciliationRepository
    ) {
    }

    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $rpaccountId      = $this->cleanFilter($_GET['rpaccount_id'] ?? null);
        $startDate        = $this->cleanDateFilter($_GET['start_date'] ?? null);
        $endDate          = $this->cleanDateFilter($_GET['end_date'] ?? null);
        $beginningBalance = $this->cleanMoney($_GET['beginning_balance'] ?? null);
        $endingBalance    = $this->cleanMoney($_GET['ending_balance'] ?? ($_GET['statement_balance'] ?? null));

        if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        /**
         * Safe auto-fill:
         * If an account is selected and beginning_balance was left blank,
         * try to use the most recent reconciliation's ending balance,
         * but ONLY when the selected start_date is AFTER the latest
         * reconciliation end_date.
         *
         * This prevents historical windows from being overwritten with a
         * future carry-forward balance.
         *
         * Manual entry always wins.
         * If anything goes wrong, fail soft and keep the page usable.
         */
        if ($rpaccountId && $beginningBalance === null && $startDate !== null) {
            try {
                $latestReconciliation = $this->reconciliationRepo()->findLatestForAccount($rpaccountId);

                if (
                    $latestReconciliation &&
                    isset($latestReconciliation['ending_balance'], $latestReconciliation['end_date']) &&
                    $startDate > (string) $latestReconciliation['end_date']
                ) {
                    $beginningBalance = round((float) $latestReconciliation['ending_balance'], 2);
                }
            } catch (Throwable $e) {
                error_log('ReconcileController::index beginning-balance autofill failed: ' . $e->getMessage());
            }
        }

        $accounts = $this->accountRepo()->all();

        $tableRows             = [];
        $totalCount            = 0;
        $postedCount           = 0;
        $postedTotal           = 0.00;
        $expectedEndingBalance = null;
        $difference            = null;
        $reconciledCount       = 0;
        $isFinalized           = false;

        if ($rpaccountId && $startDate && $endDate) {
            $tableRows = $this->transactionRepo()->getForReconcile($rpaccountId, $startDate, $endDate);
            $totalCount = count($tableRows);

            foreach ($tableRows as $row) {
                if (!empty($row->posted_at)) {
                    $postedTotal += (float) ($row->amount ?? 0);
                    $postedCount++;
                }

                if (!empty($row->reconciled_at)) {
                    $reconciledCount++;
                }
            }

            $postedTotal = round($postedTotal, 2);

            if ($beginningBalance !== null) {
                $expectedEndingBalance = round($beginningBalance + $postedTotal, 2);
            }

            if ($endingBalance !== null && $expectedEndingBalance !== null) {
                $difference = round($endingBalance - $expectedEndingBalance, 2);
            }

            $isFinalized = ($totalCount > 0 && $reconciledCount === $totalCount);
        }

        view('reconcile/index.view.php', [
            'title'                 => 'Reconcile',
            'heading'               => 'Reconcile',
            'accounts'              => $accounts,
            'rpaccountId'           => $rpaccountId,
            'startDate'             => $startDate,
            'endDate'               => $endDate,
            'beginningBalance'      => $beginningBalance,
            'endingBalance'         => $endingBalance,
            'statementBalance'      => $endingBalance, // backward compatibility for the view
            'table_rows'            => $tableRows,
            'totalCount'            => $totalCount,
            'postedCount'           => $postedCount,
            'postedTotal'           => $postedTotal,
            'expectedEndingBalance' => $expectedEndingBalance,
            'difference'            => $difference,
            'reconciledCount'       => $reconciledCount,
            'isFinalized'           => $isFinalized,
        ]);
    }

    /**
     * Toggle posted state for a transaction
     */
    public function toggle(): void
    {
        $this->requireMethod('POST');

        $transactionId = isset($_POST['transaction_id']) ? (int) $_POST['transaction_id'] : 0;

        if ($transactionId <= 0) {
            $this->redirect('/reconcile');
        }

        $isPosted = isset($_POST['is_posted']) && $_POST['is_posted'] === '1';

        $this->transactionRepo()->setPostedState($transactionId, $isPosted);

        $query = http_build_query(array_filter([
            'rpaccount_id'      => $_POST['rpaccount_id'] ?? '',
            'start_date'        => $_POST['start_date'] ?? '',
            'end_date'          => $_POST['end_date'] ?? '',
            'beginning_balance' => $_POST['beginning_balance'] ?? '',
            'ending_balance'    => $_POST['ending_balance'] ?? ($_POST['statement_balance'] ?? ''),
        ], static fn($v) => $v !== '' && $v !== null));

        $this->redirect('/reconcile' . ($query ? ('?' . $query) : ''));
    }

    /**
     * Finalize reconciliation:
     * 1) stamps transactions in the statement window with reconciled_at
     * 2) writes one row to reconciliations unless that exact window already exists
     */
    public function finalize(): void
    {
        $this->requireMethod('POST');

        $rpaccountId      = $this->cleanFilter($_POST['rpaccount_id'] ?? null);
        $startDate        = $this->cleanDateFilter($_POST['start_date'] ?? null);
        $endDate          = $this->cleanDateFilter($_POST['end_date'] ?? null);
        $beginningBalance = $this->cleanMoney($_POST['beginning_balance'] ?? null);
        $endingBalance    = $this->cleanMoney($_POST['ending_balance'] ?? ($_POST['statement_balance'] ?? null));
        $difference       = $this->cleanMoney($_POST['difference'] ?? null);

        if (!$rpaccountId || !$startDate || !$endDate) {
            $this->redirect('/reconcile');
        }

        if ($difference === null || round($difference, 2) !== 0.00) {
            $query = http_build_query(array_filter([
                'rpaccount_id'      => $rpaccountId,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'beginning_balance' => $beginningBalance !== null ? number_format($beginningBalance, 2, '.', '') : '',
                'ending_balance'    => $endingBalance !== null ? number_format($endingBalance, 2, '.', '') : '',
            ], static fn($v) => $v !== '' && $v !== null));

            $this->redirect('/reconcile' . ($query ? ('?' . $query) : ''));
        }

        if ($beginningBalance === null || $endingBalance === null) {
            $query = http_build_query(array_filter([
                'rpaccount_id'      => $rpaccountId,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'beginning_balance' => '',
                'ending_balance'    => '',
            ], static fn($v) => $v !== '' && $v !== null));

            $this->redirect('/reconcile' . ($query ? ('?' . $query) : ''));
        }

        $this->transactionRepo()->markReconciledWindow(
            $rpaccountId,
            $startDate,
            $endDate
        );

        try {
            if (!$this->reconciliationRepo()->existsForWindow($rpaccountId, $startDate, $endDate)) {
                $this->reconciliationRepo()->create([
                    'rpaccount_id'      => $rpaccountId,
                    'start_date'        => $startDate,
                    'end_date'          => $endDate,
                    'beginning_balance' => number_format($beginningBalance, 2, '.', ''),
                    'ending_balance'    => number_format($endingBalance, 2, '.', ''),
                ]);
            }
        } catch (Throwable $e) {
            error_log('ReconcileController::finalize reconciliation history insert failed: ' . $e->getMessage());
        }

        $query = http_build_query(array_filter([
            'rpaccount_id'      => $rpaccountId,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'beginning_balance' => number_format($beginningBalance, 2, '.', ''),
            'ending_balance'    => number_format($endingBalance, 2, '.', ''),
        ], static fn($v) => $v !== '' && $v !== null));

        $this->redirect('/reconcile' . ($query ? ('?' . $query) : ''));
    }

    /**
     * Undo finalization:
     * 1) clears reconciled_at for the statement window
     * 2) deletes the matching row from reconciliations
     * 3) redirects back to reconcile with the same filters
     */
    public function undoFinalize(): void
    {
        $this->requireMethod('POST');

        $rpaccountId      = $this->cleanFilter($_POST['rpaccount_id'] ?? null);
        $startDate        = $this->cleanDateFilter($_POST['start_date'] ?? null);
        $endDate          = $this->cleanDateFilter($_POST['end_date'] ?? null);
        $beginningBalance = $this->cleanMoney($_POST['beginning_balance'] ?? null);
        $endingBalance    = $this->cleanMoney($_POST['ending_balance'] ?? ($_POST['statement_balance'] ?? null));

        if (!$rpaccountId || !$startDate || !$endDate) {
            $this->redirect('/reconcile');
        }

        $this->transactionRepo()->clearReconciledWindow(
            $rpaccountId,
            $startDate,
            $endDate
        );

        try {
            $this->reconciliationRepo()->deleteForWindow(
                $rpaccountId,
                $startDate,
                $endDate
            );
        } catch (Throwable $e) {
            error_log('ReconcileController::undoFinalize reconciliation history delete failed: ' . $e->getMessage());
        }

        $query = http_build_query(array_filter([
            'rpaccount_id'      => $rpaccountId,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'beginning_balance' => $beginningBalance !== null ? number_format($beginningBalance, 2, '.', '') : '',
            'ending_balance'    => $endingBalance !== null ? number_format($endingBalance, 2, '.', '') : '',
        ], static fn($v) => $v !== '' && $v !== null));

        $this->redirect('/reconcile' . ($query ? ('?' . $query) : ''));
    }

    private function transactionRepo(): TransactionRepository
    {
        return $this->transactionRepository;
    }

    private function accountRepo(): AccountRepository
    {
        return $this->accountRepository;
    }

    private function reconciliationRepo(): ReconciliationRepository
    {
        return $this->reconciliationRepository;
    }

    private function cleanFilter($value): ?int
    {
        return (isset($value) && is_numeric($value) && (int) $value > 0)
            ? (int) $value
            : null;
    }

    private function cleanDateFilter($value): ?string
    {
        if (!isset($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);

        if (!$dt || $dt->format('Y-m-d') !== $value) {
            return null;
        }

        return $value;
    }

    private function cleanMoney($value): ?float
    {
        if (!isset($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '', $value);

        if (!is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }
}
