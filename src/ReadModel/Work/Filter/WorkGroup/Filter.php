<?php


namespace App\ReadModel\Work\Filter\WorkGroup;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $isTO;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/workGroup')) {
            $filter = $session->get('filter/workGroup');
            if (isset($filter['isTO'])) $this->isTO = $filter['isTO'];
        }
    }
}