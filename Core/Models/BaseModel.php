<?php

namespace Core\Models;

use Core\Database;

abstract class BaseModel
{
    protected static $table;
    protected $db;
    protected $attributes = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();

        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }

        $this->exists = isset($attributes['id']);
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public static function find($id): ?static
    {
        $table = static::getTable();
        $query = "SELECT * FROM {$table} WHERE id = :id";
        $result = static::query($query, ['id' => $id]);

        return $result ? new static($result[0]) : null;
    }


    public static function getTable()
    {
        return static::$table ?? strtolower((new \ReflectionClass(static::class))->getShortName()) . 's';
    }

    public static function query($sql, $params = [])
    {
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public static function all(): array
    {
        $table = static::getTable();
        $results = static::query("SELECT * FROM {$table}");

        return array_map(fn($row) => new static($row), $results);
    }


    public static function where($column, $value)
    {
        $table = static::getTable();
        $query = "SELECT * FROM {$table} WHERE {$column} = :value";
        return static::query($query, ['value' => $value])->get();
    }

    public function save()
    {
        $table = static::getTable();
        $fields = array_keys($this->attributes);
        $params = array_combine(array_map(fn($field) => ":$field", $fields), array_values($this->attributes));

        if ($this->exists) {
            // Ensure that ID exists before updating
            if (!isset($this->attributes['id'])) {
                throw new \Exception("Cannot update record without an ID.");
            }
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $fields));
            $query = "UPDATE {$table} SET {$setClause} WHERE id = :id";
        } else {
            $query = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES (" . implode(',', array_keys($params)) . ")";
        }

        $result = static::query($query, $params);

        if (!$this->exists) {
            $this->attributes['id'] = Database::getInstance()->lastInsertId();
            $this->exists = true;
        }

        return $result;
    }


    public function delete()
    {
        if (!isset($this->attributes['id'])) {
            throw new \Exception("Cannot delete without an ID.");
        }

        $table = static::getTable();
        $query = "DELETE FROM {$table} WHERE id = :id";
        return static::query($query, ['id' => $this->attributes['id']]);
    }

    public function fill($data)
    {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }
}
