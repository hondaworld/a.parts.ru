<?php


namespace App\ReadModel\Firm\Filter\FirmBalanceHistory;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $firmID;
    public $inPage;
    public $providerID;
    public $dateofadded;

    public function __construct(int $firmID)
    {
        $this->firmID = $firmID;
        $session = new Session();
        if ($session->get('filter/firmBalanceHistory')) {
            $filter = $session->get('filter/firmBalanceHistory');
            if (isset($filter['providerID'])) $this->providerID = $filter['providerID'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}