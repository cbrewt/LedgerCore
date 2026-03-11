<?php

namespace Core\Models;

use Core\App;
use Core\Repositories\AccountRepository;

class Account extends BaseModel
{
    protected static $table = 'rpaccounts'; // ✅ Ensures the correct table is used
    protected static ?AccountRepository $repository = null;

    public int $id;
    public string $account_name;
    public int $account_type_id;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->id = $attributes['id'] ?? 0;
        $this->account_name = $attributes['account_name'] ?? '';
        $this->account_type_id = $attributes['account_type_id'] ?? 0;
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
