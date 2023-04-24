<?php


namespace App\ReadModel\Detail\Filter\ShopZamenaAbcp;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $number;
    public $number2;
    public $createrID;
    public $createrID2;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/shopZamenaAbcp')) {
            $filter = $session->get('filter/shopZamenaAbcp');
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['number2'])) $this->number2 = $filter['number2'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['createrID2'])) $this->createrID2 = $filter['createrID2'];
        }
    }
}