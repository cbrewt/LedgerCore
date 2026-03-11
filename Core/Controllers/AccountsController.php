<?php

namespace Core\Controllers;

use Core\Models\Account;
use Core\Repositories\AccountRepository;
use Core\Repositories\AccountTypeRepository;
use Core\Repositories\TransactionRepository;

class AccountsController extends Controller
{
    public function __construct(
        private AccountRepository $accountRepository,
        private AccountTypeRepository $accountTypeRepository,
        private TransactionRepository $transactionRepository
    ) {
    }

    public function index(): void
    {
        $showArchived = isset($_GET['archived']) && $_GET['archived'] === '1';
        $accounts = $showArchived
            ? $this->accountRepo()->allIncludingArchived()
            : $this->accountRepo()->all();

        view('accounts/index.view.php', [
            'title' => 'Accounts',
            'heading' => $showArchived ? 'Accounts (Including Archived)' : 'Accounts',
            'accounts' => $accounts,
            'showArchived' => $showArchived
        ]);
    }

    public function show(): void
    {
        $accountId = $this->requirePositiveIdFromQuery();
        $account = Account::find($accountId);

        if (!$account) {
            $this->abort(404, 'Error: Account not found.');
        }

        $sortField = $_GET['sortField'] ?? 'transaction_date';
        $sortOrder = $_GET['sortOrder'] ?? 'DESC';
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $filters = [
            'rpaccount_id' => $accountId,
            'include_unposted' => 0
        ];
        $transactions = $this->transactionRepo()->getFiltered($filters, $limit, $offset, $sortField, $sortOrder);
        $totalTransactions = $this->transactionRepo()->countFiltered($filters);
        $totalPages = ceil($totalTransactions / $limit);

        view('accounts/show.view.php', [
            'title' => 'Account Details',
            'heading' => $account->account_name,
            'account' => $account,
            'transactions' => $transactions,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    public function balances(): void
    {
        $this->accountRepo()->updateBalances();
        $tableRows = $this->accountRepo()->getAllBalances();

        view('accounts/balances.view.php', [
            'title' => 'Balances',
            'heading' => 'Account Balances',
            'table_rows' => $tableRows
        ]);
    }

    public function overview(): void
    {
        $this->accountRepo()->updateBalances();
        $totals = $this->accountRepo()->getAllTotals();

        view('accounts/accounts_overview.view.php', [
            'title' => 'Accounts Overview',
            'heading' => 'Account Balances Overview',
            'totals' => $totals
        ]);
    }

    public function create(): void
    {
        $this->requireMethod('GET');

        view('accounts/create.view.php', [
            'title' => 'Create Account',
            'heading' => 'Add New Account',
            'accountTypes' => $this->accountTypeRepo()->all(),
            'error' => null,
            'oldAccountName' => '',
            'oldAccountTypeId' => null
        ]);
    }

    public function store(): void
    {
        $this->requireMethod('POST');

        $accountName = trim((string) filter_input(INPUT_POST, 'account_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $accountTypeId = filter_input(INPUT_POST, 'account_type_id', FILTER_VALIDATE_INT);

        if ($accountName === '' || !$accountTypeId || $accountTypeId <= 0) {
            $this->renderCreateForm('Please provide a valid account name and account type.', $accountName, $accountTypeId);
            return;
        }

        $created = $this->accountRepo()->create([
            'account_name' => $accountName,
            'account_type_id' => $accountTypeId,
        ]);

        if ($created) {
            header('Location: /accounts');
            exit;
        }

        $this->renderCreateForm('Failed to create account.', $accountName, $accountTypeId);
    }

    public function edit(): void
    {
        $accountId = $this->requirePositiveIdFromQuery();
        $account = $this->accountRepo()->find($accountId);

        if (!$account) {
            header('Location: /accounts?error=' . urlencode('Account not found.'));
            exit;
        }

        view('accounts/edit.view.php', [
            'title' => 'Edit Account',
            'heading' => 'Edit Account Name',
            'accountTypes' => $this->accountTypeRepo()->all(),
            'row_edit' => $account,
            'error' => null
        ]);
    }

    public function update(): void
    {
        $this->requireMethod('PATCH');

        $accountId = $this->requirePositiveIdFromPost();
        $accountName = trim((string) filter_input(INPUT_POST, 'account_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $accountTypeId = filter_input(INPUT_POST, 'account_type_id', FILTER_VALIDATE_INT);

        if ($accountName === '' || !$accountTypeId || $accountTypeId <= 0) {
            $this->renderEditForm($accountId, 'Please provide a valid account name and account type.', $accountName, $accountTypeId);
            return;
        }

        $updated = $this->accountRepo()->update($accountId, [
            'account_name' => $accountName,
            'account_type_id' => $accountTypeId,
        ]);

        if ($updated) {
            header('Location: /accounts');
            exit;
        }

        $this->renderEditForm($accountId, 'Failed to update account.', $accountName, $accountTypeId);
    }

    public function delete(): void
    {
        $this->requireMethod('DELETE');

        $accountId = $this->requirePositiveIdFromPost();
        $archived = $this->accountRepo()->archive($accountId);

        if ($archived) {
            header('Location: /accounts?success=Account archived');
            exit;
        }

        $this->abort(500, 'Failed to archive account');
    }

    public function restore(): void
    {
        $this->requireMethod('POST');

        $accountId = $this->requirePositiveIdFromPost();
        $restored = $this->accountRepo()->restore($accountId);

        if ($restored) {
            header('Location: /accounts?archived=1&success=Account restored');
            exit;
        }

        $this->abort(500, 'Failed to restore account');
    }

    private function accountRepo(): AccountRepository
    {
        return $this->accountRepository;
    }

    private function accountTypeRepo(): AccountTypeRepository
    {
        return $this->accountTypeRepository;
    }

    private function transactionRepo(): TransactionRepository
    {
        return $this->transactionRepository;
    }

    private function renderCreateForm(?string $error, string $oldAccountName, $oldAccountTypeId): void
    {
        view('accounts/create.view.php', [
            'title' => 'Create Account',
            'heading' => 'Add New Account',
            'accountTypes' => $this->accountTypeRepo()->all(),
            'error' => $error,
            'oldAccountName' => $oldAccountName,
            'oldAccountTypeId' => $oldAccountTypeId
        ]);
    }

    private function renderEditForm(int $accountId, string $error, ?string $attemptedName = null, ?int $attemptedTypeId = null): void
    {
        $account = $this->accountRepo()->find($accountId);

        if (!$account) {
            $this->abort(404, 'Account not found.');
        }

        if ($attemptedName !== null) {
            $account->account_name = $attemptedName;
        }
        if ($attemptedTypeId !== null) {
            $account->account_type_id = $attemptedTypeId;
        }

        view('accounts/edit.view.php', [
            'title' => 'Edit Account',
            'heading' => 'Edit Account Name',
            'accountTypes' => $this->accountTypeRepo()->all(),
            'row_edit' => $account,
            'error' => $error
        ]);
    }

}
