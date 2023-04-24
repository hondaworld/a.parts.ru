<?php


namespace App\ReadModel\Contact;


class ContactView
{
    public $contactID;
    public $townID;
    public $town;
    public $region;
    public $country;
    public $zip;
    public $street;
    public $house;
    public $str;
    public $kv;
    public $phonemob;
    public $email;
    public $isHide;
    public $isMain;

    public function getAddress(): string
    {
        $address = $this->country . ", " . $this->region . ", " . $this->town;
        if ($this->street != "") $address .= ", " . $this->street;
        if ($this->house != "") $address .= ", д." . $this->house;
        if ($this->str != "") $address .= ", стр./корп." . $this->str;
        if ($this->kv != "") $address .= ", кв./оф." . $this->kv;
        return $address;
    }
}