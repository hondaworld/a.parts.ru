<?php

namespace App\Tests\Builder\Beznal;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Firm\Entity\Firm\Firm;

class FirmBeznalBuilder
{
    private bool $isMain;
    private Bank $bank;
    private Firm $firm;

    public function __construct(Firm $firm, bool $isMain = false)
    {
        $this->isMain = $isMain;
        $this->firm = $firm;
        $this->bank = new Bank('123456', 'Банк', '1245', 'Москва, 1', 'Описание');
    }

    public function build(): Beznal
    {
        $beznal = new Beznal($this->firm, $this->bank, '1234567849499', 'Описание', $this->isMain);
        $this->firm->assignBeznal($beznal);

        return $beznal;
    }
}