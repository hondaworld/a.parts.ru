<?php


namespace App\ReadModel\Manager\Filter;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;
    public $user_name;
    public $email;
    public $login;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/managers')) {
            $filter = $session->get('filter/managers');
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['user_name'])) $this->user_name = $filter['user_name'];
            if (isset($filter['email'])) $this->email = $filter['email'];
            if (isset($filter['login'])) $this->login = $filter['login'];
        }
    }
}