<?php


namespace App\ReadModel\Detail\Filter\Creater;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;
    public $isOriginal;
    public $tableName;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/creaters')) {
            $filter = $session->get('filter/creaters');
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['isOriginal'])) $this->isOriginal = $filter['isOriginal'];
            if (isset($filter['tableName'])) $this->tableName = $filter['tableName'];
        }
    }
}