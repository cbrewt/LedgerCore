<?php

namespace Core\Models;

class CreditCard
{
    public int $id;
    public string $account_name;
    public float $balance;
    public float $credit_limit;
    public float $available_credit;
    public string $due_date;
    public string $closing_date;
    public float $utilization_percentage;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->account_name = $data['account_name'];
        $this->balance = (float)$data['balance'];
        $this->credit_limit = (float)$data['credit_limit'];
        $this->available_credit = (float)$data['available_credit'];
        $this->due_date = $data['due_date'];
        $this->closing_date = $data['closing_date'];
        $this->utilization_percentage = (float)$data['utilization_percentage'];
    }
}
