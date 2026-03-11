<?php

namespace Core\Repositories;

use Core\Database;
use PDO;
use PDOException;

class TransactionRepository
{
    protected PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    /** Alias so controllers calling create() still work */
    public function create(array $data): int
    {
        return $this->store($data);
    }

    /**
     * Store a transaction and, for checking→credit-card payments,
     * auto-mirror into the chosen credit-card account.
     */
    public function store(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $ins = $this->db->prepare("
                INSERT INTO transactions
                    (rpaccount_id, type_id, category_id, payee_id, amount, transaction_date)
                VALUES
                    (:rpaccount_id, :type_id, :category_id, :payee_id, :amount, :transaction_date)
            ");
            $ins->execute([
                ':rpaccount_id'     => $data['rpaccount_id'],
                ':type_id'          => $data['type_id'],
                ':category_id'      => $data['category_id'],
                ':payee_id'         => $data['payee_id'],
                ':amount'           => $data['amount'],
                ':transaction_date' => $data['transaction_date'],
            ]);

            $originalId = (int) $this->db->lastInsertId();

            if (($data['type_id'] ?? null) === 18 && ($data['category_id'] ?? null) === 23) {
                $rpStmt = $this->db->prepare("
                    SELECT rpaccount_id
                    FROM credit_cards
                    WHERE id = :cc_id
                    LIMIT 1
                ");
                $rpStmt->execute([':cc_id' => $data['credit_card_id']]);
                $rpAccount = $rpStmt->fetchColumn();

                if (!$rpAccount) {
                    throw new PDOException("Invalid credit_card_id: {$data['credit_card_id']}");
                }

                $mir = $this->db->prepare("
                    INSERT INTO transactions
                        (rpaccount_id, type_id, category_id, payee_id, amount, transaction_date)
                    VALUES
                        (:rpaccount_id, :type_id, :category_id, :payee_id, :amount, :transaction_date)
                ");
                $mir->execute([
                    ':rpaccount_id'     => (int) $rpAccount,
                    ':type_id'          => 20,
                    ':category_id'      => $data['category_id'],
                    ':payee_id'         => 12,
                    ':amount'           => $data['amount'],
                    ':transaction_date' => $data['transaction_date'],
                ]);
            }

            $this->db->commit();
            return $originalId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $lockCheck = $this->db->prepare("
            SELECT reconciled_at
            FROM transactions
            WHERE id = :id
            LIMIT 1
        ");
        $lockCheck->execute(['id' => $id]);
        $existing = $lockCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            return false;
        }

        if (!empty($existing['reconciled_at'])) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function markPosted(int $transactionId, ?int $rpaccountId = null): bool
    {
        $sql = "
            UPDATE transactions
            SET posted_at = CURRENT_TIMESTAMP
            WHERE id = :id
              AND posted_at IS NULL
        ";

        $params = [':id' => $transactionId];

        if ($rpaccountId !== null) {
            $sql .= " AND rpaccount_id = :rpaccount_id";
            $params[':rpaccount_id'] = $rpaccountId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function markPostedBulk(array $transactionIds, ?int $rpaccountId = null): int
    {
        $transactionIds = array_values(array_unique(array_map('intval', $transactionIds)));
        $transactionIds = array_filter($transactionIds, fn($id) => $id > 0);

        if (empty($transactionIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($transactionIds), '?'));

        $sql = "
            UPDATE transactions
            SET posted_at = CURRENT_TIMESTAMP
            WHERE posted_at IS NULL
              AND id IN ($placeholders)
        ";

        $params = $transactionIds;

        if ($rpaccountId !== null) {
            $sql .= " AND rpaccount_id = ?";
            $params[] = $rpaccountId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function sumFilteredTransactions(array $filters): float
    {
        $sql = 'SELECT SUM(amount) AS total FROM transactions WHERE 1=1';
        $params = [];

        $includeUnposted = !empty($filters['include_unposted']);
        if (!$includeUnposted) {
            $sql .= ' AND posted_at IS NOT NULL';
        }

        if (!empty($filters['rpaccount_id'])) {
            $sql .= ' AND rpaccount_id = :rpaccount_id';
            $params['rpaccount_id'] = $filters['rpaccount_id'];
        }

        if (!empty($filters['type_id'])) {
            $sql .= ' AND type_id = :type_id';
            $params['type_id'] = $filters['type_id'];
        }

        if (!empty($filters['payee_id'])) {
            $sql .= ' AND payee_id = :payee_id';
            $params['payee_id'] = $filters['payee_id'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= ' AND category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= ' AND transaction_date >= :start_date';
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= ' AND transaction_date <= :end_date';
            $params['end_date'] = $filters['end_date'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $total = $stmt->fetchColumn();
        return $total !== false ? (float) $total : 0.0;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ?: null;
    }

    public function findDetailed(int $id): ?array
    {
        $sql = "
            SELECT
                t.id,
                t.transaction_date,
                t.posted_at,
                t.reconciled_at,
                t.rpaccount_id,
                t.type_id,
                t.payee_id,
                t.category_id,
                t.amount,
                a.account_name,
                ty.type_name,
                p.payee_name,
                c.category_name
            FROM transactions t
            LEFT JOIN rpaccounts a ON t.rpaccount_id = a.id
            LEFT JOIN transaction_types ty ON t.type_id = ty.id
            LEFT JOIN payees p ON t.payee_id = p.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ?: null;
    }

    public function getLookupOptions(): array
    {
        return [
            'accounts'   => $this->fetchLookup('rpaccounts', 'account_name'),
            'types'      => $this->fetchLookup('transaction_types', 'type_name'),
            'payees'     => $this->fetchLookup('payees', 'payee_name'),
            'categories' => $this->fetchLookup('categories', 'category_name'),
        ];
    }

    public function getCreditCardOptions(): array
    {
        $stmt = $this->db->query("
            SELECT cc.id AS credit_card_id, rp.account_name
            FROM credit_cards cc
            JOIN rpaccounts rp ON cc.rpaccount_id = rp.id
            ORDER BY rp.account_name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFiltered(array $filters): int
    {
        $params = [];
        $where = $this->buildFilterWhere($filters, $params);
        $sql = "SELECT COUNT(*) AS total FROM transactions t {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        return $total !== false ? (int) $total : 0;
    }

    public function getFiltered(array $filters, int $limit, int $offset, string $sortField = 'posted_at', string $sortOrder = 'DESC'): array
    {
        $params = [];
        $where = $this->buildFilterWhere($filters, $params);
        [$sortExpression, $direction] = $this->sanitizeSort($sortField, $sortOrder);

        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $sql = "
            SELECT
                t.id,
                t.transaction_date,
                t.posted_at,
                t.reconciled_at,
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
            LEFT JOIN categories c ON t.category_id = c.id
            {$where}
            ORDER BY {$sortExpression} {$direction}, t.id {$direction}
            LIMIT {$limit} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function update(array $data): bool
    {
        $lockCheck = $this->db->prepare("
            SELECT reconciled_at
            FROM transactions
            WHERE id = :id
            LIMIT 1
        ");
        $lockCheck->execute(['id' => $data['id']]);
        $existing = $lockCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            return false;
        }

        if (!empty($existing['reconciled_at'])) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE transactions
            SET
                transaction_date = :transaction_date,
                posted_at = :posted_at,
                rpaccount_id = :rpaccount_id,
                type_id = :type_id,
                payee_id = :payee_id,
                category_id = :category_id,
                amount = :amount
            WHERE id = :id
        ");

        return $stmt->execute([
            'id'               => $data['id'],
            'transaction_date' => $data['transaction_date'],
            'posted_at'        => $data['posted_at'],
            'rpaccount_id'     => $data['rpaccount_id'],
            'type_id'          => $data['type_id'],
            'payee_id'         => $data['payee_id'],
            'category_id'      => $data['category_id'],
            'amount'           => $data['amount'],
        ]);
    }

    public function getForReconcile(int $rpaccountId, string $startDate, string $endDate): array
    {
        $sql = "
            SELECT
                t.id,
                t.transaction_date,
                t.posted_at,
                t.reconciled_at,
                t.amount,
                p.payee_name
            FROM transactions t
            LEFT JOIN payees p ON t.payee_id = p.id
            WHERE t.rpaccount_id = :rpaccount_id
              AND t.transaction_date >= :start_date
              AND t.transaction_date <= :end_date
            ORDER BY t.transaction_date ASC, t.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'rpaccount_id' => $rpaccountId,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function markReconciledWindow(int $rpaccountId, string $startDate, string $endDate): int
    {
        $sql = "
            UPDATE transactions
            SET reconciled_at = CURRENT_TIMESTAMP
            WHERE rpaccount_id = :rpaccount_id
              AND posted_at IS NOT NULL
              AND transaction_date >= :start_date
              AND transaction_date <= :end_date
              AND reconciled_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'rpaccount_id' => $rpaccountId,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
        ]);

        return $stmt->rowCount();
    }

    public function clearReconciledWindow(int $rpaccountId, string $startDate, string $endDate): int
    {
        $sql = "
            UPDATE transactions
            SET reconciled_at = NULL
            WHERE rpaccount_id = :rpaccount_id
              AND transaction_date >= :start_date
              AND transaction_date <= :end_date
              AND reconciled_at IS NOT NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'rpaccount_id' => $rpaccountId,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
        ]);

        return $stmt->rowCount();
    }

    public function setPostedState(int $transactionId, bool $isPosted): bool
    {
        $lockCheck = $this->db->prepare("
            SELECT reconciled_at
            FROM transactions
            WHERE id = :id
            LIMIT 1
        ");
        $lockCheck->execute(['id' => $transactionId]);
        $existing = $lockCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            return false;
        }

        if (!empty($existing['reconciled_at'])) {
            return false;
        }

        $sql = $isPosted
            ? "UPDATE transactions SET posted_at = CURRENT_TIMESTAMP WHERE id = :id"
            : "UPDATE transactions SET posted_at = NULL WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $transactionId]);
    }

    private function fetchLookup(string $table, string $labelColumn): array
    {
        $stmt = $this->db->query("SELECT id, {$labelColumn} FROM {$table} ORDER BY {$labelColumn} ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildFilterWhere(array $filters, array &$params): string
    {
        $clauses = [];

        foreach (['rpaccount_id', 'type_id', 'payee_id', 'category_id'] as $field) {
            if (isset($filters[$field]) && is_numeric($filters[$field]) && (int) $filters[$field] > 0) {
                $clauses[] = "t.{$field} = :{$field}";
                $params[$field] = (int) $filters[$field];
            }
        }

        if (!empty($filters['start_date'])) {
            $clauses[] = "t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $clauses[] = "t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (empty($filters['include_unposted'])) {
            $clauses[] = "t.posted_at IS NOT NULL";
        }

        if ($clauses === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $clauses);
    }

    private function sanitizeSort(string $sortField, string $sortOrder): array
    {
        $allowed = [
            'id'               => 't.id',
            'transaction_date' => 't.transaction_date',
            'posted_at'        => 'COALESCE(t.posted_at, t.transaction_date)',
            'account_name'     => 'a.account_name',
            'type_name'        => 'ty.type_name',
            'payee_name'       => 'p.payee_name',
            'category_name'    => 'c.category_name',
            'amount'           => 't.amount',
        ];

        $sortExpression = $allowed[$sortField] ?? $allowed['posted_at'];
        $direction = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        return [$sortExpression, $direction];
    }
}
