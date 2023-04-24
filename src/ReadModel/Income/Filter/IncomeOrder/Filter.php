<?php


namespace App\ReadModel\Income\Filter\IncomeOrder;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $providerID;
    public $zapSkladID;
    public $document_num;
    public $dateofadded;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/incomeOrder')) {
            $filter = $session->get('filter/incomeOrder');
            if (isset($filter['providerID'])) $this->providerID = $filter['providerID'];
            if (isset($filter['document_num'])) $this->document_num = $filter['document_num'];
            if (isset($filter['zapSkladID'])) $this->zapSkladID = $filter['zapSkladID'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}