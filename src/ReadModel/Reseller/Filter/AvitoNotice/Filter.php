<?php


namespace App\ReadModel\Reseller\Filter\AvitoNotice;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $oem;
    public $brand;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/avitoNotices')) {
            $filter = $session->get('filter/avitoNotices');
            if (isset($filter['oem'])) $this->oem = $filter['oem'];
            if (isset($filter['brand'])) $this->brand = $filter['brand'];
        }
    }
}