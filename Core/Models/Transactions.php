<?php

namespace Core\Models;

use Core\BaseModel;

class Transaction extends BaseModel
{
    protected static $table = 'transactions';

    public function account()
    {
        return Account::find($this->rpaccount_id);
    }
}
