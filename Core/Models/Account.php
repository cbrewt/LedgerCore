<?php

namespace Core\Models;

use Core\App;
use Core\Repositories\AccountRepository;

class Account extends BaseModel
{
    protected static $table = 'rpaccounts';
    protected static ?AccountRepository $repository = null;

    public int $id;
    public string $account_name;
    public int $account_type_id;
    public int $is_archived;
    public ?string $archived_at;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->id = isset($attributes['id']) ? (int) $attributes['id'] : 0;
        $this->account_name = isset($attributes['account_name']) ? (string) $attributes['account_name'] : '';
        $this->account_type_id = isset($attributes['account_type_id']) ? (int) $attributes['account_type_id'] : 0;
        $this->is_archived = isset($attributes['is_archived']) ? (int) $attributes['is_archived'] : 0;
        $this->archived_at = isset($attributes['archived_at']) && $attributes['archived_at'] !== ''
            ? (string) $attributes['archived_at']
            : null;
    }

    protected static function getRepository()
    {
        if (self::$repository === null) {
            self::$repository = App::resolve(AccountRepository::class);
        }

        return self::$repository;
    }

    public static function getAll(): array
    {
        return self::getRepository()->all();
    }

    public static function getAllTotals(): array
    {
        return self::getRepository()->getAllTotals();
    }

    public static function getAllBalances(): array
    {
        return self::getRepository()->getAllBalances();
    }
}