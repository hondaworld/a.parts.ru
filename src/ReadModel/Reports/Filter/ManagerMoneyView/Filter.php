<?php


namespace App\ReadModel\Reports\Filter\ManagerMoneyView;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $dateofreport;
    public $managerID;
    public $finance_typeID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/reportManagerMoneyView')) {
            $filter = $session->get('filter/reportManagerMoneyView');
            if (isset($filter['dateofreport'])) $this->dateofreport = $filter['dateofreport'];
            if (isset($filter['managerID'])) $this->managerID = $filter['managerID'];
            if (isset($filter['finance_typeID'])) $this->finance_typeID = $filter['finance_typeID'];
        }
    }
}