<?php

namespace Core;

class DatabaseHelper
{
    protected static function getDb()
    {
        return Database::getInstance();
    }

    public static function fetchAll($table)
    {
        return self::getDb()->query("SELECT * FROM {$table}")->get();
    }

    public static function fetchById($table, $id)
    {
        return self::getDb()->query("SELECT * FROM {$table} WHERE id = ?", [$id])->find();
    }

    public static function fetchAllByQuery($sql, $params = [])
    {
        return self::getDb()->query($sql, $params)->get();
    }

    public static function fetchByQuery($sql, $params = [])
    {
        return self::getDb()->query($sql, $params)->find();
    }

    public static function insert(string $table, array $data): bool
    {
        $db = Database::getInstance();

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $stmt = $db->query($query, array_values($data));

        return $stmt ? true : false; // ✅ Ensure a boolean return
    }

    public static function update($table, $id, array $data)
    {
        $set = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE id = ?";

        return self::getDb()->query($sql, array_merge(array_values($data), [$id]));
    }

    public static function delete($table, $id)
    {
        return self::getDb()->query("DELETE FROM {$table} WHERE id = ?", [$id]);
    }

    /**
     * Executes an SQL query with parameters, used for update, delete, and custom queries.
     */
    public static function executeQuery($sql, $params = [])
    {
        return self::getDb()->query($sql, $params);
    }

    public static function getConnection(): \PDO
    {
        return self::getDb()->getConnection();
    }

}
