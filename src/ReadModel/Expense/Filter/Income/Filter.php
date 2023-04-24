<?php


namespace App\ReadModel\Expense\Filter\Income;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $createrID;
    public $number;
    public $zapSkladID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/skladsIncome')) {
            $filter = $session->get('filter/skladsIncome');
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['zapSkladID'])) $this->zapSkladID = $filter['zapSkladID'];
        }
    }
}