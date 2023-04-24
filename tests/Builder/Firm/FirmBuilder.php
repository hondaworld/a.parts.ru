<?php

namespace App\Tests\Builder\Firm;

use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;

class FirmBuilder
{
    private bool $isNDS;
    private string $nameShort;

    public function __construct(bool $isNDS, string $nameShort = 'Тестовая компания')
    {
        $this->isNDS = $isNDS;
        $this->nameShort = $nameShort;
    }

    public function build(): Firm
    {
        $nalog = new Nalog('Налог');
        $nalog->addNds(new \DateTime('2019-01-01'), 20);
        $firm = new Firm($this->nameShort, 'ООО "Тестовая компания"', null, null, null, null, $this->isNDS, true, $nalog, null, null);

        return $firm;
    }
}