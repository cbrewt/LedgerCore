<?php

namespace Core\Repositories;

use Core\DatabaseHelper;
use Core\Models\Payee;

class PayeeRepository extends BaseRepository
{
    protected static string $table = 'payees';

    public function all(): array
    {
        $query = "SELECT id, payee_name FROM " . static::$table . " ORDER BY payee_name ASC";
        $results = DatabaseHelper::fetchAllByQuery($query);

        return array_map(fn($row) => new Payee($row), $results);
    }

    public function find(int $id): ?Payee
    {
        $query = "SELECT id, payee_name FROM " . static::$table . " WHERE id = ? LIMIT 1";
        $result = DatabaseHelper::fetchByQuery($query, [$id]);

        return $result ? new Payee($result) : null;
    }

    public function create(string $payeeName): bool
    {
        return DatabaseHelper::insert(self::$table, ['payee_name' => $payeeName]);
    }

    public function update(int $id, string $payeeName): bool
    {
        return DatabaseHelper::update(self::$table, $id, ['payee_name' => $payeeName]) ? true : false;
    }

    public function delete(int $id): bool
    {
        return DatabaseHelper::delete(self::$table, $id) ? true : false;
    }
}

