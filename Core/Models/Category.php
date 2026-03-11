<?php

namespace Core\Models;

class Category extends BaseModel
{
    protected static $table = 'categories';

    public static function getAll()
    {
        $results = static::query("SELECT * FROM " . static::getTable());

        return array_map(fn($row) => new static($row), $results);
    }
}
