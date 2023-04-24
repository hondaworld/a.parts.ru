<?php


namespace App\ReadModel\Contact;


class TownView
{
    public const townsWithoutRegion = [598, 822];

    public $townID;
    public $regionID;
    public $typeID;
    public $name;
    public $name_short;
    public $name_doc;
    public $daysFromMoscow;
    public $isFree;
    public $isHide;
    public $region;
    public $daysFromMoscowRegion;
    public $country;
    public $type;
    public $type_short;

    public function getTownFullName()
    {
        return ($this->name_doc ?: (in_array($this->townID, self::townsWithoutRegion) ? '' : $this->region . ', ') . $this->type_short . ' ' . $this->name_short);
    }
}