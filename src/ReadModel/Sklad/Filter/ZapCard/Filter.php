<?php


namespace App\ReadModel\Sklad\Filter\ZapCard;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $showDeleted;
    public $number;
    public $zapGroupID;
    public $managerID;
    public $shop_typeID;
    public $createrID;
    public $auto_modelID;
    public $year;
    public $searchWholeNumber;
    public $quantity1_from;
    public $quantity1_till;
    public $quantity5_from;
    public $quantity5_till;
    public $quantity6_from;
    public $quantity6_till;
    public $quantity;
    public $abc;
    public $zapSkladID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/skladZapCards')) {
            $filter = $session->get('filter/skladZapCards');
            if (isset($filter['showDeleted'])) $this->showDeleted = $filter['showDeleted'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['zapGroupID'])) $this->zapGroupID = $filter['zapGroupID'];
            if (isset($filter['managerID'])) $this->managerID = $filter['managerID'];
            if (isset($filter['shop_typeID'])) $this->shop_typeID = $filter['shop_typeID'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['auto_modelID'])) $this->auto_modelID = $filter['auto_modelID'];
            if (isset($filter['year'])) $this->year = $filter['year'];
            if (isset($filter['quantity1_from'])) $this->quantity1_from = $filter['quantity1_from'];
            if (isset($filter['quantity1_till'])) $this->quantity1_till = $filter['quantity1_till'];
            if (isset($filter['quantity5_from'])) $this->quantity5_from = $filter['quantity5_from'];
            if (isset($filter['quantity5_till'])) $this->quantity5_till = $filter['quantity5_till'];
            if (isset($filter['quantity6_from'])) $this->quantity6_from = $filter['quantity6_from'];
            if (isset($filter['quantity6_till'])) $this->quantity6_till = $filter['quantity6_till'];
            if (isset($filter['quantity'])) $this->quantity = $filter['quantity'];
            if (isset($filter['abc'])) $this->abc = $filter['abc'];
            if (isset($filter['searchWholeNumber'])) $this->searchWholeNumber = $filter['searchWholeNumber'];
        }
    }
}