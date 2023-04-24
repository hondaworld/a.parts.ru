<?php


namespace App\ReadModel\Card\Filter\ZapCard;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $showDeleted;
    public $number;
    public $managerID;
    public $zapGroupID;
    public $shop_typeID;
    public $createrID;
    public $auto_modelID;
    public $year;
    public $searchWholeNumber;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/zapCards')) {
            $filter = $session->get('filter/zapCards');
            if (isset($filter['showDeleted'])) $this->showDeleted = $filter['showDeleted'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['zapGroupID'])) $this->zapGroupID = $filter['zapGroupID'];
            if (isset($filter['managerID'])) $this->managerID = $filter['managerID'];
            if (isset($filter['shop_typeID'])) $this->shop_typeID = $filter['shop_typeID'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['auto_modelID'])) $this->auto_modelID = $filter['auto_modelID'];
            if (isset($filter['year'])) $this->year = $filter['year'];
            if (isset($filter['searchWholeNumber'])) $this->searchWholeNumber = $filter['searchWholeNumber'];
        }
    }
}