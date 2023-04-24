<?php


namespace App\ReadModel\Auto\Filter\Auto;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $number;
    public $vin;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/autos')) {
            $filter = $session->get('filter/autos');
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['vin'])) $this->vin = $filter['vin'];
        }
    }
}