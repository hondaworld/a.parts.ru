<?php


namespace App\ReadModel\Beznal;


class BankView
{
    public $bankID;
    public $bik;
    public $name;
    public $korschet;
    public $address;
    public $description;

    public function getBankFullName()
    {
        return $this->bik . ' - ' . $this->name;
    }
}