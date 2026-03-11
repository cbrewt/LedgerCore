<?php

namespace Core\Models;

class Type extends BaseModel
{
    protected static $table = 'transaction_types';

    public static function getAll()
    {
        // Fetch results as an array
        $results = static::query("SELECT * FROM " . static::getTable());

        // Ensure it returns an array
        return array_map(fn($row) => new static($row), $results ?? []);
    }
}
