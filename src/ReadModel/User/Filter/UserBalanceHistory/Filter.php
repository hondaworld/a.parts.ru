<?php


namespace App\ReadModel\User\Filter\UserBalanceHistory;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $userID;
    public $inPage;
    public $firmID;
    public $dateofadded;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
        $session = new Session();
        if ($session->get('filter/userBalanceHistory')) {
            $filter = $session->get('filter/userBalanceHistory');
            if (isset($filter['firmID'])) $this->firmID = $filter['firmID'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}