<?php


namespace App\ReadModel\Beznal;


class BeznalView
{
    public $beznalID;
    public $bankID;
    public $bank;
    public $bank_name;
    public $rasschet;
    public $isHide;
    public $isMain;

    public function getRequisite(): string
    {
        return 'р/с №' . $this->rasschet . ' в ' . $this->bank_name;
    }
}