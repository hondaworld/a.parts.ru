<?php


namespace App\ReadModel\Card\Filter\InventarizationGood;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $showDis;
    public $number;
    public $managerID;
    public $createrID;
    public $zapSkladID;
    public $location;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/inventarizationGood')) {
            $filter = $session->get('filter/inventarizationGood');
            if (isset($filter['showDis'])) $this->showDis = $filter['showDis'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['managerID'])) $this->managerID = $filter['managerID'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['zapSkladID'])) $this->zapSkladID = $filter['zapSkladID'];
            if (isset($filter['location'])) $this->location = $filter['location'];
        }
    }
}