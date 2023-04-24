<?php


namespace App\ReadModel\Income\Filter\Income;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $incomeID;
    public $managerID;
    public $number;
    public $abc;
    public $dateofadded;
    public $dateofzakaz;
    public $dateofin;
    public $dateofinplan;
    public $createrID;
    public $status;
    public $orderID;
    public $gtd;
//    public $isDoc;
    public $isUnpack;
    public $incomeOrder;
    public $incomeDocument;
    public $isShowQuantityMskNull;
    public $isShowQuantitySpbNull;
    public $isShowQuantitySrvNull;
    public $isShowLessMskMax;
    public $isShowLessSpbMax;
    public $providerPriceID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/income')) {
            $filter = $session->get('filter/income');
            if (isset($filter['incomeID'])) $this->incomeID = $filter['incomeID'];
            if (isset($filter['abc'])) $this->abc = $filter['abc'];
            if (isset($filter['managerID'])) $this->managerID = $filter['managerID'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['dateofadded']) && $filter['dateofadded'] != '') $this->dateofadded = new \Datetime($filter['dateofadded']);
            if (isset($filter['dateofzakaz']) && $filter['dateofzakaz'] != '') $this->dateofzakaz = new \Datetime($filter['dateofzakaz']);
            if (isset($filter['dateofin']) && $filter['dateofin'] != '') $this->dateofin = new \Datetime($filter['dateofin']);
            if (isset($filter['dateofinplan']) && $filter['dateofinplan'] != '') $this->dateofinplan = new \Datetime($filter['dateofinplan']);
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['status'])) $this->status = $filter['status'];
            if (isset($filter['orderID'])) $this->orderID = $filter['orderID'];
            if (isset($filter['gtd'])) $this->gtd = $filter['gtd'];
//            if (isset($filter['isDoc'])) $this->isDoc = $filter['isDoc'];
            if (isset($filter['isUnpack'])) $this->isUnpack = $filter['isUnpack'];
            if (isset($filter['incomeOrder'])) $this->incomeOrder = $filter['incomeOrder'];
            if (isset($filter['incomeDocument'])) $this->incomeDocument = $filter['incomeDocument'];
            if (isset($filter['providerPriceID'])) $this->providerPriceID = $filter['providerPriceID'];
            if (isset($filter['isShowQuantityMskNull'])) $this->isShowQuantityMskNull = (bool) $filter['isShowQuantityMskNull'];
            if (isset($filter['isShowQuantitySpbNull'])) $this->isShowQuantitySpbNull = (bool) $filter['isShowQuantitySpbNull'];
            if (isset($filter['isShowQuantitySrvNull'])) $this->isShowQuantitySrvNull = (bool) $filter['isShowQuantitySrvNull'];
            if (isset($filter['isShowLessMskMax'])) $this->isShowLessMskMax = (bool) $filter['isShowLessMskMax'];
            if (isset($filter['isShowLessSpbMax'])) $this->isShowLessSpbMax = (bool) $filter['isShowLessSpbMax'];
        }
    }
}