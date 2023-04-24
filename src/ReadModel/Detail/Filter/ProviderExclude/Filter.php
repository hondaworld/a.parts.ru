<?php


namespace App\ReadModel\Detail\Filter\ProviderExclude;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $number;
    public $createrID;
    public $providerID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/providerExclude')) {
            $filter = $session->get('filter/providerExclude');
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['providerID'])) $this->providerID = $filter['providerID'];
        }
    }
}