<?php

namespace Core\Repositories;

use Core\DatabaseHelper;
use Core\Models\Account;

class AccountRepository extends BaseRepository
{
    protected static string $table = 'rpaccounts';

    public function create(array $data)
    {
        return DatabaseHelper::insert(self::$table, $data);
    }

    public function update(int $id, array $data): bool
    {
        if ($id <= 0 || $data === []) {
            return false;
        }

        $allowed = ['account_name', 'account_type_id'];
        $updates = [];
        foreach ($allowed as $column) {
            if (array_key_exists($column, $data)) {
                $updates[$column] = $data[$column];
            }
        }

        if ($updates === []) {
            return false;
        }

        $sqlParts = [];
        $params = [];
        foreach ($updates as $column => $value) {
            $sqlParts[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $params['id'] = $id;
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $sqlParts) . " WHERE id = :id";

        return DatabaseHelper::executeQuery($sql, $params) ? true : false;
    }

    /**
     * Returns only non-archived accounts by default.
     * Requires rpaccounts.is_archived column.
     */
    public function all(): array
    {
        $query = "SELECT id, account_name, account_type_id
                  FROM " . static::$table . "
                  WHERE is_archived = 0
                  ORDER BY account_name ASC";

        $results = DatabaseHelper::fetchAllByQuery($query);

        return array_map(fn($row) => new Account($row), $results);
    }

    /**
     * Returns ALL accounts, including archived ones.
     * Useful for an "Archived Accounts" view or admin screens.
     *
     * Note: This selects is_archived and archived_at so your UI can display status.
     */
    public function allIncludingArchived(): array
    {
        $query = "SELECT id, account_name, account_type_id, is_archived, archived_at
                  FROM " . static::$table . "
                  ORDER BY account_name ASC";

        $results = DatabaseHelper::fetchAllByQuery($query);

        return array_map(fn($row) => new Account($row), $results);
    }

    public function find(int $id): ?Account
    {
        $query = "SELECT id, account_name, account_type_id, is_archived, archived_at
                  FROM " . static::$table . "
                  WHERE id = :id";
        $result = DatabaseHelper::fetchByQuery($query, ['id' => $id]);

        return $result ? new Account($result) : null;
    }

    /**
     * Archive (soft delete) an account. Keeps transactions intact.
     * Requires:
     *  - rpaccounts.is_archived TINYINT(1) NOT NULL DEFAULT 0
     * Optional:
     *  - rpaccounts.archived_at DATETIME NULL
     */
    public function archive(int $id): bool
    {
        // If you did NOT add archived_at, remove ", archived_at = NOW()" from this SQL.
        $sql = "UPDATE " . static::$table . "
                SET is_archived = 1, archived_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        return DatabaseHelper::executeQuery($sql, ['id' => $id]) ? true : false;
    }

    /**
     * Restore a previously archived account.
     */
    public function restore(int $id): bool
    {
        $sql = "UPDATE " . static::$table . "
                SET is_archived = 0, archived_at = NULL
                WHERE id = :id";

        return DatabaseHelper::executeQuery($sql, ['id' => $id]) ? true : false;
    }

    /**
     * Hard delete (NOT recommended for this app).
     * Retained for compatibility if older code still calls delete().
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
        $affected = DatabaseHelper::executeQuery($sql, ['id' => $id]);

        return $affected ? true : false;
    }

    public function updateBalances()
    {
        error_log("🔍 updateBalances() called for accounts!");

        // Proceed with balance updates
        DatabaseHelper::executeQuery("START TRANSACTION");

        $query1 = "UPDATE account_balances ab
               JOIN (
                   SELECT rpaccount_id, COALESCE(SUM(amount), 0) AS total_balance
                   FROM transactions
                   GROUP BY rpaccount_id
               ) AS calculated ON ab.rpaccount_id = calculated.rpaccount_id
               SET ab.balance = calculated.total_balance";

        DatabaseHelper::executeQuery($query1);

        $query2 = "UPDATE credit_cards cc
               JOIN (
                   SELECT rpaccount_id, COALESCE(SUM(amount), 0) AS total_balance
                   FROM transactions
                   GROUP BY rpaccount_id
               ) AS calculated ON cc.rpaccount_id = calculated.rpaccount_id
               SET cc.balance = calculated.total_balance";

        DatabaseHelper::executeQuery($query2);

        DatabaseHelper::executeQuery("COMMIT");

        error_log("✅ updateBalances() executed successfully.");
    }

    public function getAllBalances(): array
    {
        $query = "SELECT * FROM account_balances ORDER BY id ASC";
        return DatabaseHelper::fetchAllByQuery($query);
    }

    public function getAllTotals()
    {
        $sql = "
        SELECT 
            SUM(CASE WHEN at.account_type_name = 'Checking' THEN ab.balance ELSE 0 END) AS checking_total,
            SUM(CASE WHEN at.account_type_name = 'Savings' THEN ab.balance ELSE 0 END) AS savings_total,
            SUM(CASE WHEN at.account_type_name IN ('Checking', 'Savings') THEN ab.balance ELSE 0 END) AS cash_total,
            SUM(cc.credit_limit - cc.balance) AS available_credit_total,
            SUM(cc.balance) AS credit_card_balance
        FROM account_balances ab
        JOIN account_types at ON ab.account_type_id = at.id
        LEFT JOIN credit_cards cc ON ab.rpaccount_id = cc.rpaccount_id
    ";

        $result = DatabaseHelper::fetchByQuery($sql);

        return [
            'checking_total' => $result['checking_total'] ?? 0,
            'savings_total' => $result['savings_total'] ?? 0,
            'cash_total' => $result['cash_total'] ?? 0,
            'available_credit_total' => $result['available_credit_total'] ?? 0,
            'credit_card_balance' => $result['credit_card_balance'] ?? 0,
        ];
    }

    public function getTransactions($accountId, $limit = 10, $offset = 0)
    {
        $sql = "SELECT 
                id, 
                transaction_date, 
                amount, 
                transaction_type_id, 
                payee_id, 
                category_id, 
                notes 
            FROM transactions 
            WHERE rpaccount_id = ? 
            ORDER BY transaction_date DESC
            LIMIT ? OFFSET ?";

        return DatabaseHelper::fetchAllByQuery($sql, [$accountId, $limit, $offset]);
    }
}
