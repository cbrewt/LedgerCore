<?php

namespace Core\Controllers;

use Core\Repositories\TransactionRepository;
use DateTime;

class TransactionsController extends Controller
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {
    }

    public function create(): void
    {
        $this->requireMethod('GET');

        $options = $this->transactionRepo()->getLookupOptions();
        $creditCards = $this->transactionRepo()->getCreditCardOptions();

        view('transactions/create.view.php', [
            'title' => 'Create Transaction',
            'heading' => 'New Transaction',
            'accounts' => $options['accounts'],
            'types' => $options['types'],
            'payees' => $options['payees'],
            'categories' => $options['categories'],
            'creditCards' => $creditCards,
        ]);
    }

    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $isPrintMode = isset($_GET['print']) && $_GET['print'] === '1';
        [$sortField, $sortOrder] = $this->sanitizeSort($_GET['sortField'] ?? 'posted_at', $_GET['sortOrder'] ?? 'DESC');

        $filters = [
            'rpaccount_id' => $this->cleanFilter($_GET['rpaccount_id'] ?? null),
            'type_id' => $this->cleanFilter($_GET['type_id'] ?? null),
            'payee_id' => $this->cleanFilter($_GET['payee_id'] ?? null),
            'category_id' => $this->cleanFilter($_GET['category_id'] ?? null),
            'start_date' => $this->cleanDateFilter($_GET['start_date'] ?? null),
            'end_date' => $this->cleanDateFilter($_GET['end_date'] ?? null),
            'include_unposted' => isset($_GET['include_unposted']) && $_GET['include_unposted'] === '1' ? 1 : 0,
        ];

        if ($filters['start_date'] !== null && $filters['end_date'] !== null && $filters['start_date'] > $filters['end_date']) {
            [$filters['start_date'], $filters['end_date']] = [$filters['end_date'], $filters['start_date']];
        }

        $defaultPageSize = 10;
        $totalTransactions = $this->transactionRepo()->countFiltered($filters);

        if ($isPrintMode) {
            $limit = max(1, $totalTransactions);
            $page = 1;
            $offset = 0;
            $totalPages = 1;
        } else {
            $limit = isset($_GET['limit']) && is_numeric($_GET['limit'])
                ? min((int) $_GET['limit'], $defaultPageSize)
                : $defaultPageSize;
            $limit = max(1, $limit);

            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

            if (isset($_SESSION['prev_limit']) && (int) $_SESSION['prev_limit'] !== $limit) {
                $page = 1;
            }
            $_SESSION['prev_limit'] = $limit;

            $totalPages = max(1, (int) ceil($totalTransactions / $limit));
            if ($page > $totalPages) {
                $page = $totalPages;
            }
            $offset = ($page - 1) * $limit;
        }

        $tableRows = $this->transactionRepo()->getFiltered($filters, $limit, $offset, $sortField, $sortOrder);
        $totalSum = $this->transactionRepo()->sumFilteredTransactions($filters);

        $printQuery = array_filter([
            'rpaccount_id' => $filters['rpaccount_id'],
            'type_id' => $filters['type_id'],
            'payee_id' => $filters['payee_id'],
            'category_id' => $filters['category_id'],
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'include_unposted' => $filters['include_unposted'],
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'print' => 1
        ], static fn($value) => $value !== null && $value !== '');
        $printUrl = '/transactions?' . http_build_query($printQuery);

        if ($isPrintMode) {
            view('transactions/print.view.php', [
                'title' => 'Transactions (Print)',
                'heading' => 'Transactions',
                'table_rows' => $tableRows,
                'filters' => $filters,
                'sortField' => $sortField,
                'sortOrder' => $sortOrder,
                'totalSum' => $totalSum,
                'totalTransactions' => $totalTransactions,
                'includeUnposted' => (bool) $filters['include_unposted'],
            ]);
            return;
        }

        $options = $this->transactionRepo()->getLookupOptions();

        view('transactions/index.view.php', [
            'title' => 'Transactions',
            'heading' => 'Transactions',
            'table_rows' => $tableRows,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'accounts' => $this->toObjectList($options['accounts']),
            'types' => $this->toObjectList($options['types']),
            'payees' => $this->toObjectList($options['payees']),
            'categories' => $this->toObjectList($options['categories']),
            'totalSum' => $totalSum,
            'limit' => $limit,
            'offset' => $offset,
            'totalTransactions' => $totalTransactions,
            'isPrintMode' => false,
            'printUrl' => $printUrl,
            'includeUnposted' => (bool) $filters['include_unposted'],
        ]);
    }

    public function show(): void
    {
        $id = $this->requirePositiveIdFromQuery('id', 'Invalid transaction ID.');
        $row = $this->transactionRepo()->findDetailed($id);
        if ($row === null) {
            $this->abort(404, 'Transaction not found.');
        }

        view('transactions/show.view.php', [
            'title' => 'Transaction',
            'heading' => 'Show Transaction',
            'table_row' => $row,
            'transactionId' => $id
        ]);
    }

    public function edit(): void
    {
        $this->requireMethod('GET');

        $id = $this->requirePositiveIdFromQuery('id', 'Invalid transaction ID.');
        $transaction = $this->transactionRepo()->findDetailed($id);
        if ($transaction === null) {
            $this->abort(404, 'Transaction not found.');
        }

        $options = $this->transactionRepo()->getLookupOptions();

        view('transactions/edit.view.php', [
            'title' => 'Edit Transaction',
            'heading' => 'Edit Transaction',
            'transaction' => $transaction,
            'accounts' => $options['accounts'],
            'types' => $options['types'],
            'payees' => $options['payees'],
            'categories' => $options['categories']
        ]);
    }

    public function store(): void
    {
        $this->requireMethod('POST');

        $payload = [
            'transaction_date' => filter_input(INPUT_POST, 'transaction_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'rpaccount_id' => filter_input(INPUT_POST, 'rpaccount_id', FILTER_VALIDATE_INT),
            'type_id' => filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT),
            'payee_id' => filter_input(INPUT_POST, 'payee_id', FILTER_VALIDATE_INT),
            'category_id' => filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT),
            'amount' => filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT),
        ];

        foreach (['transaction_date', 'rpaccount_id', 'type_id', 'payee_id', 'category_id', 'amount'] as $key) {
            if ($payload[$key] === false || $payload[$key] === null) {
                $this->abort(400, "Missing or invalid value for {$key}.");
            }
        }

        if ((int) $payload['type_id'] === 18 && (int) $payload['category_id'] === 23) {
            $creditCardId = filter_input(INPUT_POST, 'credit_card_id', FILTER_VALIDATE_INT);
            if ($creditCardId === false || $creditCardId === null) {
                $this->abort(400, 'Missing or invalid value for credit_card_id.');
            }
            $payload['credit_card_id'] = $creditCardId;
        }

        $this->transactionRepo()->create($payload);
        $this->redirect('/transactions');
    }

    public function update(): void
    {
        $this->requireMethod('PATCH');

        if (!isset($_POST['submit'])) {
            $this->redirect('/transactions');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $transactionDate = filter_input(INPUT_POST, 'transaction_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $postedAt = $this->normalizePostedAt($_POST['posted_at'] ?? null);
        $rpaccountId = filter_input(INPUT_POST, 'rpaccount_id', FILTER_VALIDATE_INT);
        $typeId = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
        $payeeId = filter_input(INPUT_POST, 'payee_id', FILTER_VALIDATE_INT);
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

        if (
            !$id ||
            !$transactionDate ||
            !$rpaccountId ||
            !$typeId ||
            !$payeeId ||
            !$categoryId ||
            $amount === false ||
            $amount === null
        ) {
            $this->abort(400, 'Invalid transaction update payload.');
        }

        $this->transactionRepo()->update([
            'id' => $id,
            'transaction_date' => $transactionDate,
            'posted_at' => $postedAt,
            'rpaccount_id' => $rpaccountId,
            'type_id' => $typeId,
            'payee_id' => $payeeId,
            'category_id' => $categoryId,
            'amount' => $amount,
        ]);

        $this->redirect("/accounts/show?id={$rpaccountId}");
    }

    public function delete(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
        }

        if (!$id || $id <= 0) {
            $this->abort(400, 'Transaction ID is required.');
        }

        $this->transactionRepo()->delete($id);
        $this->redirect('/transactions');
    }

    private function transactionRepo(): TransactionRepository
    {
        return $this->transactionRepository;
    }

    private function cleanFilter($value): ?int
    {
        return (isset($value) && is_numeric($value) && (int) $value > 0) ? (int) $value : null;
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

    private function sanitizeSort(string $sortField, string $sortOrder): array
    {
        $allowed = ['id', 'transaction_date', 'posted_at', 'account_name', 'type_name', 'payee_name', 'category_name', 'amount'];
        if (!in_array($sortField, $allowed, true)) {
            $sortField = 'posted_at';
        }

        $sortOrder = strtoupper($sortOrder);
        if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
            $sortOrder = 'DESC';
        }

        return [$sortField, $sortOrder];
    }

    private function normalizePostedAt($value): ?string
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

        return $value . ' 00:00:00';
    }

    private function toObjectList(array $rows): array
    {
        return array_map(static fn(array $row) => (object) $row, $rows);
    }
}
