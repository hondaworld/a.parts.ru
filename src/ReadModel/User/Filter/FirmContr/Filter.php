<?php


namespace App\ReadModel\User\Filter\FirmContr;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $organization;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/firmContr')) {
            $filter = $session->get('filter/firmContr');
            if (isset($filter['organization'])) $this->organization = $filter['organization'];
        }
    }
}