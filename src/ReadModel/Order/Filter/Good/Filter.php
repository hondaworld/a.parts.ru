<?php


namespace App\ReadModel\Order\Filter\Good;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $orderID;
    public $number;
    public $createrID;
    public $incomeStatus;
    public $reserve;
    public $isShowAllGoods;
    public $expenseDocumentNumber;
    public $schetNumber;
    public $zapSkladID;
    public $providerID;

    public function __construct(int $userID)
    {
        $session = new Session();
        if ($session->get('filter/orderGoods' . $userID)) {
            $filter = $session->get('filter/orderGoods' . $userID);
            if (isset($filter['orderID'])) $this->orderID = $filter['orderID'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['incomeStatus'])) $this->incomeStatus = $filter['incomeStatus'];
            if (isset($filter['reserve'])) $this->reserve = $filter['reserve'];
            if (isset($filter['isShowAllGoods'])) $this->isShowAllGoods = $filter['isShowAllGoods'];
            if (isset($filter['expenseDocumentNumber'])) $this->expenseDocumentNumber = $filter['expenseDocumentNumber'];
            if (isset($filter['schetNumber'])) $this->schetNumber = $filter['schetNumber'];
            if (isset($filter['zapSkladID'])) $this->zapSkladID = $filter['zapSkladID'];
            if (isset($filter['providerID'])) $this->providerID = $filter['providerID'];
        }
    }
}