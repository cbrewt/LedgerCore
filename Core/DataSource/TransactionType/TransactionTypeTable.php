<?php

namespace Core\DataSource\TransactionType;

use Atlas\Orm\Mapper\Table;

class TransactionTypeTable extends Table
{
    protected function setTableName()
    {
        $this->tableName = 'transaction_types';
    }

    protected function setPrimaryKey()
    {
        $this->primaryKey = ['id'];
    }

    protected function setColumns()
    {
        $this->columns = ['id', 'type_name'];
    }
}
