<?php


namespace App\ReadModel\Shop\Filter\Location;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;
    public $name_short;
    public $number;
    public $isEmpty;
    public $showHidden;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/shopLocation')) {
            $filter = $session->get('filter/shopLocation');
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['name_short'])) $this->name = $filter['name_short'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['isEmpty'])) $this->isEmpty = $filter['isEmpty'];
            if (isset($filter['showHidden'])) $this->showHidden = $filter['showHidden'];
        }
    }
}