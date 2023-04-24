<?php


namespace App\ReadModel\Detail\Filter\Weight;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $number;
    public $createrID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/weight')) {
            $filter = $session->get('filter/weight');
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
        }
    }
}