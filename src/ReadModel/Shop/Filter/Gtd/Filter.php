<?php


namespace App\ReadModel\Shop\Filter\Gtd;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/shopGtd')) {
            $filter = $session->get('filter/shopGtd');
            if (isset($filter['name'])) $this->name = $filter['name'];
        }
    }
}