<?php


namespace App\ReadModel\Manager\Filter\Auth;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $ip;
    public $dateofadded;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/managerAuth')) {
            $filter = $session->get('filter/managerAuth');
            if (isset($filter['ip'])) $this->ip = $filter['ip'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}