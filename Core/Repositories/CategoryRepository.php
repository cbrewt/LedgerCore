<?php

namespace Core\Repositories;

use Core\DatabaseHelper;
use Core\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected static string $table = 'categories';

    public function all(): array
    {
        $query = "SELECT id, category_name FROM " . static::$table . " ORDER BY category_name ASC";
        $results = DatabaseHelper::fetchAllByQuery($query);

        return array_map(fn($row) => new Category($row), $results);
    }

    public function find(int $id): ?Category
    {
        $query = "SELECT id, category_name FROM " . static::$table . " WHERE id = ? LIMIT 1";
        $result = DatabaseHelper::fetchByQuery($query, [$id]);

        return $result ? new Category($result) : null;
    }

    public function create(string $categoryName): bool
    {
        return DatabaseHelper::insert(self::$table, ['category_name' => $categoryName]);
    }

    public function update(int $id, string $categoryName): bool
    {
        return DatabaseHelper::update(self::$table, $id, ['category_name' => $categoryName]) ? true : false;
    }

    public function delete(int $id): bool
    {
        return DatabaseHelper::delete(self::$table, $id) ? true : false;
    }
}

