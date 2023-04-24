<?php


namespace App\ReadModel\User\Filter\UserBalanceAct;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $firmID;
    public $userID;
    public $dateofadded;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
        $this->DefaultDate();
    }

    public function DefaultDate()
    {
        $this->dateofadded['date_from'] = (new \DateTime(date('Y-m') . '-01'))->modify('-1 month')->format('d.m.Y');
        $this->dateofadded['date_till'] = (new \DateTime(date('Y-m') . '-01'))->modify('-1 day')->format('d.m.Y');
    }
}