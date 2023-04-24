<?php


namespace App\ReadModel\Firm\Filter\Schet;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $user_name;
    public $firmID;
    public $finance_typeID;
    public $dateofadded;
    public $dateofpaid;
    public $schet_num;
    public $status;
    public $isShowCanceled;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/schet')) {
            $filter = $session->get('filter/schet');
            if (isset($filter['firmID'])) $this->firmID = $filter['firmID'];
            if (isset($filter['user_name'])) $this->user_name = $filter['user_name'];
            if (isset($filter['finance_typeID'])) $this->finance_typeID = $filter['finance_typeID'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
            if (isset($filter['dateofpaid'])) $this->dateofpaid = $filter['dateofpaid'];
            if (isset($filter['schet_num'])) $this->schet_num = $filter['schet_num'];
            if (isset($filter['status'])) $this->status = $filter['status'];
            if (isset($filter['isShowCanceled'])) $this->isShowCanceled = $filter['isShowCanceled'];
        }
    }
}