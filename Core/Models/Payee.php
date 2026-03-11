<?php

namespace Core\Models;

class Payee extends BaseModel
{
    protected static $table = 'payees';

    public static function getAll()
    {
        $results = static::query("SELECT * FROM " . static::getTable());

        return array_map(fn($row) => new static($row), $results);
    }
}
