<?php

namespace Core\Repositories;

use Core\Database;
use PDO;

class ReconciliationRepository
{
    protected PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO reconciliations (
                rpaccount_id,
                start_date,
                end_date,
                beginning_balance,
                ending_balance
            ) VALUES (
                :rpaccount_id,
                :start_date,
                :end_date,
                :beginning_balance,
                :ending_balance
            )
        ");

        $stmt->execute([
            'rpaccount_id'      => $data['rpaccount_id'],
            'start_date'        => $data['start_date'],
            'end_date'          => $data['end_date'],
            'beginning_balance' => $data['beginning_balance'],
            'ending_balance'    => $data['ending_balance'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findLatestForAccount(int $rpaccountId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                rpaccount_id,
                start_date,
                end_date,
                beginning_balance,
                ending_balance,
                finalized_at
            FROM reconciliations
            WHERE rpaccount_id = :rpaccount_id
            ORDER BY end_date DESC, id DESC
            LIMIT 1
        ");

        $stmt->execute([
            'rpaccount_id' => $rpaccountId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function existsForWindow(int $rpaccountId, string $startDate, string $endDate): bool
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM reconciliations
            WHERE rpaccount_id = :rpaccount_id
              AND start_date = :start_date
              AND end_date = :end_date
            LIMIT 1
        ");

        $stmt->execute([
            'rpaccount_id' => $rpaccountId,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
