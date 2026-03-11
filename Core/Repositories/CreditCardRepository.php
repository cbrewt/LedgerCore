<?php

namespace Core\Repositories;

use Core\DatabaseHelper;
use Core\Models\CreditCard;

class CreditCardRepository extends BaseRepository
{
    protected static string $table = 'credit_cards';

    public function all(): array
    {
        $query = "SELECT cc.id, rpa.account_name, cc.balance, cc.credit_limit, 
                         cc.available_credit, cc.due_date, cc.closing_date, cc.utilization_percentage
                  FROM " . static::$table . " cc
                  LEFT JOIN rpaccounts rpa ON cc.rpaccount_id = rpa.id
                  ORDER BY cc.due_date ASC";

        $results = DatabaseHelper::fetchAllByQuery($query);

        return array_map(fn($row) => new CreditCard($row), $results);
    }

    public function find(int $id): ?CreditCard
    {
        $query = "SELECT cc.id, rpa.account_name, cc.balance, cc.credit_limit, 
                         cc.available_credit, cc.due_date, cc.closing_date, cc.utilization_percentage
                  FROM " . static::$table . " cc
                  LEFT JOIN rpaccounts rpa ON cc.rpaccount_id = rpa.id
                  WHERE cc.id = ?";

        $result = DatabaseHelper::fetchByQuery($query, [$id]);

        return $result ? new CreditCard($result) : null;
    }
}
