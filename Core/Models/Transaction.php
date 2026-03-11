<?php

namespace Core\Models;

use Core\Database;
use Core\Models\Account;

class Transaction extends BaseModel
{
    public int $id;
    public string $transaction_date;

    public ?string $posted_at;

    public float $amount;
    public ?int $rpaccount_id; // Can be null to avoid the NULL error
    public ?string $account_name;
    public ?string $type_name;
    public ?string $payee_name;
    public ?string $category_name;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->id = $attributes['id'] ?? 0;
        $this->transaction_date = $attributes['transaction_date'] ?? '';
        $this->posted_at = $attributes['posted_at'] ?? null;

        $this->amount = $attributes['amount'] ?? 0.0;
        $this->rpaccount_id = $attributes['rpaccount_id'] ?? null;
        $this->account_name = $attributes['account_name'] ?? null;
        $this->type_name = $attributes['type_name'] ?? null;
        $this->payee_name = $attributes['payee_name'] ?? null;
        $this->category_name = $attributes['category_name'] ?? null;

        if ($this->rpaccount_id === null) {
            error_log("❌ ERROR: `rpaccount_id` is NULL in Transaction ID: {$this->id}");
        }
    }

    public function account()
    {
        if ($this->rpaccount_id === null) {
            error_log("❌ ERROR: `rpaccount_id` is NULL inside `account()` method for Transaction ID: {$this->id}");
            return null;
        }

        $account = Account::find($this->rpaccount_id);

        if (!$account) {
            error_log("❌ ERROR: No account found for rpaccount_id: " . $this->rpaccount_id);
            return null;
        }

        error_log("✅ DEBUG: Found Account '{$account->account_name}' for Transaction ID: {$this->id}");
        return $account;
    }

   


public static function getAll($limit = 10, $offset = 0, $filters = [], $sortField = 'transaction_date', $sortOrder = 'DESC')
{
    $db = Database::getInstance();

    // Hard-cast pagination to safe ints
    $limit  = is_numeric($limit)  ? max(1, (int) $limit)  : 10;
    $offset = is_numeric($offset) ? max(0, (int) $offset) : 0;

    // Allow-list sort fields (raw transaction columns only)
    $allowedSortFields = ['transaction_date', 'posted_at', 'amount', 'rpaccount_id', 'type_id', 'payee_id', 'category_id', 'id'];
    $sortField = in_array($sortField, $allowedSortFields, true) ? $sortField : 'transaction_date';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

    $query = "SELECT
                 t.id,
                 t.transaction_date,
                 t.posted_at,
                 t.amount,
                 t.rpaccount_id,
                 a.account_name,
                 ty.type_name,
                 p.payee_name,
                 c.category_name
          FROM transactions t
          LEFT JOIN rpaccounts a ON t.rpaccount_id = a.id
          LEFT JOIN transaction_types ty ON t.type_id = ty.id
          LEFT JOIN payees p ON t.payee_id = p.id
          LEFT JOIN categories c ON t.category_id = c.id";

    $conditions = [];
    $params = [];

    foreach (['rpaccount_id', 'type_id', 'payee_id', 'category_id'] as $field) {
        if (isset($filters[$field]) && is_numeric($filters[$field]) && (int) $filters[$field] > 0) {
            $conditions[] = "t.{$field} = :{$field}";
            $params[$field] = (int) $filters[$field];
        }
    }

    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $conditions[] = "t.transaction_date BETWEEN :start_date AND :end_date";
        $params['start_date'] = $filters['start_date'];
        $params['end_date']   = $filters['end_date'];
    }

    // Default behavior: posted-only unless include_unposted truthy
    if (empty($filters['include_unposted'])) {
        $conditions[] = "t.posted_at IS NOT NULL";
    }

    if ($conditions) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // Deterministic ORDER BY
    if ($sortField === 'posted_at') {
        // ✅ Interleave unposted by transaction_date:
        // "effective date" = posted_at when present, otherwise transaction_date.
        //
        // If posted_at is DATETIME and transaction_date is DATE, CONCAT normalizes type.
        $effectiveDateExpr = "COALESCE(t.posted_at, CONCAT(t.transaction_date, ' 00:00:00'))";

        if ($sortOrder === 'DESC') {
            $orderBy = "{$effectiveDateExpr} DESC, t.id DESC";
        } else {
            $orderBy = "{$effectiveDateExpr} ASC, t.id ASC";
        }
    } else {
        // General tie-breaker: always add id in the same direction
        $orderBy = "t.{$sortField} {$sortOrder}, t.id {$sortOrder}";
    }

    // IMPORTANT: inject limit/offset as ints to avoid PDO binding them as strings
    $query .= " ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}";

    error_log("🔍 DEBUG: Generated Query: {$query}");
    error_log("🔍 DEBUG: Query Parameters: " . print_r($params, true));

    $results = $db->query($query, $params)->get();

    return array_map(fn($row) => new static($row), $results);
}

    public static function count($filters = [])
    {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as total FROM transactions t";
        $conditions = [];
        $params = [];

        foreach (['rpaccount_id', 'type_id', 'payee_id', 'category_id'] as $field) {
            if (!empty($filters[$field])) {
                $conditions[] = "t.{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "t.transaction_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $filters['start_date'];
            $params['end_date'] = $filters['end_date'];
        }

        if (empty($filters['include_unposted'])) {
            $conditions[] = "t.posted_at IS NOT NULL";
        }

        if ($conditions) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        return $db->query($query, $params)->find()['total'] ?? 0;
    }

    public function save()
    {
        $db = Database::getInstance();
        $table = static::getTable();
        $fields = array_keys($this->attributes);
        $placeholders = array_map(fn($field) => ":$field", $fields);
        $params = array_combine($placeholders, array_values($this->attributes));

        if (isset($this->id)) {
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $fields));
            $query = "UPDATE {$table} SET {$setClause} WHERE id = :id";
        } else {
            $query = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        }

        $saved = $db->query($query, $params);

        if ($saved) {
            error_log("✅ SUCCESS: Transaction saved, updating balances for Account ID: " . $this->rpaccount_id);
            Account::updateBalances();
        } else {
            error_log("❌ ERROR: Failed to save transaction.");
        }

        return $saved;
    }

    public function delete()
    {
        $db = Database::getInstance();
        $deleted = $db->query("DELETE FROM transactions WHERE id = :id", ['id' => $this->id]);

        if ($deleted) {
            error_log("🔄 DEBUG: Transaction deleted, updating balances...");
            Account::updateBalances();
        }

        return $deleted;
    }

public static function getForReconcile(int $rpaccountId, string $startDate, string $endDate): array
{
    $db = Database::getInstance();

    $sql = "
        SELECT
            t.id,
            t.transaction_date,
            t.posted_at,
            t.amount,
            p.payee_name
        FROM transactions t
        LEFT JOIN payees p ON t.payee_id = p.id
        WHERE t.rpaccount_id = :rpaccount_id
          AND t.transaction_date BETWEEN :start_date AND :end_date
        ORDER BY t.transaction_date ASC, t.id ASC
    ";

    $params = [
        'rpaccount_id' => $rpaccountId,
        'start_date'   => $startDate,
        'end_date'     => $endDate,
    ];

    error_log("RECONCILE SQL: " . $sql);
    error_log("RECONCILE PARAMS: " . print_r($params, true));

    $rows = $db->query($sql, $params)->get();

    return array_map(fn($row) => new static($row), $rows);
}
public static function updatePostedAt(int $id, ?string $postedAt): bool
    {
        $db = Database::getInstance();

        $db->query(
            "UPDATE transactions SET posted_at = :posted_at WHERE id = :id",
            [
                'posted_at' => $postedAt,
                'id'        => $id,
            ]
        );

        return true;
    }
   
}
