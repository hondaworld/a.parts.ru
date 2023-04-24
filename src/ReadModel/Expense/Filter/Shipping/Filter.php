<?php


namespace App\ReadModel\Expense\Filter\Shipping;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $managerID;
    public $createrID;
    public $number;
    public $zapSkladID_to;
    public $isPacked;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/skladsShipping')) {
            $filter = $session->get('filter/skladsShipping');
            if (isset($filter['managerID'])) $this->managerID = $filter['managerID'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['zapSkladID_to'])) $this->zapSkladID_to = $filter['zapSkladID_to'];
            if (isset($filter['isPacked'])) $this->isPacked = $filter['isPacked'];
        }
    }
}