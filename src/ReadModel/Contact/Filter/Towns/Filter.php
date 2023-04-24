<?php


namespace App\ReadModel\Contact\Filter\Towns;


use App\Model\Contact\Entity\Country\Country;
use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;
    public $name_short;
    public $regionID;
    public $typeID;
    public $isFree;
    public $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
        $session = new Session();
        if ($session->get('filter/towns')) {
            $filter = $session->get('filter/towns');
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['name_short'])) $this->name_short = $filter['name_short'];
            if (isset($filter['regionID'])) $this->regionID = $filter['regionID'];
            if (isset($filter['typeID'])) $this->typeID = $filter['typeID'];
            if (isset($filter['isFree'])) $this->isFree = $filter['isFree'];
        }
    }
}