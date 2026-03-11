<?php

namespace Core\Repositories;

use Core\DatabaseHelper;
use Core\Models\AccountType;

class AccountTypeRepository extends BaseRepository
{
    protected static string $table = 'account_types';

    public function all()
    {
        $query = "SELECT id, account_type_name FROM account_types ORDER BY id ASC";
        $results = DatabaseHelper::fetchAllByQuery($query);

        $accountTypes = [];
        foreach ($results as $row) {
            $accountTypes[$row['id']] = new AccountType([
                'id' => $row['id'],
                'account_type_name' => $row['account_type_name']
            ]);
        }
        return $accountTypes;
    }


}
