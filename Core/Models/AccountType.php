<?php

namespace Core\Models;

use Core\App;
use Core\Repositories\AccountTypeRepository;

class AccountType
{
    public int $id;
    public string $account_type_name;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->account_type_name = $data['account_type_name'];
    }

    // Static method to delegate to repository
    public static function all()
    {
        return App::resolve(AccountTypeRepository::class)->all();
    }
}
