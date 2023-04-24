<?php


namespace App\ReadModel\Provider\Filter\Provider;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;
    public $showHide = false;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/providers')) {
            $filter = $session->get('filter/providers');
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['showHide'])) $this->showHide = $filter['showHide'];
        }
    }
}