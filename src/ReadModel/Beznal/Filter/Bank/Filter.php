<?php


namespace App\ReadModel\Beznal\Filter\Bank;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $bik;
    public $name;
    public $address;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/bank')) {
            $filter = $session->get('filter/bank');
            if (isset($filter['bik'])) $this->bik = $filter['bik'];
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['address'])) $this->address = $filter['address'];
        }
    }
}