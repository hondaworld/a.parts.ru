<?php


namespace App\ReadModel\User\Filter\User;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;
    public $userName;
    public $phonemob;
    public $town;
    public $ownerManagerID;
    public $isOpt;
    public $isShowHide;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/users')) {
            $filter = $session->get('filter/users');
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['userName'])) $this->userName = $filter['userName'];
            if (isset($filter['phonemob'])) $this->phonemob = $filter['phonemob'];
            if (isset($filter['town'])) $this->town = $filter['town'];
            if (isset($filter['ownerManagerID'])) $this->ownerManagerID = $filter['ownerManagerID'];
            if (isset($filter['isOpt'])) $this->isOpt = $filter['isOpt'];
            if (isset($filter['isShowHide'])) $this->isShowHide = $filter['isShowHide'];
        }
    }
}