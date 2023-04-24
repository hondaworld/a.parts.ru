<?php


namespace App\ReadModel\Order\Filter\ManagerOperation;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $userID;
    public $inPage;
    public $managerID;
    public $orderID;
    public $number;
    public $dateofadded;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
        $session = new Session();
        if ($session->get('filter/orderManagerOperation')) {
            $filter = $session->get('filter/orderManagerOperation');
            if (isset($filter['managerID'])) $this->managerID = $filter['managerID'];
            if (isset($filter['orderID'])) $this->orderID = $filter['orderID'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}