<?php


namespace App\ReadModel\Firm\Filter\ProviderBalanceHistory;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $providerID;
    public $inPage;
    public $firmID;
    public $userID;
    public $dateofadded;

    public function __construct(int $providerID)
    {
        $this->providerID = $providerID;
        $session = new Session();
        if ($session->get('filter/providerBalanceHistory')) {
            $filter = $session->get('filter/providerBalanceHistory');
            if (isset($filter['firmID'])) $this->firmID = $filter['firmID'];
            if (isset($filter['userID'])) $this->userID = $filter['userID'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}