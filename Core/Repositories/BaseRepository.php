<?php

namespace Core\Repositories;

use Core\Database;

abstract class BaseRepository
{
    protected $db;
    protected static string $table = ''; // ✅ Make `$table` static

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public static function getTableName()
    {
        return static::$table ?: strtolower((new \ReflectionClass(static::class))->getShortName()) . 's';
    }

    public function find(int $id)
    {
        return $this->db->query("SELECT * FROM " . static::getTableName() . " WHERE id = :id", ['id' => $id])->find();
    }

    public function all()
    {
        return $this->db->query("SELECT * FROM " . static::getTableName())->get();
    }
}
