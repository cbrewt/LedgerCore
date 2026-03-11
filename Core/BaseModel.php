<?php

namespace Core;

class BaseModel
{
    protected static $table;
    protected $db;
    protected $attributes = [];
    protected bool $exists = false; // ✅ Define the property explicitly

    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();

        if (!empty($attributes)) {
            $this->attributes = $attributes; // ✅ Ensure attributes are stored
        }

        $this->exists = isset($attributes['id']); // ✅ Identify if it is an existing record
    }


    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }


    public static function find($id)
    {
        $instance = new static();
        $table = static::$table ?? strtolower(static::class) . 's';
        $query = "SELECT * FROM {$table} WHERE id = :id";
        return $instance->db->query($query, ['id' => $id])->find();
    }

    public static function getTable()
    {
        return static::$table ?? strtolower((new \ReflectionClass(static::class))->getShortName()) . 's';
    }


    public static function all()
    {
        $instance = new static();
        $table = static::getTable();
        $results = $instance->db->query("SELECT * FROM {$table}")->get();

        // Convert array results into objects
        $models = array_map(fn($row) => new static($row), $results);

        return $models;
    }


    public static function where($column, $value)
    {
        $instance = new static();
        $table = static::$table ?? strtolower(static::class) . 's';
        $query = "SELECT * FROM {$table} WHERE {$column} = :value";
        return $instance->db->query($query, ['value' => $value])->get();
    }

    public function save()
    {
        $table = static::$table ?? strtolower(static::class) . 's';
        $fields = array_keys($this->attributes);
        $placeholders = array_map(fn($field) => ":$field", $fields);
        $params = array_combine($placeholders, array_values($this->attributes));

        if (isset($this->attributes['id'])) {
            // Update existing record
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $fields));
            $query = "UPDATE {$table} SET {$setClause} WHERE id = :id";
        } else {
            // Insert new record
            $query = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        }

        return $this->db->query($query, $params);
    }

    public function delete()
    {
        if (!isset($this->attributes['id'])) {
            throw new \Exception("Cannot delete without an ID.");
        }

        $table = static::$table ?? strtolower(static::class) . 's';
        $query = "DELETE FROM {$table} WHERE id = :id";
        return $this->db->query($query, ['id' => $this->attributes['id']]);
    }

    public function fill($data)
    {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }


}
