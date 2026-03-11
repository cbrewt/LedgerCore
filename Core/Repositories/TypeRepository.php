<?php

namespace Core\Repositories;

use Core\DatabaseHelper;
use Core\Models\Type;

class TypeRepository extends BaseRepository
{
    protected static string $table = 'transaction_types';

    public function all(): array
    {
        $query = "SELECT id, type_name FROM " . static::$table . " ORDER BY type_name ASC";
        $results = DatabaseHelper::fetchAllByQuery($query);

        return array_map(fn($row) => new Type($row), $results);
    }

    public function find(int $id): ?Type
    {
        $query = "SELECT id, type_name FROM " . static::$table . " WHERE id = ? LIMIT 1";
        $result = DatabaseHelper::fetchByQuery($query, [$id]);

        return $result ? new Type($result) : null;
    }

    public function create(string $typeName): bool
    {
        if ($typeName === '') {
            return false;
        }

        return DatabaseHelper::insert(static::$table, ['type_name' => $typeName]);
    }

    public function update(int $id, string $typeName): bool
    {
        if ($typeName === '') {
            return false;
        }

        return DatabaseHelper::update(static::$table, $id, ['type_name' => $typeName]) ? true : false;
    }

    public function delete(int $id): bool
    {
        return DatabaseHelper::delete(static::$table, $id) ? true : false;
    }
}

