<?php

namespace Core;

class QueryBuilder
{
    // Define common query fragments
    private const TRANSACTIONS_JOIN = '
        FROM transactions t
        INNER JOIN rpaccounts rp ON t.rpaccount_id = rp.id
        INNER JOIN transaction_types tt ON t.type_id = tt.id
        LEFT JOIN payees p ON t.payee_id = p.id
        INNER JOIN categories c ON t.category_id = c.id
    ';

    private const TRANSACTIONS_COLUMNS = '
        t.id, t.transaction_date, rp.account_name, tt.type_name, p.payee_name, c.category_name, t.amount
    ';

    private const BALANCES_CONDITIONS = [
        'totalChecking' => 'rpaccount_id IN (SELECT id FROM rpaccounts WHERE account_type_id = 3)',
        'totalCreditCard' => 'rpaccount_id IN (SELECT id FROM rpaccounts WHERE account_type_id = 1)',
        'totalSavings' => 'id IN (SELECT id FROM rpaccounts WHERE account_type_id = 2)',
    ];

    // Queries grouped by entity
    public static function getQueries(): array
    {
        return [
            'transactions' => self::buildTransactionQueries(),
            'accounts' => self::buildAccountQueries(),
            'account_types' => self::buildAccountTypeQueries(),
            'balances' => self::buildBalanceQueries(),
            'payees' => self::buildCrudQueries('payees', 'payee_name'),
            'categories' => self::buildCrudQueries('categories', 'category_name'),
            'types' => self::buildCrudQueries('transaction_types', 'type_name'),
            'credit_cards' => self::buildCreditCardQueries(),
            'totals' => self::buildTotalsQueries(),
            'updates' => self::buildUpdateQueries()
        ];
    }

    // Helper functions to build queries dynamically
    private static function buildTransactionQueries(): array
    {
        return [
            'showAll' => self::buildSelectQuery(self::TRANSACTIONS_COLUMNS, self::TRANSACTIONS_JOIN, 'ORDER BY t.id DESC'),
            'showSingle' => self::buildSelectQuery(self::TRANSACTIONS_COLUMNS, self::TRANSACTIONS_JOIN, 'WHERE t.id = :id'),
            'store' => 'INSERT INTO transactions (transaction_date, rpaccount_id, type_id, payee_id, category_id, amount)
                        VALUES (:date, :rpaccount, :type, :payee, :category, :amount)',
            'update' => 'UPDATE transactions
                        SET transaction_date = :date, posted_at = :posted_at, rpaccount_id = :rpaccount, type_id = :type,
                            payee_id = :payee, category_id = :category, amount = :amount
                        WHERE id = :id',
            'destroy' => 'DELETE FROM transactions WHERE id = :id',
        ];
    }

    private static function buildAccountQueries(): array
    {
        return [
            'showAll' => 'SELECT * FROM rpaccounts ORDER BY account_name',
            'showSingleAccountTransactions' => self::buildSelectQuery(
                self::TRANSACTIONS_COLUMNS,
                self::TRANSACTIONS_JOIN,
                'WHERE rp.id = :id ORDER BY t.transaction_date DESC'
            ),
            'store' => 'INSERT INTO rpaccounts (account_name, account_type_id)
                    VALUES (:name, :type)',
            'update' => 'UPDATE rpaccounts SET account_name = :value WHERE id = :id',
            'destroy' => 'DELETE FROM rpaccounts WHERE id = :id',
        ];
    }

    private static function buildAccountTypeQueries(): array
    {
        return [
            'showAll' => 'SELECT * FROM account_types ORDER BY account_type_name',
        ];
    }

    private static function buildBalanceQueries(): array
    {
        $queries = [];

        foreach (self::BALANCES_CONDITIONS as $key => $condition) {
            $column = ($key === 'totalSavings') ? 'balance' : 'amount';
            $table = ($key === 'totalSavings') ? 'account_balances' : 'transactions';
            $queries[$key] = self::buildSumQuery($column, $table, $condition);
        }

        return $queries;
    }

    private static function buildCreditCardQueries(): array
    {
        return [
            'showAll' => 'SELECT cc.id, rp.account_name, cc.balance,2, cc.credit_limit,2, cc.available_credit,2,
                          cc.due_date, cc.closing_date, cc.utilization_percentage
                          FROM credit_cards cc
                          INNER JOIN rpaccounts rp ON cc.rpaccount_id = rp.id
                          ORDER BY cc.due_date',
        ];
    }

    private static function buildTotalsQueries(): array
    {
        return [
            'total_cash' => 'SELECT SUM(amount) 
                       FROM transactions t
                       JOIN view_accounts_type_3_2 v
                       ON t.rpaccount_id = v.id',
            'total_checking' => 'SELECT SUM(amount) 
                       FROM transactions t
                       JOIN view_accounts_type_3 v
                       ON t.rpaccount_id = v.id',
            'total_savings' => 'SELECT SUM(amount)
                        FROM transactions t
                        JOIN view_accounts_type_2 v
                        ON t.rpaccount_id = v.id',
            'total_available_credit' => 'SELECT SUM(available_credit)
                        FROM credit_cards',
            'total_credit_card_balance' => 'SELECT SUM(amount)
                        FROM transactions t
                        JOIN view_accounts_type_1 v
                        ON t.rpaccount_id = v.id'
        ];
    }

    private static function buildSelectQuery(string $columns, string $join, string $conditions = ''): string
    {
        return "SELECT {$columns} {$join} {$conditions}";
    }

    private static function buildSumQuery(string $column, string $table, string $conditions): string
    {
        return "SELECT SUM({$column}) AS total FROM {$table} WHERE {$conditions}";
    }

    private static function buildCrudQueries(string $table, string $column): array
    {
        return [
            'showAll' => "SELECT * FROM {$table} ORDER BY {$column}",
            'showSingle' => "SELECT * FROM {$table} WHERE id = :id",
            'store' => "INSERT INTO {$table} ({$column}) VALUES (:value)",
            'update' => "UPDATE {$table} SET {$column} = :value WHERE id = :id",
            'destroy' => "DELETE FROM {$table} WHERE id = :id",
        ];
    }

    private static function buildUpdateQueries(): array
    {
        return [
            'updateBalances' => "UPDATE account_balances ab
                             JOIN (
                                 SELECT t.rpaccount_id, COALESCE(SUM(t.amount), 0) AS total_balance
                                 FROM transactions t
                                 GROUP BY t.rpaccount_id
                             ) AS calculated ON ab.id = calculated.rpaccount_id
                             SET ab.balance = calculated.total_balance"
        ];
    }
}