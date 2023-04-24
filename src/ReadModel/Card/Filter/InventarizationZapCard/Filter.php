<?php


namespace App\ReadModel\Card\Filter\InventarizationZapCard;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $number;
    public $createrID;
    public $zapSkladID;
    public $location;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/inventarizationZapCard')) {
            $filter = $session->get('filter/inventarizationZapCard');
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['zapSkladID'])) $this->zapSkladID = $filter['zapSkladID'];
            if (isset($filter['location'])) $this->location = $filter['location'];
        }
    }
}